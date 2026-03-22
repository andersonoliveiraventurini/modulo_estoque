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

    public function handle(OrcamentoPago $event): void
    {
        $orcamento = $event->orcamento;

        try {
            Log::info("Processando evento OrcamentoPago para Orçamento #{$orcamento->id}");

            // ── REGRA 1: Balcão e Retira WhatsApp ──
            // Gatilho: No ato do pagamento na tela de Balcão.
            if ($orcamento->isBalcao()) {
                Log::info("Canal Balcão detectado. Iniciando baixa definitiva de estoque para Orçamento #{$orcamento->id}");
                $this->estoqueService->baixarEstoqueDefinitivo($orcamento);
            } 
            // ── REGRA 2: Canais de Entrega (Tavares, Rota, etc.) ──
            // Gatilho: Quando o financeiro marca como concluído (Será acionado em outro ponto).
            elseif ($orcamento->isCanalEntrega()) {
                Log::info("Canal de Entrega detectado (Loading Day: {$orcamento->loading_day}). Ignorando baixa automática via evento de pagamento. Aguardando finalização do Financeiro.");
            }
            // ── REGRA 3: Encomendas ──
            // Gatilho: No ato da retirada (Será acionado em tela específica).
            elseif ($orcamento->isEncomenda()) {
                Log::info("Orçamento de Encomenda detectado. Ignorando baixa automática via evento de pagamento. Aguardando confirmação de retirada.");
            }

            // ── REGISTRO DA VENDA PARA O DASHBOARD (Sempre ocorre ao pagar) ──
            \App\Models\Venda::updateOrCreate(
                ['orcamento_id' => $orcamento->id],
                [
                    'cliente_id'   => $orcamento->cliente_id,
                    'vendedor_id'  => $orcamento->vendedor_id,
                    'valor_total'  => $orcamento->valor_com_desconto > 0 ? $orcamento->valor_com_desconto : $orcamento->valor_total_itens,
                    'status'       => 'concluida',
                    'data_venda'   => now(),
                ]
            );

            Log::info("Registro de venda atualizado para Orçamento #{$orcamento->id}");
        } catch (\Exception $e) {
            Log::error("Erro no processamento pós-venda do Orçamento #{$orcamento->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }
}
