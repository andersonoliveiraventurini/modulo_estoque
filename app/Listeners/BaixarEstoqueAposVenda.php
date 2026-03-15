<?php

namespace App\Listeners;

use App\Events\OrcamentoPago;
use App\Services\EstoqueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BaixarEstoqueAposVenda
{
    protected $estoqueService;

    /**
     * Create the event listener.
     */
    public function __construct(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrcamentoPago $event): void
    {
        try {
            Log::info("Iniciando baixa de estoque para Orçamento Pago #{$event->orcamento->id}");
            
            $event->orcamento->load('itens.produto');

            foreach ($event->orcamento->itens->whereNotNull('produto_id') as $item) {
                if ($item->produto) {
                    $item->produto->decrement('estoque_atual', $item->quantidade);
                    $this->estoqueService->verificarAlertaEstoqueBaixo($item->produto);
                    
                    Log::info("Estoque atualizado: Produto #{$item->produto_id} - Qtd: -{$item->quantidade}");
                }
            }

            // ── REGISTRO DA VENDA PARA O DASHBOARD ──
            \App\Models\Venda::create([
                'orcamento_id' => $event->orcamento->id,
                'cliente_id'   => $event->orcamento->cliente_id,
                'vendedor_id'  => $event->orcamento->vendedor_id,
                'valor_total'  => $event->orcamento->valor_com_desconto > 0 ? $event->orcamento->valor_com_desconto : $event->orcamento->valor_total_itens,
                'status'       => 'concluida',
                'data_venda'   => now(),
            ]);

            Log::info("Registro de venda criado e baixa de estoque concluída para Orçamento #{$event->orcamento->id}");
        } catch (\Exception $e) {
            Log::error("Erro no processamento pós-venda do Orçamento #{$event->orcamento->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }
}
