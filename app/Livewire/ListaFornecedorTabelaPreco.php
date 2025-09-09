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
                $query->where(function ($query) {
                    $query->where('nome', 'like', '%' . $this->search . '%')
                          ->orWhere('codigo_brcom', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                          ->orWhere('codigo_barras', 'like', '%' . $this->search . '%')
                          ->orWhere('marca', 'like', '%' . $this->search . '%')
                          ->orWhere('modelo', 'like', '%' . $this->search . '%')
                          ->orWhere('estoque_minimo', 'like', '%' . $this->search . '%')
                          ->orWhere('estoque_atual', 'like', '%' . $this->search . '%')
                          ->orWhere('ncm', 'like', '%' . $this->search . '%')
                          ->orWhere('preco_custo', 'like', '%' . $this->search . '%')
                          ->orWhere('preco_venda', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-fornecedor-tabela-preco', compact('produtos'));
        // layout('layouts.app');
    }
}
