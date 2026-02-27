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
                $terms = preg_split('/\s+/', trim($this->search));

                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);

                    $query->where(function ($q) use ($normalizedTerm, $term) {
                        $q->where('motivo', 'like', "%{$term}%")
                            ->orWhere('valor', 'like', "%{$normalizedTerm}%")
                            ->orWhere('porcentagem', 'like', "%{$normalizedTerm}%")
                            ->orWhere('tipo', 'like', "%{$term}%")
                            ->orWhereHas('cliente', function ($clienteQuery) use ($term) {
                                $clienteQuery->where('nome_fantasia', 'like', "%{$term}%")
                                    ->orWhere('razao_social', 'like', "%{$term}%")
                                    ->orWhere('nome', 'like', "%{$term}%");
                            })
                            ->orWhereHas('orcamento', function ($orcamentoQuery) use ($term) {
                                $orcamentoQuery->where('id', 'like', "%{$term}%");
                            })
                            ->orWhereHas('pedido', function ($pedidoQuery) use ($term) {
                                $pedidoQuery->where('id', 'like', "%{$term}%");
                            })
                            ->orWhereHas('user', function ($userQuery) use ($term) {
                                $userQuery->where('name', 'like', "%{$term}%");
                            });
                    });
                }
            })
            ->when($this->sortField === 'status', function ($query) {
                // Pendente = 1, Aprovado = 2, Rejeitado = 3
                $query->orderByRaw("
                    CASE
                        WHEN aprovado_por IS NULL AND rejeitado_por IS NULL THEN 1
                        WHEN aprovado_por IS NOT NULL THEN 2
                        WHEN rejeitado_por IS NOT NULL THEN 3
                    END {$this->sortDirection}
                ");
            }, function ($query) {
                $query->orderBy($this->sortField, $this->sortDirection);
            })
            ->paginate($this->perPage);

        return view('livewire.lista-desconto', [
            'descontos' => $descontos,
        ]);
    }
}