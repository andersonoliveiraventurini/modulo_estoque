<?php 
namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamentoCliente extends Component
{
    use WithPagination;

    public $clienteId;
    public $search = '';
    public $sortField = 'obra';
    public $sortDirection = 'asc';
    public $perPage = 10;

    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function render()
    {
        $orcamentos = Orcamento::query()
            ->where('cliente_id', $this->clienteId)
            ->with(['cliente'])
            ->when($this->search, function ($query) {
                $query->where('obra', 'like', "%{$this->search}%")
                      ->orWhere('status', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento-cliente', [
            'orcamentos' => $orcamentos,
        ]);
    }
}
