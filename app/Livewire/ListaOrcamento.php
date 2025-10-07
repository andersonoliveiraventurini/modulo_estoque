<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamento extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
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
        $this->resetPage();
    }

    public function render()
    {
        $orcamentos = Orcamento::query()
            ->with(['cliente', 'vendedor', 'endereco']) // Eager loading para relacionamentos
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));

                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);

                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('obra', 'like', "%{$normalizedTerm}%")
                          ->orWhere('valor_total', 'like', "%{$normalizedTerm}%")
                          ->orWhere('status', 'like', "%{$normalizedTerm}%")
                          ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                          // busca nos relacionamentos
                          ->orWhereHas('cliente', function ($q2) use ($normalizedTerm) {
                              $q2->where('nome', 'like', "%{$normalizedTerm}%");
                          })
                          ->orWhereHas('vendedor', function ($q2) use ($normalizedTerm) {
                              $q2->where('name', 'like', "%{$normalizedTerm}%");
                          })
                          ->orWhereHas('endereco', function ($q2) use ($normalizedTerm) {
                              $q2->where('logradouro', 'like', "%{$normalizedTerm}%")
                                 ->orWhere('cidade', 'like', "%{$normalizedTerm}%");
                          });
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento', [
            'orcamentos' => $orcamentos,
        ]);
    }
}
