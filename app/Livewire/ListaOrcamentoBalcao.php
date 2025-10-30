<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamentoBalcao extends Component
{
    use WithPagination;

    public $search = '';
    public $cliente = '';
    public $cidade = '';
    public $vendedor = '';
    public $dataInicio = '';
    public $dataFim = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'cliente' => ['except' => ''],
        'cidade' => ['except' => ''],
        'vendedor' => ['except' => ''],
        'dataInicio' => ['except' => ''],
        'dataFim' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCliente()
    {
        $this->resetPage();
    }

    public function updatingVendedor()
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
        $this->reset(['search', 'cliente', 'vendedor', 'dataInicio', 'dataFim']);
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
        $orcamentos = Orcamento::query()
            ->with(['cliente', 'vendedor', 'endereco'])

            // Pontos obrigatórios do Balcão
            ->whereIn('workflow_status', ['conferido', 'finalizado'])
            ->whereIn('status', ['Aprovado'])
            ->whereHas('transportes', function ($query) {
                $query->whereIn('tipo_transporte_id', [5, 6]);
            })
            // fim - pontos obrigatórios do Balcão

            // 🔎 Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('obra', 'like', "%{$normalizedTerm}%")
                            ->orWhere('valor_total', 'like', "%{$normalizedTerm}%")
                            ->orWhere('status', 'like', "%{$normalizedTerm}%")
                            ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cliente', fn($q2) => $q2->where('nome', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('vendedor', fn($q2) => $q2->where('name', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('endereco', fn($q2) => $q2
                                ->where('logradouro', 'like', "%{$normalizedTerm}%")
                                ->orWhere('cidade', 'like', "%{$normalizedTerm}%"));
                    });
                }
            })

            // 🧍‍♂️ Filtro específico por cliente
            ->when($this->cliente, function ($query) {
                $query->whereHas('cliente', fn($q) =>
                $q->where('nome', 'like', "%{$this->cliente}%"));
            })

            // 🧑‍💼 Filtro específico por vendedor
            ->when($this->vendedor, function ($query) {
                $query->whereHas('vendedor', fn($q) =>
                $q->where('name', 'like', "%{$this->vendedor}%"));
            })

            // 📅 Filtro por intervalo de datas (created_at)
            ->when($this->dataInicio, function ($query) {
                $query->whereDate('created_at', '>=', $this->dataInicio);
            })
            ->when($this->dataFim, function ($query) {
                $query->whereDate('created_at', '<=', $this->dataFim);
            })

            // 🧍‍♂️ Filtro específico por cliente (nome ou cidade)
            ->when($this->cliente, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('nome', 'like', "%{$this->cliente}%")
                        ->orWhereHas('enderecos', function ($q2) {
                            $q2->where('cidade', 'like', "%{$this->cliente}%");
                        });
                });
            })

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento-balcao', [
            'orcamentos' => $orcamentos,
        ]);
    }
}
