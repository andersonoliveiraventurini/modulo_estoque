<?php

namespace App\Livewire;

use App\Models\Desconto;
use Livewire\Component;
use Livewire\WithPagination;

class ListaDesconto extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
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

    // Funções específicas de ordenação
    public function sortByMotivo()
    {
        $this->sortBy('motivo');
    }

    public function sortByValor()
    {
        $this->sortBy('valor');
    }

    public function sortByPorcentagem()
    {
        $this->sortBy('porcentagem');
    }

    public function sortByTipo()
    {
        $this->sortBy('tipo');
    }

    public function sortByCliente()
    {
        $this->sortBy('cliente_id');
    }

    public function sortByCreatedAt()
    {
        $this->sortBy('created_at');
    }

    public function render()
    {
        $descontos = Desconto::query()
            ->with(['cliente', 'orcamento', 'pedido', 'user'])
            ->when($this->search, function ($query) {
                // Divide a busca em palavras (tokens)
                $terms = preg_split('/\s+/', trim($this->search));

                foreach ($terms as $term) {
                    // Normaliza números no formato brasileiro (ex: 19,55 → 19.55)
                    $normalizedTerm = str_replace(',', '.', $term);

                    $query->where(function ($q) use ($normalizedTerm, $term) {
                        $q->where('motivo', 'like', "%{$term}%")
                            ->orWhere('valor', 'like', "%{$normalizedTerm}%")
                            ->orWhere('porcentagem', 'like', "%{$normalizedTerm}%")
                            ->orWhere('tipo', 'like', "%{$term}%")
                            // Busca por nome do cliente
                            ->orWhereHas('cliente', function ($clienteQuery) use ($term) {
                                $clienteQuery->where('nome_fantasia', 'like', "%{$term}%")
                                    ->orWhere('razao_social', 'like', "%{$term}%")
                                    ->orWhere('nome', 'like', "%{$term}%");
                            })
                            // Busca por ID do orçamento
                            ->orWhereHas('orcamento', function ($orcamentoQuery) use ($term) {
                                $orcamentoQuery->where('id', 'like', "%{$term}%");
                            })
                            // Busca por ID do pedido
                            ->orWhereHas('pedido', function ($pedidoQuery) use ($term) {
                                $pedidoQuery->where('id', 'like', "%{$term}%");
                            })
                            // Busca por nome do usuário
                            ->orWhereHas('user', function ($userQuery) use ($term) {
                                $userQuery->where('name', 'like', "%{$term}%");
                            });
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-desconto', [
            'descontos' => $descontos,
        ]);
    }
}