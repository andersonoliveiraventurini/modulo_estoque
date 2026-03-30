<?php

namespace App\Livewire\Quality;

use App\Models\ProductReturn;
use App\Services\ProductReturnService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ProductReturnApproval extends Component
{
    public $returnId;
    public $return;
    public $observacoes;
    public $retorno_estoque = false;

    public function mount($return)
    {
        $this->returnId = $return instanceof ProductReturn ? $return->id : $return;
        $this->loadReturn();
    }

    public function loadReturn()
    {
        $this->return = ProductReturn::with([
            'items.produto', 
            'cliente', 
            'vendedor', 
            'orcamento', 
            'authorizations.user'
        ])->findOrFail($this->returnId);
    }

    public function approve(ProductReturnService $service)
    {
        try {
            if ($this->return->status === 'pendente_supervisor') {
                $this->authorize('approveSupervisor', $this->return);
                $service->authorizeSupervisor($this->return, true, $this->observacoes);
                session()->flash('success', 'Aprovação do supervisor registrada com sucesso!');
            } elseif ($this->return->status === 'pendente_estoque') {
                $this->authorize('approveEstoque', $this->return);
                $service->authorizeEstoque($this->return, true, [
                    'observacoes_estoque' => $this->observacoes,
                    'retorno_estoque' => $this->retorno_estoque
                ]);
                session()->flash('success', 'Devolução finalizada e estoque atualizado!');
            }

            return redirect()->route('quality.dashboard');
        } catch (\Exception $e) {
            Log::error("Erro na aprovação da devolução: " . $e->getMessage());
            $this->addError('general', 'Erro ao aprovar: ' . $e->getMessage());
        }
    }

    public function reject(ProductReturnService $service)
    {
        if (empty($this->observacoes)) {
            $this->addError('observacoes', 'Por favor, informe o motivo da rejeição nas observações.');
            return;
        }

        try {
            if ($this->return->status === 'pendente_supervisor') {
                $this->authorize('approveSupervisor', $this->return);
                $service->authorizeSupervisor($this->return, false, $this->observacoes);
            } elseif ($this->return->status === 'pendente_estoque') {
                $this->authorize('approveEstoque', $this->return);
                $service->authorizeEstoque($this->return, false, ['observacoes_estoque' => $this->observacoes]);
            }

            session()->flash('warning', 'Devolução rejeitada.');
            return redirect()->route('quality.dashboard');
        } catch (\Exception $e) {
            Log::error("Erro na rejeição da devolução: " . $e->getMessage());
            $this->addError('general', 'Erro ao rejeitar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.quality.product-return-approval');
    }
}
