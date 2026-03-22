<?php

namespace App\Livewire\Estoque;

use App\Models\Orcamento;
use App\Services\EstoqueService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class EncomendaRetirada extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingId = null;

    protected $estoqueService;

    public function boot(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmarRetirada($id)
    {
        $this->confirmingId = $id;
    }

    public function processarRetirada()
    {
        if (!$this->confirmingId) return;

        try {
            $orcamento = Orcamento::findOrFail($this->confirmingId);

            if (!$orcamento->isEncomenda()) {
                throw new \Exception('Este orçamento não é uma encomenda.');
            }

            if ($orcamento->retirado_em) {
                throw new \Exception('Esta encomenda já foi retirada em ' . $orcamento->retirado_em->format('d/m/Y H:i') . '.');
            }

            // Realiza a baixa definitiva de estoque
            $this->estoqueService->baixarEstoqueDefinitivo($orcamento);

            // Marca como retirado
            $orcamento->update([
                'retirado_em' => now(),
                'retirado_por_id' => auth()->id(),
                'status' => 'Concluido' // Opcional: define um status final
            ]);

            Log::info("Encomenda #{$orcamento->id} retirada confirmada por " . auth()->user()->name);

            $this->confirmingId = null;
            session()->flash('success', 'Retirada da Encomenda #' . $orcamento->id . ' confirmada com sucesso! Estoque atualizado.');

        } catch (\Exception $e) {
            Log::error("Erro ao confirmar retirada de encomenda: " . $e->getMessage());
            session()->flash('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $encomendas = Orcamento::query()
            ->whereNotNull('encomenda')
            ->where('status', 'Pago')
            ->whereNull('retirado_em')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function ($sq) {
                          $sq->where('nome', 'like', '%' . $this->search . '%')
                             ->orWhere('cnpj', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->with(['cliente', 'vendedor'])
            ->latest()
            ->paginate(15);

        return view('livewire.estoque.encomenda-retirada', [
            'encomendas' => $encomendas
        ]);
    }
}
