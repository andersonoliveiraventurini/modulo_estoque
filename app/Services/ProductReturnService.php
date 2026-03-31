<?php

namespace App\Services;

use App\Models\ProductReturn;
use App\Models\ReturnItem;
use App\Models\ReturnAuthorization;
use App\Models\Orcamento;
use App\Models\MetodoPagamento;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use App\Services\QualityPdfService;
use App\Services\CreditoService;

class ProductReturnService
{
    protected $pdfService;
    protected $creditoService;

    public function __construct(QualityPdfService $pdfService, CreditoService $creditoService)
    {
        $this->pdfService = $pdfService;
        $this->creditoService = $creditoService;
    }

    public function initiate(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orcamento = Orcamento::findOrFail($data['orcamento_id']);
            
            $return = ProductReturn::create([
                'nr' => $this->generateNr(),
                'orcamento_id' => $data['orcamento_id'],
                'cliente_id' => $orcamento->cliente_id,
                'vendedor_id' => $orcamento->vendedor_id,
                'usuario_id' => auth()->id(),
                'data_ocorrencia' => $data['data_ocorrencia'],
                'status' => 'pendente_supervisor',
                'valor_total_credito' => 0, // Will be calculated from items
                'nota_fiscal' => $data['nota_fiscal'] ?? null,
                'romaneio_recebimento' => $data['romaneio_recebimento'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'troca_produto' => $data['troca_produto'] ?? false,
            ]);

            $totalCredito = 0;
            foreach ($data['items'] as $itemId => $quantity) {
                $orcamentoItem = $orcamento->itens()->findOrFail($itemId);
                
                // O crédito deve ser baseado no que o cliente REALMENTE pagou (valor com desconto)
                $valorUnitarioPago = (float) ($orcamentoItem->valor_unitario_com_desconto ?? $orcamentoItem->valor_unitario);

                $item = ReturnItem::create([
                    'return_id' => $return->id,
                    'produto_id' => $orcamentoItem->produto_id,
                    'orcamento_item_id' => $itemId,
                    'quantidade' => $quantity,
                    'valor_unitario' => $valorUnitarioPago,
                    'subtotal' => $valorUnitarioPago * $quantity,
                ]);

                $totalCredito += $item->subtotal;
            }

            $return->update(['valor_total_credito' => $totalCredito]);

            // Gera PDF de solicitação
            $this->pdfService->generateReturnPdf($return, 'solicited');

            return $return;
        });
    }

    public function authorizeSupervisor(ProductReturn $return, bool $approved, ?string $observacoes = null)
    {
        return DB::transaction(function () use ($return, $approved, $observacoes) {
            ReturnAuthorization::create([
                'return_id' => $return->id,
                'user_id' => auth()->id() ?? 1,
                'role' => 'supervisor',
                'status' => $approved ? 'aprovado' : 'negado',
                'observacoes' => $observacoes,
            ]);

            $return->update([
                'status' => $approved ? 'pendente_estoque' : 'negado'
            ]);

            return $return;
        });
    }

    public function authorizeEstoque(ProductReturn $return, bool $approved, array $params = [])
    {
        return DB::transaction(function () use ($return, $approved, $params) {
            ReturnAuthorization::create([
                'return_id' => $return->id,
                'user_id' => auth()->id() ?? 1,
                'role' => 'estoque',
                'status' => $approved ? 'aprovado' : 'negado',
                'observacoes' => $params['observacoes_estoque'] ?? null,
            ]);

            if ($approved) {
                $return->update([
                    'status' => 'finalizado',
                    'finalizado_at' => now(),
                ]);

                // Atualizar estoque se solicitado
                if ($params['retorno_estoque'] ?? false) {
                    foreach ($return->items as $item) {
                        $product = Produto::find($item->produto_id);
                        if ($product) {
                            $product->addEstoque($item->quantidade);
                        }
                    }
                }

                // Recalcular o crédito total a partir dos itens para garantir precisão absoluta
                $valorFinalCredito = (float) $return->items()->sum('subtotal');

                // Garante que o valor no registro da devolução esteja sincronizado
                $return->update(['valor_total_credito' => $valorFinalCredito]);

                // Gerar Crédito para o Cliente
                $this->creditoService->adicionarCredito(
                    $return->cliente,
                    $valorFinalCredito,
                    "Crédito gerado pela devolução #{$return->nr}",
                    $return->id
                );

                // Gera PDF de autorização finalizada
                $this->pdfService->generateReturnPdf($return, 'authorized');
                
                // Se for troca, gera romaneio de troca
                if ($return->troca_produto) {
                    $this->pdfService->generateReturnPdf($return, 'exchange');
                }
            } else {
                $return->update(['status' => 'negado']);
            }

            return $return;
        });
    }

    protected function generateNr()
    {
        $year = date('Y');
        $last = ProductReturn::whereYear('created_at', $year)->latest()->first();
        $num = $last ? (int) substr($last->nr, -4) + 1 : 1;
        return "DEV-{$year}-" . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
