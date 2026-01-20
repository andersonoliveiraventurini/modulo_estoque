<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class ListaClientesBloqueados extends Component
{
    use WithPagination;

    public $search = '';
    public $nomeFantasia = '';
    public $razaoSocial = '';
    public $cnpj = '';
    public $vendedor = '';
    public $tratamento = '';
    public $dataInicio = '';
    public $dataFim = '';
    public $sortField = 'bloqueios.created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'nomeFantasia' => ['except' => ''],
        'razaoSocial' => ['except' => ''],
        'cnpj' => ['except' => ''],
        'vendedor' => ['except' => ''],
        'tratamento' => ['except' => ''],
        'dataInicio' => ['except' => ''],
        'dataFim' => ['except' => ''],
        'sortField' => ['except' => 'bloqueios.created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingNomeFantasia()
    {
        $this->resetPage();
    }

    public function updatingRazaoSocial()
    {
        $this->resetPage();
    }

    public function updatingCnpj()
    {
        $this->resetPage();
    }

    public function updatingVendedor()
    {
        $this->resetPage();
    }

    public function updatingTratamento()
    {
        $this->resetPage();
    }

    public function updatingDataInicio()
    {
        $this->resetPage();
    }

    public function updatingDataFim()
    {
        $this->resetPage();
    }

    public function limparFiltros()
    {
        $this->reset(['search', 'nomeFantasia', 'razaoSocial', 'cnpj', 'vendedor', 'tratamento', 'dataInicio', 'dataFim']);
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

            // 🔎 Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->where('nome_fantasia', 'like', "%{$term}%")
                          ->orWhere('razao_social', 'like', "%{$term}%")
                          ->orWhere('tratamento', 'like', "%{$term}%")
                          ->orWhere('cnpj', 'like', "%{$term}%")
                          ->orWhereHas('vendedor', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                          ->orWhereHas('ultimoBloqueio', fn($q2) => $q2->where('motivo', 'like', "%{$term}%"));
                    });
                }
            })

            // 🏢 Filtro específico por nome fantasia
            ->when($this->nomeFantasia, function ($query) {
                $query->where('nome_fantasia', 'like', "%{$this->nomeFantasia}%");
            })

            // 📋 Filtro específico por razão social
            ->when($this->razaoSocial, function ($query) {
                $query->where('razao_social', 'like', "%{$this->razaoSocial}%");
            })

            // 🆔 Filtro específico por CNPJ
            ->when($this->cnpj, function ($query) {
                $query->where('cnpj', 'like', "%{$this->cnpj}%");
            })

            // 👤 Filtro específico por tratamento
            ->when($this->tratamento, function ($query) {
                $query->where('tratamento', 'like', "%{$this->tratamento}%");
            })

            // 🧑‍💼 Filtro específico por vendedor
            ->when($this->vendedor, function ($query) {
                $query->whereHas('vendedor', fn($q) =>
                    $q->where('name', 'like', "%{$this->vendedor}%")
                );
            })

            // 📅 Filtro por intervalo de datas (data do bloqueio)
            ->when($this->dataInicio || $this->dataFim, function ($query) {
                $query->whereHas('bloqueios', function ($q) {
                    if ($this->dataInicio) {
                        $q->whereDate('created_at', '>=', $this->dataInicio);
                    }
                    if ($this->dataFim) {
                        $q->whereDate('created_at', '<=', $this->dataFim);
                    }
                });
            })

            // Join para pegar o último bloqueio
            ->leftJoin('bloqueios', function ($join) {
                $join->on('clientes.id', '=', 'bloqueios.cliente_id')
                    ->whereNull('bloqueios.deleted_at');
            })
            ->with(['ultimoBloqueio.user', 'vendedor']) // eager load
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-clientes-bloqueados', compact('clientes'));
    }
}