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
        ->with('user') // já traz o relacionamento
        ->when($this->search, function ($query) {
            $terms = preg_split('/\s+/', trim($this->search));

            foreach ($terms as $term) {
                $normalizedTerm = str_replace(',', '.', $term);

                $query->where(function ($q) use ($normalizedTerm) {
                    $q->where('desconto', 'like', "%{$normalizedTerm}%")
                    ->orWhere('user_id', 'like', "%{$normalizedTerm}%")
                    ->orWhereHas('user', function ($uq) use ($normalizedTerm) {
                        $uq->where('name', 'like', "%{$normalizedTerm}%");
                    });
                });
            }
        })
        ->when($this->sortField === 'nome', function ($query) {
            // ordenar pelo nome do usuário
            $query->join('users', 'vendedores.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->sortDirection)
                ->select('vendedores.*'); // evita conflito de colunas
        }, function ($query) {
            $query->orderBy($this->sortField, $this->sortDirection);
        })
        ->paginate($this->perPage);


        return view('livewire.lista-vendedores', [
            'vendedores' => $vendedores,
        ]);
    }
}
