<?php

namespace App\Livewire;

use App\Models\ConsultaPreco;
use Livewire\Component;
use Livewire\WithPagination;

class ListaConsultaPreco extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
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
        $precos = ConsultaPreco::query()
        ->when($this->search, function ($query) {
            // Divide a string em palavras (tokens)
            $terms = preg_split('/\s+/', trim($this->search));

            foreach ($terms as $term) {
                // Normaliza números no formato brasileiro (ex: 19,55 → 19.55)
                $normalizedTerm = str_replace(',', '.', $term);

                $query->where(function ($q) use ($normalizedTerm) {
                    $q->where('descricao', 'like', "%{$normalizedTerm}%")
                      ->orWhere('cor', 'like', "%{$normalizedTerm}%")
                      ->orWhere('preco', 'like', "%{$normalizedTerm}%")
                      ->orWhere('preco_venda', 'like', "%{$normalizedTerm}%")
                      ->orWhere('observacao', 'like', "%{$normalizedTerm}%");
                });
            }
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);

        return view('livewire.lista-consulta-preco', [
            'precos' => $precos,
        ]);
    }
}
