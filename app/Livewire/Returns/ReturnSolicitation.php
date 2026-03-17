<?php

namespace App\Livewire\Returns;

use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Pedido;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ReturnSolicitation extends Component
{
    use WithPagination;

    public $pedidoId;
    public $pedido;
    public $items = [];
    public $selectedItems = []; // item_id => [selected => bool, quantity => float]
    public $orderSearch = '';

    public function mount($pedidoId = null)
    {
        if (!$pedidoId) {
            return;
        }

        $this->pedidoId = $pedidoId;
        $this->pedido = Pedido::with(['cliente', 'descontos'])->findOrFail($pedidoId);
        
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
                $item = (array) collect($this->items)->firstWhere('id', (int)$itemId);
                
                if (empty($item)) continue;

                OrderReturnItem::create([
                    'order_return_id' => $return->id,
                    'product_id' => $item['product_id'] ?? null,
                    'quantity_requested' => $data['quantity'],
                    'unit_price' => $item['valor_unitario'] ?? 0,
                ]);
            }
        });

        session()->flash('message', 'Solicitação de devolução enviada com sucesso!');
        return redirect()->route('devolucoes.solicitar_index');
    }

    public function selectPedido($id)
    {
        return redirect()->route('devolucoes.solicitar.pedido', $id);
    }

    public function render()
    {
        $recentOrders = [];
        if (!$this->pedidoId) {
            $recentOrders = Pedido::query()
                ->with('cliente')
                ->where('status', 'aprovado')
                ->when($this->orderSearch, function($q) {
                    $q->where('id', 'like', "%{$this->orderSearch}%")
                      ->orWhereHas('cliente', function($query) {
                          $query->where('nome', 'like', "%{$this->orderSearch}%");
                      });
                })
                ->latest()
                ->paginate(10);
        }

        return view('livewire.returns.return-solicitation', [
            'recentOrders' => $recentOrders
        ])
            ->layout('components.layouts.app');
    }
}
