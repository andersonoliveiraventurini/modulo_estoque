<?php

namespace App\Livewire\Returns;

use App\Models\OrderReturn;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnApprovalSales extends Component
{
    use WithPagination;

    public $selectedReturnId;
    public $refusalReason;

    public function approve($returnId)
    {
        $return = OrderReturn::findOrFail($returnId);
        $return->update([
            'status' => 'sales_approved',
            'sales_supervisor_id' => auth()->id(),
            'sales_approved_at' => now(),
        ]);

        session()->flash('message', 'Devolução aprovada pelo supervisor de vendas.');
    }

    public function refuse($returnId)
    {
        $this->validate(['refusalReason' => 'required|string|min:5']);

        $return = OrderReturn::findOrFail($returnId);
        $return->update([
            'status' => 'refused',
            'sales_supervisor_id' => auth()->id(),
            'refusal_reason' => $this->refusalReason,
        ]);

        $this->selectedReturnId = null;
        $this->refusalReason = '';
        session()->flash('message', 'Devolução recusada.');
    }

    public function render()
    {
        $pendingReturns = OrderReturn::query()
            ->with(['customer', 'order', 'items.product'])
            ->whereStatus('pending')
            ->latest()
            ->paginate(10);

        return view('livewire.returns.return-approval-sales', [
            'pendingReturns' => $pendingReturns
        ])->layout('components.layouts.app.sidebar');
    }
}
