<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamentoBalcaoConcluidos extends Component
{
    use WithPagination;

    public $search = '';
    public $cliente = '';
    public $cidade = '';
    public $vendedor = '';
    public $loadingDay = '';
    public $dataInicio = '';
    public $dataFim = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search'        => ['except' => ''],
        'cliente'       => ['except' => ''],
        'cidade'        => ['except' => ''],
        'vendedor'      => ['except' => ''],
        'loadingDay'    => ['except' => ''],
        'dataInicio'    => ['except' => ''],
        'dataFim'       => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()     { $this->resetPage(); }
    public function updatingCliente()    { $this->resetPage(); }
    public function updatingCidade()     { $this->resetPage(); }
    public function updatingVendedor()   { $this->resetPage(); }
    public function updatingLoadingDay() { $this->resetPage(); }
    public function updatingDataInicio() { $this->resetPage(); }
    public function updatingDataFim()    { $this->resetPage(); }

    public function limparFiltros(): void
    {
        $this->reset(['search', 'cliente', 'cidade', 'vendedor', 'loadingDay', 'dataInicio', 'dataFim']);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
        $this->resetPage();
    }

    public function render()
    {
        $orcamentos = Orcamento::query()
            ->with([
                'cliente',
                'vendedor',
                'endereco',
                // Carrega o pagamento ativo mais recente + formas + condicao
                // Mesmo padrão do ListaOrcamentoConcluidos
                'pagamentos'                        => fn ($q) => $q->where('estornado', false)->latest()->limit(1),
                'pagamentos.formas.condicaoPagamento',
            ])

            // Filtros obrigatórios do Balcão
            ->whereIn('workflow_status', ['conferido', 'finalizado'])
            ->whereIn('status', ['Pago'])
            ->whereHas('transportes', fn ($q) => $q->whereIn('tipo_transporte_id', [4, 5]))

            // Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalized = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalized) {
                        $q->where('obra', 'like', "%{$normalized}%")
                            ->orWhere('valor_total', 'like', "%{$normalized}%")
                            ->orWhere('status', 'like', "%{$normalized}%")
                            ->orWhere('observacoes', 'like', "%{$normalized}%")
                            ->orWhereHas('cliente', fn ($q2) => $q2->where('nome', 'like', "%{$normalized}%"))
                            ->orWhereHas('vendedor', fn ($q2) => $q2->where('name', 'like', "%{$normalized}%"))
                            ->orWhereHas('endereco', fn ($q2) => $q2
                                ->where('logradouro', 'like', "%{$normalized}%")
                                ->orWhere('cidade', 'like', "%{$normalized}%"));
                    });
                }
            })

            // Filtro por nome do cliente
            ->when($this->cliente, fn ($q) =>
                $q->whereHas('cliente', fn ($q2) => $q2->where('nome', 'like', "%{$this->cliente}%"))
            )

            // Filtro por cidade (via endereços do cliente)
            ->when($this->cidade, fn ($q) =>
                $q->whereHas('cliente.enderecos', fn ($q2) =>
                    $q2->where('cidade', 'like', "%{$this->cidade}%")
                )
            )

            // Filtro por vendedor
            ->when($this->vendedor, fn ($q) =>
                $q->whereHas('vendedor', fn ($q2) => $q2->where('name', 'like', "%{$this->vendedor}%"))
            )

            // Filtro por dia de carregamento
            ->when($this->loadingDay, fn ($query) =>
                $query->where('loading_day', $this->loadingDay)
            )

            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim,    fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento-balcao-concluidos', [
            'orcamentos' => $orcamentos,
        ]);
    }
}