<?php

namespace App\Livewire;

use App\Models\Vendedor;
use Livewire\Component;
use Livewire\WithPagination;

class ListaVendedores extends Component
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
        $vendedores = Vendedor::query()
            ->when($this->search, function ($query) {
                // Divide a busca em palavras (tokens)
                $terms = preg_split('/\s+/', trim($this->search));

                foreach ($terms as $term) {
                    // Normaliza números no formato brasileiro (ex: 19,55 → 19.55)
                    $normalizedTerm = str_replace(',', '.', $term);

                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('user_id', 'like', "%{$normalizedTerm}%")
                            ->orWhere('desconto', 'like', "%{$normalizedTerm}%");
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-vendedores', [
            'vendedores' => $vendedores,
        ]);
    }
}
