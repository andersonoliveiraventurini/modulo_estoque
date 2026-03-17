<?php

namespace App\Console\Commands;

use App\Models\RequisicaoCompra;
use App\Services\CompraService;
use Illuminate\Console\Command;

class ProcessarEstoqueCritico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-critical-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converte requisições de compra automáticas (estoque baixo) em rascunhos de pedidos de compra.';

    /**
     * Execute the console command.
     */
    public function handle(CompraService $compraService)
    {
        $this->info('Iniciando processamento de requisições de estoque crítico...');

        $requisicoes = RequisicaoCompra::where('status', 'pendente')
            ->where('observacao', 'like', '%Gerada automaticamente pelo sistema devido a estoque baixo.%')
            ->get();

        if ($requisicoes->isEmpty()) {
            $this->comment('Nenhuma requisição pendente de estoque baixo encontrada.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($requisicoes));
        $bar->start();

        foreach ($requisicoes as $requisicao) {
            $compraService->converterRequisicaoEmPedido($requisicao);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Processamento concluído com sucesso.');

        return 0;
    }
}
