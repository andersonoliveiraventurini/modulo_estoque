<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class ListaCliente extends Component
{
    use WithPagination;

    public $search = '';
    public $vendedor = '';
    public $cidade = '';
    public $sortField = 'nome_fantasia';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search'        => ['except' => ''],
        'vendedor'      => ['except' => ''],
        'cidade'        => ['except' => ''],
        'sortField'     => ['except' => 'nome_fantasia'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()   { $this->resetPage(); }
    public function updatingVendedor() { $this->resetPage(); }
    public function updatingCidade()   { $this->resetPage(); }

    public function limparFiltros()
    {
        $this->reset(['search', 'vendedor', 'cidade']);
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
            ->with(['vendedor.user', 'vendedorExterno.user', 'enderecos'])

            //  Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('nome_fantasia', 'like', "%{$normalizedTerm}%")
                            ->orWhere('nome', 'like', "%{$normalizedTerm}%")
                            ->orWhere('razao_social', 'like', "%{$normalizedTerm}%")
                            ->orWhere('tratamento', 'like', "%{$normalizedTerm}%")
                            ->orWhere('cnpj', 'like', "%{$normalizedTerm}%")
                            ->orWhere('desconto', 'like', "%{$normalizedTerm}%");
                    });
                }
            })

            // 🧑‍💼 Filtro por vendedor (interno ou externo, busca pelo nome do User)
            ->when($this->vendedor, function ($query) {
                $query->where(function ($q) {
                    // vendedor interno
                    $q->whereHas('vendedor.user', fn($q2) =>
                        $q2->where('name', 'like', "%{$this->vendedor}%")
                    )
                    // vendedor externo
                    ->orWhereHas('vendedorExterno.user', fn($q2) =>
                        $q2->where('name', 'like', "%{$this->vendedor}%")
                    );
                });
            })

            // 🏙️ Filtro por cidade (em qualquer endereço do cliente)
            ->when($this->cidade, function ($query) {
                $query->whereHas('enderecos', fn($q) =>
                    $q->where('cidade', 'like', "%{$this->cidade}%")
                );
            })

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-cliente', [
            'clientes' => $clientes,
        ]);
    }
}