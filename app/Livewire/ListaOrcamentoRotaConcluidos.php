<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class ListaOrcamentoRotaConcluidos extends Component
{
    use WithPagination;

    public $search        = '';
    public $cliente       = '';
    public $vendedor      = '';
    public $loadingDay    = '';
    public $billingStatus = '';   // pending | approved | restrictions | rejected
    public $dataInicio    = '';
    public $dataFim       = '';
    public $sortField     = 'id';
    public $sortDirection = 'desc';
    public $perPage       = 10;

    /** Tipos de transporte que pertencem à Rota */
    const ROUTE_TRANSPORT_IDS = [1, 2, 3, 6, 7];

    protected $queryString = [
        'search'        => ['except' => ''],
        'cliente'       => ['except' => ''],
        'vendedor'      => ['except' => ''],
        'loadingDay'    => ['except' => ''],
        'billingStatus' => ['except' => ''],
        'dataInicio'    => ['except' => ''],
        'dataFim'       => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()        { $this->resetPage(); }
    public function updatingCliente()       { $this->resetPage(); }
    public function updatingVendedor()      { $this->resetPage(); }
    public function updatingLoadingDay()    { $this->resetPage(); }
    public function updatingBillingStatus() { $this->resetPage(); }
    public function updatingDataInicio()    { $this->resetPage(); }
    public function updatingDataFim()       { $this->resetPage(); }

    public function limparFiltros(): void
    {
        $this->reset([
            'search', 'cliente', 'vendedor', 'loadingDay',
            'billingStatus', 'dataInicio', 'dataFim',
        ]);
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
        Log::info('ListaOrcamentoRotaConcluidos: carregando listagem de pedidos de rota.');

        $orcamentos = Orcamento::query()
            ->with([
                'cliente',
                'vendedor',
                'endereco',
                'pagamentos'    => fn ($q) => $q->where('estornado', false)->latest()->limit(1),
                'pagamentos.formas.condicaoPagamento',
                'routeBillingApprovals' => fn ($q) => $q->latest()->limit(1),
                'routeBillingAttachments',
            ])

            // Apenas orçamentos de Rota
            ->whereHas('transportes', fn ($q) => $q->whereIn('tipos_transportes.id', self::ROUTE_TRANSPORT_IDS))
            ->whereIn('workflow_status', ['conferido', 'finalizado'])

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
                            ->orWhereHas('vendedor', fn ($q2) => $q2->where('name', 'like', "%{$normalized}%"));
                    });
                }
            })

            // Filtro por cliente
            ->when($this->cliente, fn ($q) =>
                $q->whereHas('cliente', fn ($q2) => $q2->where('nome', 'like', "%{$this->cliente}%"))
            )

            // Filtro por vendedor
            ->when($this->vendedor, fn ($q) =>
                $q->whereHas('vendedor', fn ($q2) => $q2->where('name', 'like', "%{$this->vendedor}%"))
            )

            // Filtro por dia de carregamento
            ->when($this->loadingDay, fn ($q) =>
                $q->where('loading_day', $this->loadingDay)
            )

            // Filtro por status de aprovação do financeiro
            ->when($this->billingStatus, function ($q) {
                if ($this->billingStatus === 'pending') {
                    // Pedidos que nunca receberam aprovação
                    $q->whereDoesntHave('routeBillingApprovals');
                } else {
                    $q->whereHas('routeBillingApprovals', function ($q2) {
                        $q2->where('status', $this->billingStatus)
                           ->whereIn('id', function ($sub) {
                               $sub->selectRaw('MAX(id)')
                                   ->from('route_billing_approvals')
                                   ->groupBy('orcamento_id');
                           });
                    });
                }
            })

            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim,    fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento-rota-concluidos', [
            'orcamentos' => $orcamentos,
        ]);
    }
}
