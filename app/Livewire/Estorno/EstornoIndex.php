<?php

namespace App\Livewire\Estorno;

use App\Models\Estorno;
use App\Services\EstornoService;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class EstornoIndex extends Component
{
    use WithPagination;

    public $status = '';
    public $solicitante = '';

    protected $listeners = ['estorno-decidido' => '$refresh'];

    public function concluir(EstornoService $estornoService, $estornoId)
    {
        $estorno = Estorno::findOrFail($estornoId);
        
        $this->authorize('conclude', $estorno);

        try {
            $estornoService->concluir(auth()->user(), $estorno);
            session()->flash('success', 'Estorno marcado como concluído (pago)!');
        } catch (Exception $e) {
            $this->addError('geral', 'Erro ao concluir o estorno: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Estorno::class);

        $query = Estorno::with(['pagamento', 'solicitante', 'aprovador'])->latest();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->solicitante) {
            $query->whereHas('solicitante', function ($q) {
                $q->where('name', 'like', '%' . $this->solicitante . '%');
            });
        }

        return view('livewire.estorno.estorno-index', [
            // Padrão de paginação do Laravel
            'estornos' => $query->paginate(15)
        ])->layout('components.layouts.app', ['title' => 'Lista de Estornos']);
    }
}
