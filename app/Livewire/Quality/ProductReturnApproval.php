<?php

namespace App\Livewire\Quality;

use App\Models\ProductReturn;
use App\Services\ProductReturnService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductReturnApproval extends Component
{
    public $returnId;
    public $return;
    
    // Approval fields
    public $status_approval = 'aprovado';
    public $observacoes;
    public $retorno_estoque = true;

    protected $rules = [
        'status_approval' => 'required|in:aprovado,negado',
        'observacoes' => 'nullable|string',
    ];

    public function mount($return, $action = null)
    {
        $this->returnId = $return;
        $this->return = ProductReturn::with(['orcamento.cliente', 'items.produto', 'authorizations.user'])->findOrFail($return);
        
        // Se a ação for negar, pré-seleciona
        if ($action === 'deny') {
            $this->status_approval = 'negado';
        }

        // Se o retorno já estiver finalizado ou negado, o formulário de decisão não deve ser exibido (modo visualização)
    }

    public function approve(ProductReturnService $service)
    {
        $this->validate();

        $isApproved = $this->status_approval === 'aprovado';

        if ($this->return->status === 'pendente_supervisor') {
            $service->authorizeSupervisor($this->return, $isApproved, $this->observacoes);
            session()->flash('success', $isApproved ? 'Aprovação do Supervisor registrada. Aguardando Estoque.' : 'Devolução negada pelo Supervisor.');
        } 
        elseif ($this->return->status === 'pendente_estoque') {
            $service->authorizeEstoque($this->return, $isApproved, [
                'retorno_estoque' => $this->retorno_estoque,
                'observacoes_estoque' => $this->observacoes,
            ]);
            session()->flash('success', $isApproved ? 'Devolução finalizada com sucesso! Créditos gerados.' : 'Devolução negada pelo Estoque.');
        }

        return redirect()->route('quality.dashboard');
    }

    public function downloadReturn($id, $type)
    {
        $return = ProductReturn::findOrFail($id);
        $path = "quality/return_{$type}_{$return->nr}.pdf";
        
        if (!Storage::disk('public')->exists($path)) {
            app(\App\Services\QualityPdfService::class)->generateReturnPdf($return, $type);
        }
        
        return response()->download(storage_path("app/public/{$path}"));
    }

    public function render()
    {
        return view('livewire.quality.product-return-approval')
            ->layout('components.layouts.app');
    }
}
