<?php

namespace App\Services;

use App\Models\RequisicaoCompra;
use App\Models\PedidoCompra;
use App\Models\PedidoCompraItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraService
{
    /**
     * Converte uma Requisição de Compra aprovada em um rascunho de Pedido de Compra.
     */
    public function converterRequisicaoEmPedido(RequisicaoCompra $requisicao)
    {
        if ($requisicao->status !== 'aprovado' && $requisicao->status !== 'pendente') {
            // Se for automação de estoque baixo, ela nasce como 'pendente' 
            // no EstoqueService. Vamos permitir processar as pendentes 
            // que foram geradas automaticamente.
        }

        try {
            return DB::transaction(function () use ($requisicao) {
                // Agrupar itens por fornecedor do produto
                // Se o item não tiver produto ou o produto não tiver fornecedor, usamos null
                $itensComFornecedor = $requisicao->itens->groupBy(function ($item) {
                    return $item->produto?->fornecedor_id ?? null;
                });

                $pedidosCriados = [];

                foreach ($itensComFornecedor as $fornecedorId => $itens) {
                    $pedido = PedidoCompra::create([
                        'fornecedor_id' => $fornecedorId,
                        'requisicao_compra_id' => $requisicao->id,
                        'usuario_id' => $requisicao->solicitante_id,
                        'data_pedido' => now(),
                        'status' => 'rascunho',
                        'observacao' => "Gerado automaticamente a partir da Requisição #{$requisicao->id}. " . ($requisicao->observacao ?? ''),
                        'valor_total' => $itens->sum(function($i) { 
                            return $i->quantidade * ($i->produto?->preco_custo ?? 0); 
                        }),
                    ]);

                    foreach ($itens as $item) {
                        PedidoCompraItem::create([
                            'pedido_compra_id' => $pedido->id,
                            'produto_id' => $item->produto_id,
                            'quantidade' => $item->quantidade,
                            'valor_unitario' => $item->produto?->preco_custo ?? 0,
                            'valor_total' => $item->quantidade * ($item->produto?->preco_custo ?? 0),
                        ]);
                    }

                    $pedidosCriados[] = $pedido;
                }

                // Atualiza o status da requisição
                $requisicao->update(['status' => 'em_processamento']);

                Log::info("Requisição #{$requisicao->id} convertida em " . count($pedidosCriados) . " pedido(s) de compra.");

                return $pedidosCriados;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao converter Requisição #{$requisicao->id} em pedido: " . $e->getMessage());
            throw $e;
        }
    }
}
