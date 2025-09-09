<?php

namespace App\Livewire;

use App\Models\AnaliseCredito;
use Livewire\Component;
use Livewire\WithPagination;

class ListaAnaliseCreditoCliente extends Component
{
    use WithPagination;

    public $clienteId; // ID do cliente que será passado para o componente
    public $clienteNome; // Nome do cliente que será passado para o componente
    public $search = '';
    public $sortField = 'validade'; // pode alterar para outro campo se quiser
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'validade'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Recebe o cliente_id ao montar o componente
    public function mount($clienteId, $clienteNome)
    {
        $this->clienteId = $clienteId;
        $this->clienteNome = $clienteNome;
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
        $analises = AnaliseCredito::query()
            ->where('cliente_id', $this->clienteId)
            ->when($this->search, function ($query) {
                $query->where('observacoes', 'like', '%' . $this->search . '%')
                    ->orWhere('limite_credito', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $clienteNome = $this->clienteNome;

        return view('livewire.lista-analise-credito-cliente', compact(
            'analises',
            'clienteNome'
        ));
    }
}
