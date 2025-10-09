<?php

namespace App\Livewire;

use App\Models\Produto;
use Livewire\Component;

class ListaProdutoFixoOrcamento extends Component
{
    public $search = '';
    public $sortField = 'nome';
    public $sortDirection = 'asc';
    public $perPage = 20; // quantidade inicial
    public $hasMore = true;

    public function loadMore()
    {
        $this->perPage += 20;
    }

    public function updatingSearch()
    {
        $this->reset('perPage');
        $this->perPage = 20;
    }

    public function render()
    {
        $produtos = Produto::query()
            ->with('fornecedor')
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('nome', 'like', "%{$normalizedTerm}%")
                            ->orWhere('id', 'like', "%{$normalizedTerm}%")
                            ->orWhere('sku', 'like', "%{$normalizedTerm}%")
                            ->orWhere('preco_venda', 'like', "%{$normalizedTerm}%")
                            ->orWhere('descricao', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('fornecedor', function ($fq) use ($normalizedTerm) {
                                $fq->where('nome_fantasia', 'like', "%{$normalizedTerm}%");
                            });
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return view('livewire.lista-produto-fixo-orcamento', [
            'produtos' => $produtos,
        ]);
    }
}
