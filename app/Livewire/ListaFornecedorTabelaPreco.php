<?php

namespace App\Livewire;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class ListaFornecedorTabelaPreco extends Component
{
    use WithPagination;

    public $fornecedorId; // Id do fornecedor
    public $search = '';
    public $sortField = 'nome';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nome'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount($fornecedorId)
    {
        $this->fornecedorId = $fornecedorId;
    }

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
        ->where('fornecedor_id', $this->fornecedorId)
        ->when($this->search, function ($query) {
            // Divide a busca em palavras (tokens)
            $terms = preg_split('/\s+/', trim($this->search));

            foreach ($terms as $term) {
                // Normaliza números no formato brasileiro (ex: 19,55 → 19.55)
                $normalizedTerm = str_replace(',', '.', $term);
                
                $query->where(function ($q) use ($normalizedTerm) {
                    $q->where('nome', 'like', "%{$normalizedTerm}%")
                    ->orWhere('codigo_brcom', 'like', "%{$normalizedTerm}%")
                    ->orWhere('sku', 'like', "%{$normalizedTerm}%")
                    ->orWhere('codigo_barras', 'like', "%{$normalizedTerm}%")
                    ->orWhere('marca', 'like', "%{$normalizedTerm}%")
                    ->orWhere('modelo', 'like', "%{$normalizedTerm}%")
                    ->orWhere('estoque_minimo', 'like', "%{$normalizedTerm}%")
                    ->orWhere('estoque_atual', 'like', "%{$normalizedTerm}%")
                    ->orWhere('ncm', 'like', "%{$normalizedTerm}%")
                    ->orWhere('preco_custo', 'like', "%{$normalizedTerm}%")
                    ->orWhere('preco_venda', 'like', "%{$normalizedTerm}%");
                });
            }
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);

        return view('livewire.lista-fornecedor-tabela-preco', compact('produtos'));
        // layout('layouts.app');
    }
}
