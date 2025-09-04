<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AtualizarClientesCNPJ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'clientes:atualizar-cnpj {--limit=100}';
    protected $description = 'Atualiza dados dos clientes com base na BrasilAPI usando o CNPJ';

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $clientes = Cliente::whereNotNull('cnpj')
            ->whereRaw('LENGTH(cnpj) = 14')
            ->limit($limit)
            ->get();

        foreach ($clientes as $cliente) {
            $cnpj = preg_replace('/\D/', '', $cliente->cnpj);

            try {
                $response = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

                if ($response->failed()) {
                    $this->error("Erro ao buscar CNPJ {$cnpj}");
                    continue;
                }

                $data = $response->json();

                $cliente->razao_social       = $data['razao_social'] ?? $cliente->razao_social;
                $cliente->nome_fantasia      = $data['nome_fantasia'] ?? $cliente->nome_fantasia;
                $cliente->data_abertura      = $data['data_inicio_atividade'] ?? $cliente->data_abertura;
                $cliente->cnae               = $data['cnae_fiscal_descricao'] ?? $cliente->cnae;
                $cliente->inscricao_estadual = $data['inscricao_estadual'] ?? $cliente->inscricao_estadual;
                $cliente->save();

                $this->info("✅ Cliente {$cliente->id} atualizado com sucesso!");
                sleep(1); // pausa para evitar bloqueio (ajuste se necessário)

            } catch (\Exception $e) {
                $this->error("Erro no cliente {$cliente->id}: {$e->getMessage()}");
            }
        }
    }
}
