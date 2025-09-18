<?php

namespace App\Livewire;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class ListaProduto extends Component
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
        $produtos = Produto::query()
            ->with(['fornecedor', 'cor']) // eager loading
            ->leftJoin('cores', 'produtos.cor_id', '=', 'cores.id') // join para ordenação
            ->select('produtos.*') // importante para não quebrar o modelo
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));

                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);

                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('nome', 'like', "%{$normalizedTerm}%")
                            ->orWhere('codigo_brcom', 'like', "%{$normalizedTerm}%")
                            ->orWhere('sku', 'like', "%{$normalizedTerm}%")
                            ->orWhere('preco_venda', 'like', "%{$normalizedTerm}%")
                            ->orWhere('preco_custo', 'like', "%{$normalizedTerm}%")
                            ->orWhere('codigo_barras', 'like', "%{$normalizedTerm}%")
                            ->orWhere('marca', 'like', "%{$normalizedTerm}%")
                            ->orWhere('modelo', 'like', "%{$normalizedTerm}%")
                            ->orWhere('estoque_minimo', 'like', "%{$normalizedTerm}%")
                            ->orWhere('estoque_atual', 'like', "%{$normalizedTerm}%")
                            ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhere('descricao', 'like', "%{$normalizedTerm}%")
                            ->orWhere('ncm', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cor', function ($cq) use ($normalizedTerm) {
                                $cq->where('nome', 'like', "%{$normalizedTerm}%")
                                    ->orWhere('codigo_hex', 'like', "%{$normalizedTerm}%");
                            });
                    });
                }
            })
            ->orderBy(
                $this->sortField === 'cor' ? 'cores.nome' : $this->sortField, // ordena por cores.nome se for 'cor'
                $this->sortDirection
            )
            ->paginate($this->perPage);

        return view('livewire.lista-produto', [
            'produtos' => $produtos,
        ]);
    }
}
