<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    protected $csvPath = 'database/seeders/_carga_inicial/clientes.csv';
    protected $batchSize = 100;

    public function run(): void
    {
        $fullPath = base_path($this->csvPath);
        if (!file_exists($fullPath)) {
            $this->command->error("Arquivo CSV não encontrado em: {$fullPath}");
            return;
        }

        DB::disableQueryLog();
        @ini_set('auto_detect_line_endings', '1');

        $handle = fopen($fullPath, 'r');
        if (!$handle) {
            $this->command->error("Não foi possível abrir o arquivo CSV.");
            return;
        }

        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);
            $this->command->error("CSV vazio ou inválido.");
            return;
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '');
        $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);

        $line = 1;
        $ok   = 0;
        $fail = 0;
        $remessa = 0;
        $startedAt = microtime(true);

        $this->command->info("Iniciando importação de clientes em lotes de {$this->batchSize}...");

        $batchClientes = [];
        $batchContatos = [];
        $batchEnderecos = [];
        $batchCreditos = [];
        $batchBloqueios = [];

        $flushBatch = function () use (&$batchClientes, &$batchContatos, &$batchEnderecos, &$batchCreditos, &$batchBloqueios, &$ok, &$fail, &$remessa) {
            if (empty($batchClientes)) {
                return;
            }

            $remessa++;
            $inicio = $ok + 1;
            $fim    = $ok + count($batchClientes);
            $this->command->info("Processando remessa {$remessa} (clientes {$inicio} até {$fim})...");

            try {
                DB::transaction(function () use (&$batchClientes, &$batchContatos, &$batchEnderecos, &$batchCreditos, &$batchBloqueios, &$ok) {
                    // Insere clientes e captura IDs
                    $ids = [];
                    foreach ($batchClientes as $c) {
                        $id = DB::table('clientes')->insertGetId($c);
                        $ids[] = $id;
                    }

                    // Monta relacionamentos ajustando cliente_id
                    $i = 0;
                    foreach ($ids as $clienteId) {
                        // Contatos
                        if (isset($batchContatos[$i])) {
                            foreach ($batchContatos[$i] as $c) {
                                $c['cliente_id'] = $clienteId;
                                DB::table('contatos')->insert($c);
                            }
                        }
                        // Endereço
                        if (isset($batchEnderecos[$i])) {
                            $e = $batchEnderecos[$i];
                            $e['cliente_id'] = $clienteId;
                            DB::table('enderecos')->insert($e);
                        }
                        // Crédito
                        if (isset($batchCreditos[$i])) {
                            $cr = $batchCreditos[$i];
                            $cr['cliente_id'] = $clienteId;
                            DB::table('analise_creditos')->insert($cr);
                        }
                        // Bloqueio
                        if (isset($batchBloqueios[$i]) && $batchBloqueios[$i] !== null) {
                            $b = $batchBloqueios[$i];
                            $b['cliente_id'] = $clienteId;
                            DB::table('bloqueios')->insert($b);

                            $cliente = Cliente::find($clienteId);
                            $cliente->bloqueado = true;
                            $cliente->save();
                        }

                        $i++;
                    }

                    $ok += count($ids);
                });

                $this->command->info("Remessa {$remessa} concluída: {$fim} clientes importados até agora.");
            } catch (\Throwable $e) {
                $fail += count($batchClientes);
                Log::error("Falha ao inserir remessa {$remessa}. Erro: {$e->getMessage()}");
                $this->command->error("Erro na remessa {$remessa}, consulte os logs.");
            }

            // Limpa os buffers
            $batchClientes = [];
            $batchContatos = [];
            $batchEnderecos = [];
            $batchCreditos = [];
            $batchBloqueios = [];
        };

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $line++;
                if (count($row) !== count($header)) {
                    $fail++;
                    Log::warning("Linha {$line}: quantidade de colunas não corresponde ao cabeçalho.");
                    continue;
                }

                $data = array_combine($header, $row);
                if ($data === false) {
                    $fail++;
                    Log::warning("Linha {$line}: não foi possível combinar as colunas.");
                    continue;
                }

                $digits = fn($v) => $v !== null && $v !== '' ? preg_replace('/\D+/', '', (string) $v) : null;
                $trimOrNull = fn($v) => ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;

                $clienteData = [
                    'numero_brcom'  => $data['numero'] !== '' ? (int) $digits($data['numero']) : null,
                    'cpf'           => $digits($data['cpf'] ?? null),
                    'cnpj'          => $digits($data['cnpj'] ?? null),
                    'nome'          => $trimOrNull($data['nome'] ?? null),
                    'nome_fantasia' => $trimOrNull($data['fantasia'] ?? null),
                    'razao_social'  => $trimOrNull($data['empresa'] ?? null),
                    'tratamento'    => $trimOrNull($data['tratamento'] ?? null),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                $email        = $trimOrNull($data['email'] ?? null);
                $telComercial = $trimOrNull($data['telefone_cial'] ?? null);
                $telCelular   = $trimOrNull($data['celular'] ?? null);

                $contatos = [];
                if ($email !== null || $telComercial !== null) {
                    $contatos[] = [
                        'nome'       => $clienteData['nome'] ?? ($clienteData['razao_social'] ?? 'Contato'),
                        'email'      => $email,
                        'telefone'   => $telComercial,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if ($telCelular !== null && ($telComercial === null || $telCelular !== $telComercial)) {
                    $contatos[] = [
                        'nome'       => $clienteData['nome'] ?? ($clienteData['razao_social'] ?? 'Contato'),
                        'email'      => null,
                        'telefone'   => $telCelular,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $endereco = [
                    'logradouro'  => $trimOrNull($data['endereço'] ?? null),
                    'numero'      => null,
                    'complemento' => null,
                    'bairro'      => $trimOrNull($data['bairro'] ?? null),
                    'cidade'      => $trimOrNull($data['codmun'] ?? ($data['cidade'] ?? null)),
                    'estado'      => null,
                    'cep'         => $trimOrNull($data['cep'] ?? null),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $credito = [
                    'limite_credito' => $trimOrNull($data['limite'] ?? null),
                    'validade'       => $trimOrNull($data['venc_limite'] ?? null),
                    'observacoes'    => $trimOrNull($data['bloqueio'] ?? null),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                $bloqueio = null;
                if (($trimOrNull($data['bloqueio'] ?? null) != null) && $trimOrNull($data['bloqueio'] ?? null) != '*') {
                    $bloqueio = [
                        'motivo'     => $trimOrNull($data['bloqueio'] ?? null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $batchClientes[]  = $clienteData;
                $batchContatos[]  = $contatos;
                $batchEnderecos[] = $endereco;
                $batchCreditos[]  = $credito;
                $batchBloqueios[] = $bloqueio;

                if (count($batchClientes) >= $this->batchSize) {
                    $flushBatch();
                }
            }

            $flushBatch();
        } finally {
            fclose($handle);
        }

        $elapsed = round(microtime(true) - $startedAt, 2);
        $this->command->info("Importação finalizada em {$elapsed}s. Sucesso: {$ok}, Falhas: {$fail}.");

        if ($fail > 0) {
            $this->command->warn("Houveram {$fail} falhas. Consulte storage/logs/laravel.log para detalhes.");
        }
    }
}
