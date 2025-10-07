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
                        $q->where('produtos.nome', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.codigo_brcom', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.sku', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.preco_venda', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.preco_custo', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.codigo_barras', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.estoque_minimo', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.estoque_atual', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.descricao', 'like', "%{$normalizedTerm}%")
                            ->orWhere('produtos.ncm', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cor', function ($cq) use ($normalizedTerm) {
                                $cq->where('cores.nome', 'like', "%{$normalizedTerm}%")
                                    ->orWhere('cores.codigo_hex', 'like', "%{$normalizedTerm}%");
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
