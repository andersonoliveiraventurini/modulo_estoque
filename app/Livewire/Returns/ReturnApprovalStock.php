<?php

namespace App\Livewire\Returns;

use App\Models\OrderReturn;
use App\Services\FinancialService;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnApprovalStock extends Component
{
    use WithPagination;

    public $selectedReturnId;
    public $approvedQuantities = []; // item_id => quantity

    public function mount()
    {
        $this->loadQuantities();
    }

    public function loadQuantities()
    {
        $returns = OrderReturn::query()->with('items')->where('status', 'sales_approved')->get();
        foreach($returns as $return) {
            foreach($return->items as $item) {
                $this->approvedQuantities[$item->id] = $item->quantity_requested;
            }
        }
    }

    public function approve($returnId)
    {
        $return = OrderReturn::with('items')->findOrFail($returnId);
        
        foreach($return->items as $item) {
            $item->update([
                'quantity_approved' => $this->approvedQuantities[$item->id] ?? 0
            ]);
        }

        $return->update([
            'status' => 'stock_approved',
            'stock_supervisor_id' => auth()->id(),
            'stock_approved_at' => now(),
        ]);

        // Gerar crédito automático
        app(FinancialService::class)->generateCreditFromReturn($return);

        $return->update(['status' => 'credited']);

        session()->flash('message', 'Devolução aprovada e crédito gerado com sucesso!');
    }

    public function refuse($returnId, $reason)
    {
        $return = OrderReturn::findOrFail($returnId);
        $return->update([
            'status' => 'refused',
            'stock_supervisor_id' => auth()->id(),
            'refusal_reason' => $reason,
        ]);

        session()->flash('message', 'Devolução recusada pelo estoque.');
    }

    public function render()
    {
        $pendingReturns = OrderReturn::query()
            ->with(['customer', 'order', 'items.product'])
            ->where('status', 'sales_approved')
            ->latest()
            ->paginate(10);

        return view('livewire.returns.return-approval-stock', [
            'pendingReturns' => $pendingReturns
        ])->layout('components.layouts.app.sidebar');
    }
}
