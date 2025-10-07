<?php

namespace App\Livewire;

use App\Models\Bloqueio;
use Livewire\Component;
use Livewire\WithPagination;

class ListaBloqueiosCliente extends Component
{
    use WithPagination;

    public $clienteId; // ID do cliente que será passado para o componente
    public $nome; // Nome do cliente para exibição no título
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Recebe o cliente_id ao montar o componente
    public function mount($clienteId, $clienteNome)
    {   
        $this->clienteId = $clienteId;
        $this->nome = $clienteNome;
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
        $bloqueios = Bloqueio::query()
        ->where('cliente_id', $this->clienteId)
        ->when($this->search, function ($query) {
            // Divide a busca em palavras (tokens)
            $terms = preg_split('/\s+/', trim($this->search));

            foreach ($terms as $term) {
                // Normaliza números no formato brasileiro (ex: 19,55 → 19.55)
                $normalizedTerm = str_replace(',', '.', $term);
                
                $query->where(function ($q) use ($normalizedTerm) {
                    $q->where('motivo', 'like', "%{$normalizedTerm}%");
                });
            }
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);


            $nome = $this->nome;

        return view('livewire.lista-bloqueios-cliente', compact(
            'bloqueios', 'nome'
        ));
    }
}
