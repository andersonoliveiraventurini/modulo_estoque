<?php

use App\Models\Orcamento;
use App\Models\RouteBillingApproval;
use App\Models\RouteBillingAttachment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RouteBillingApprovalManager extends Component
{
    public Orcamento $orcamento;
    public $status = 'approved';
    public $comments = '';

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(Orcamento $orcamento)
    {
        $this->orcamento = $orcamento;
        $this->authorize('approve', $this->orcamento);
    }

    public function approve()
    {
        $this->authorize('approve', $this->orcamento);

        $this->validate([
            'status' => 'required|in:approved,rejected,restrictions',
            'comments' => 'nullable|string|max:1000',
        ]);

        RouteBillingApproval::create([
            'orcamento_id' => $this->orcamento->id,
            'user_id' => Auth::id(),
            'status' => $this->status,
            'comments' => $this->comments,
        ]);

        $this->reset(['comments']);
        $this->dispatch('notify', 'Decisão de faturamento registrada!');
        $this->dispatch('refresh');
    }

    public function toggleAttachmentValidity($attachmentId)
    {
        $this->authorize('validateAttachment', $this->orcamento);

        $attachment = RouteBillingAttachment::findOrFail($attachmentId);
        $attachment->update([
            'is_valid' => !$attachment->is_valid,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);
        
        $this->dispatch('notify', 'Status do anexo atualizado!');
        $this->dispatch('refresh');
    }

    public function render()
    {
        return view('livewire.route-billing-approval-manager');
    }
}
