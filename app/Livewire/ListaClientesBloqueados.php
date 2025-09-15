<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class ListaClientesBloqueados extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'bloqueios.created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'bloqueios.created_at'],
        'sortDirection' => ['except' => 'desc'],
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
        $clientes = Cliente::query()
            ->select('clientes.*')
            ->where('clientes.bloqueado', true)
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->where('nome_fantasia', 'like', "%{$term}%")
                          ->orWhere('razao_social', 'like', "%{$term}%")
                          ->orWhere('tratamento', 'like', "%{$term}%")
                          ->orWhere('cnpj', 'like', "%{$term}%");
                    });
                }
            })
            // join para pegar o último bloqueio
            ->leftJoin('bloqueios', function ($join) {
                $join->on('clientes.id', '=', 'bloqueios.cliente_id')
                    ->whereNull('bloqueios.deleted_at');
            })
            ->with(['ultimoBloqueio.user']) // eager load
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-clientes-bloqueados', compact('clientes'));
    }
}
