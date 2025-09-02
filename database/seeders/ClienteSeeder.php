<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;
use App\Models\Contato;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    protected $csvPath = 'database/seeders/_carga_inicial/clientes.csv';

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
        $header = array_map(fn ($h) => mb_strtolower(trim($h)), $header);

        $line = 1;          // linha do cabeçalho
        $ok   = 0;
        $fail = 0;
        $startedAt = microtime(true);

        $this->command->info("Iniciando importação de clientes a partir de: {$this->csvPath}");

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
                $digits = fn ($v) => $v !== null && $v !== '' ? preg_replace('/\D+/', '', (string) $v) : null;
                $trimOrNull = fn ($v) => ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;

                // Monta dados do cliente (mapeamento conforme solicitado)
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

                // Dados de contato a partir da MESMA linha do CSV
                $email         = $trimOrNull($data['email'] ?? null);
                $telComercial  = $trimOrNull($data['telefone_cial'] ?? null);
                $telCelular    = $trimOrNull($data['celular'] ?? null);
                // (telefone_res está disponível mas não foi solicitado para inserir)

                try {
                    DB::transaction(function () use ($clienteData, $email, $telComercial, $telCelular) {
                        // Insere cliente e captura o ID com segurança
                        $clienteId = DB::table('clientes')->insertGetId($clienteData);

                        $contatos = [];

                        // Contato 1: email + telefone_cial (quando houver)
                        if ($email !== null || $telComercial !== null) {
                            $contatos[] = [
                                'nome'       => $clienteData['nome'] ?? ($clienteData['razao_social'] ?? 'Contato'),
                                'email'      => $email,
                                'telefone'   => $telComercial,
                                'cliente_id' => $clienteId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        // Contato 2: celular (quando houver)
                        if ($telCelular !== null) {
                            // evita duplicar se for igual ao comercial
                            if ($telComercial === null || $telCelular !== $telComercial) {
                                $contatos[] = [
                                    'nome'       => $clienteData['nome'] ?? ($clienteData['razao_social'] ?? 'Contato'),
                                    'email'      => null,
                                    'telefone'   => $telCelular,
                                    'cliente_id' => $clienteId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        if (!empty($contatos)) {
                            DB::table('contatos')->insert($contatos);
                        }
                    });

                    $ok++;
                    // Feedback pontual para lotes grandes (opcional)
                    if ($ok > 0 && $ok % 1000 === 0) {
                        $this->command->info("Progresso: {$ok} clientes importados...");
                    }
                } catch (\Throwable $e) {
                    $fail++;
                    Log::error("Linha {$line}: falha ao inserir cliente/contatos. Erro: {$e->getMessage()}");
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
        // carga de clientes inicial
        /*
        numero -> idbrcom

     */

        /*valor para analise de limite de credito 
        
       tabela brcom - 
        referencias,
        filiação,
        referencias2,
        ultima,
        referencias3,

        // tabela analise_creditos
        
$table->unsignedBigInteger('cliente_id')->nullable()
                    ->comment('Referência ao cliente associado a este orçamento.');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->double('limite_boleto')->nullable()
                    ->comment('Limite de crédito para boleto aprovado para o cliente.');
            $table->double('limite_credito')->nullable()
                    ->comment('Limite de crédito aprovado para o cliente.');
            $table->date('validade')->nullable()
                    ->comment('Data de validade do limite de crédito.');
            $table->text('observacoes')->nullable()
                    ->comment('Observações adicionais sobre a análise de crédito.');

        */



    /* Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de endereço
     tabela brcom* 
    nome,
    endereço,
    bairro,
    cidade,
    cep,

    sendo  tabela endereços
            logradouro,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            cep,
            cliente_id
     */

    /* Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de telefone
     * 
     *tabela brcom*  
    telefone_res,
    telefone_cial,
    celular,
    email,

    sendo tabela de contatos
        nome,
        email,
        telefone,
        cliente_id
     */



        /* campos descartados
         * 
         * relaciona a tabela de clientes 2 - campos cliente     empresa,

valores nulos, 1 ou 0 :
valor,
    funcionario,

    ref_cial,
    emi_rg,
    carta,
    seproc,
    idcarta,
cliente,
    complemento,
    fornecedor,
    compl,
    arquivo,
    vencimento,
    enviado,
    nascimento, carteira,
    cheque,
    boleto,
    cadastro,
avisar,
    cf,
         */
    
}
