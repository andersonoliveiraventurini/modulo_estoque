<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoSeeder extends Seeder
{
    protected $csvPath = 'database/seeders/_carga_inicial/produtos.csv';

    public function run(): void
    {
        $fullPath = base_path($this->csvPath);
        if (!file_exists($fullPath)) {
            $this->command->error("Arquivo CSV não encontrado em: {$fullPath}");
            return;
        }

        // Performance
        DB::disableQueryLog();
        @ini_set('auto_detect_line_endings', '1');

        $handle = fopen($fullPath, 'r');
        if (!$handle) {
            $this->command->error("Não foi possível abrir o arquivo CSV.");
            return;
        }

        // Lê cabeçalho e normaliza chaves
        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);
            $this->command->error("CSV vazio ou inválido.");
            return;
        }
        // Remove possível BOM e normaliza
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '');
        $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);

        $line = 1;          // linha do cabeçalho
        $ok   = 0;
        $fail = 0;
        $startedAt = microtime(true);

        $this->command->info("Iniciando importação de produtos a partir de: {$this->csvPath}");

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $line++;

                // Garante que o número de colunas bate
                if (count($row) !== count($header)) {
                    $fail++;
                    Log::warning("Linha {$line}: quantidade de colunas não corresponde ao cabeçalho.");
                    continue;
                }

                // Combina header => valores
                $data = array_combine($header, $row);
                if ($data === false) {
                    $fail++;
                    Log::warning("Linha {$line}: não foi possível combinar as colunas.");
                    continue;
                }

                // Helpers de limpeza
                $digits = fn($v) => $v !== null && $v !== '' ? preg_replace('/\D+/', '', (string) $v) : null;
                $trimOrNull = fn($v) => ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;
                $toFloat = fn($v) => $v !== null && $v !== '' ? (float) str_replace(',', '.', $v) : null;
                $toInt = fn($v) => $v !== null && $v !== '' ? (int) $v : null;

                try {
                    DB::transaction(function () use ($data, $trimOrNull, $toFloat, $toInt, $digits) {
                        // Monta dados do produto
                        $produtoData = [
                            'codigo_brcom'   => $trimOrNull($data['codigo'] ?? null),
                            'sku'            => $trimOrNull($data['referencia'] ?? null),
                            'nome'           => $trimOrNull($data['descrição'] ?? null),
                            'ncm'            => $trimOrNull($data['ncm'] ?? null),
                            'codigo_barras'  => $digits($data['codigo'] ?? null),
                            'preco_custo'    => $toFloat($data['custo'] ?? null),
                            'preco_venda'    => $toFloat($data['preço'] ?? null),
                            'estoque_minimo' => $toFloat($data['estmin'] ?? null),
                            'estoque_atual'  => $toFloat($data['saldo'] ?? null),
                            'unidade_medida' => $trimOrNull($data['und'] ?? ($data['unidade'] ?? null)),
                            'marca'          => $trimOrNull($data['marca'] ?? null),
                            'modelo'         => $trimOrNull($data['modelo'] ?? null),
                            'descricao'      => $trimOrNull($data['descrição'] ?? null),
                            'observacoes'    => $trimOrNull($data['nomelinha'] ?? null),
                            'ativo'          => isset($data['ativo']) ? (bool) $data['ativo'] : true,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        $fornecedorId = null;

                        if (!empty($data['fornecedor']) && $data['fornecedor'] !== '0') {
                            $cliente = DB::table('clientes')
                                ->where('numero_brcom', $data['fornecedor'])
                                ->first();

                            if ($cliente) {
                                // Verifica se já existe fornecedor com a mesma linha_brcom
                                $fornecedor = DB::table('fornecedores')
                                    ->where('linha_brcom', $cliente->numero_brcom)
                                    ->first();

                                if (!$fornecedor) {
                                    // Se não existir, insere e pega o ID
                                    $fornecedorId = DB::table('fornecedores')->insertGetId([
                                        'nome_fantasia' => $cliente->nome_fantasia,
                                        'linha_brcom'   => $cliente->numero_brcom,
                                        'tratamento'    => $cliente->nome,
                                        'cnpj' => !empty($cliente->cnpj) ? $cliente->cnpj : null,
                                        'created_at'    => now(),
                                        'updated_at'    => now(),
                                    ]);

                                    $this->command->info("Fornecedor criado: {$fornecedorId}");
                                } else {
                                    $fornecedorId = $fornecedor->id;
                                }
                            }
                        }

                        if ($fornecedorId) {
                            $produtoData['fornecedor_id'] = $fornecedorId;
                        }

                        // Insere produto
                        DB::table('produtos')->insert($produtoData);
                    });


                    $ok++;
                    // Feedback pontual para lotes grandes
                    if ($ok > 0 && $ok % 1000 === 0) {
                        $this->command->info("Progresso: {$ok} produtos importados...");
                    }
                } catch (\Throwable $e) {
                    $fail++;
                    Log::error("Linha {$line}: falha ao inserir produto. Erro: {$e->getMessage()}");
                    Log::error("Dados da linha: " . json_encode($data));
                }
            }
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
