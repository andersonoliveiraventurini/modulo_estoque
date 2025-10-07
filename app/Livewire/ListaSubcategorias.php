<?php

namespace App\Livewire;

use App\Models\SubCategoria;
use Livewire\Component;
use Livewire\WithPagination;

class ListaSubcategorias extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nome';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nome'],
        'sortDirection' => ['except' => 'asc'],
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
        $subcategorias = SubCategoria::query()
            ->with('categoria') // eager loading
            ->leftJoin('categorias', 'sub_categorias.categoria_id', '=', 'categorias.id')
            ->select('sub_categorias.*') // evita quebra do modelo
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->where('sub_categorias.nome', 'like', "%{$term}%")
                          ->orWhere('sub_categorias.descricao', 'like', "%{$term}%")
                          ->orWhereHas('categoria', function ($cq) use ($term) {
                              $cq->where('nome', 'like', "%{$term}%")
                                 ->orWhere('descricao', 'like', "%{$term}%");
                          });
                    });
                }
            })
            ->orderBy(
                $this->sortField === 'categoria' ? 'categorias.nome' : $this->sortField,
                $this->sortDirection
            )
            ->paginate($this->perPage);

        return view('livewire.lista-subcategorias', [
            'subcategorias' => $subcategorias,
        ]);
    }
}
