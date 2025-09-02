<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class ListaCliente extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nome_fantasia';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nome_fantasia'],
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

    // Funções específicas
    public function sortByCnpj()
    {
        $this->sortBy('cnpj');
    }

    public function sortByNomeFantasia()
    {
        $this->sortBy('nome_fantasia');
    }

    public function sortByTratamento()
    {
        $this->sortBy('tratamento');
    }

    public function sortByRazaoSocial()
    {
        $this->sortBy('razao_social');
    }

    public function sortByLimite()
    {
        $this->sortBy('limite');
    }

    public function sortByDesconto()
    {
        $this->sortBy('desconto');
    }

    public function render()
    {
        $clientes = Cliente::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('nome_fantasia', 'like', '%' . $this->search . '%')
                        ->orWhere('nome', 'like', '%' . $this->search . '%') // nome no brcom
                        ->orWhere('razao_social', 'like', '%' . $this->search . '%')
                        ->orWhere('tratamento', 'like', '%' . $this->search . '%')
                        ->orWhere('cnpj', 'like', '%' . $this->search . '%')
                        ->orWhere('desconto', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-cliente', [
            'clientes' => $clientes,
        ]);
    }
}