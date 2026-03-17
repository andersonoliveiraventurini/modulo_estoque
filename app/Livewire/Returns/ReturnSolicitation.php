<?php

namespace App\Livewire\Returns;

use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Pedido;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReturnSolicitation extends Component
{
    public $pedidoId;
    public $pedido;
    public $items = [];
    public $selectedItems = []; // item_id => [selected => bool, quantity => float]

    public function mount($pedidoId)
    {
        $this->pedidoId = $pedidoId;
        $this->pedido = Pedido::with(['cliente', 'descontos'])->findOrFail($pedidoId);
        
        // No sistema, itens do pedido parecem vir dos itens do orçamento associado.
        // Vamos buscar o orçamento vinculado ao pedido (mesmo cliente, valor, etc ou relação direta se existisse)
        // Como 'Pedido' não tem 'orcamento_id' explicitamente no fillable, mas orçamentos viram pedidos.
        // Vou assumir que o pedido herda os itens do orçamento que o gerou.
        // Nota: Em um ERP real, haveria uma tabela intermediária ou relação. 
        // Aqui, vou buscar o orçamento com o mesmo ID ou relação se disponível.
        
        $orcamento = \App\Models\Orcamento::where('cliente_id', $this->pedido->cliente_id)
            ->where('status', 'Aprovado')
            ->latest()
            ->first();

        if ($orcamento) {
            foreach ($orcamento->itens as $item) {
                $this->items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'nome' => $item->produto->nome,
                    'sku' => $item->produto->sku,
                    'quantidade' => $item->quantidade,
                    'valor_unitario' => $item->valor_unitario,
                ];
                $this->selectedItems[$item->id] = [
                    'selected' => false,
                    'quantity' => 0,
                    'max' => $item->quantidade
                ];
            }
        }
    }

    public function submit()
    {
        $toReturn = array_filter($this->selectedItems, fn($i) => $i['selected'] && $i['quantity'] > 0);

        if (empty($toReturn)) {
            session()->flash('error', 'Selecione ao menos um item para devolução.');
            return;
        }

        DB::transaction(function () use ($toReturn) {
            $return = OrderReturn::create([
                'order_id' => $this->pedidoId,
                'customer_id' => $this->pedido->cliente_id,
                'status' => 'pending',
            ]);

            foreach ($toReturn as $itemId => $data) {
                $item = collect($this->items)->firstWhere('id', $itemId);
                OrderReturnItem::create([
                    'order_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $data['quantity'],
                    'unit_price' => $item['valor_unitario'],
                ]);
            }
        });

        session()->flash('message', 'Solicitação de devolução enviada com sucesso!');
        return redirect()->route('historico.financeiro');
    }

    public function render()
    {
        return view('livewire.returns.return-solicitation')
            ->layout('components.layouts.app.sidebar');
    }
}
