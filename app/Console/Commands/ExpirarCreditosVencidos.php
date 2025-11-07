<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CreditoService;

class ExpirarCreditosVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creditos:expirar
{--force : Força a execução sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira créditos vencidos dos clientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando processo de expiração de créditos vencidos...');

        if (!$this->option('force') && !$this->confirm('Deseja continuar?', true)) {
            $this->info('Operação cancelada.');
            return 0;
        }

        try {
            $creditoService = app(CreditoService::class);

            $this->info('Processando créditos vencidos...');
            $quantidadeExpirada = $creditoService->expirarCreditosVencidos();

            $this->info("✓ Processo concluído com sucesso!");
            $this->info("✓ Total de créditos expirados: {$quantidadeExpirada}");

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Erro ao expirar créditos: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }
}
