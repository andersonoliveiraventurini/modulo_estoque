<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamentoConcluidos extends Component
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
        'search'        => ['except' => ''],
        'cliente'       => ['except' => ''],
        'cidade'        => ['except' => ''],
        'vendedor'      => ['except' => ''],
        'dataInicio'    => ['except' => ''],
        'dataFim'       => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingCliente()   { $this->resetPage(); }
    public function updatingCidade()    { $this->resetPage(); }
    public function updatingVendedor()  { $this->resetPage(); }
    public function updatingDataInicio(){ $this->resetPage(); }
    public function updatingDataFim()   { $this->resetPage(); }

    public function limparFiltros()
    {
        $this->reset(['search', 'cliente', 'cidade', 'vendedor', 'dataInicio', 'dataFim']);
        $this->resetPage();
    }

    public function sortBy($field)
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
                // Carrega apenas o pagamento ativo (não estornado) mais recente de cada orçamento
                'pagamentos' => fn ($q) => $q->where('estornado', false)->latest()->limit(1),
                'pagamentos.formas.condicaoPagamento',
            ])
            ->whereIn('workflow_status', ['conferido', 'finalizado'])
            ->whereIn('status', ['Pago'])

            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('obra', 'like', "%{$normalizedTerm}%")
                            ->orWhere('valor_total', 'like', "%{$normalizedTerm}%")
                            ->orWhere('status', 'like', "%{$normalizedTerm}%")
                            ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cliente', fn ($q2) => $q2->where('nome', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('vendedor', fn ($q2) => $q2->where('name', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('endereco', fn ($q2) => $q2
                                ->where('logradouro', 'like', "%{$normalizedTerm}%")
                                ->orWhere('cidade', 'like', "%{$normalizedTerm}%"));
                    });
                }
            })

            ->when($this->cliente, function ($query) {
                $query->whereHas('cliente', fn ($q) =>
                    $q->where('nome', 'like', "%{$this->cliente}%"));
            })

            ->when($this->cidade, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->whereHas('enderecos', fn ($q2) =>
                        $q2->where('cidade', 'like', "%{$this->cidade}%"));
                });
            })

            ->when($this->vendedor, function ($query) {
                $query->whereHas('vendedor', fn ($q) =>
                    $q->where('name', 'like', "%{$this->vendedor}%"));
            })

            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim,    fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-orcamento-concluidos', [
            'orcamentos' => $orcamentos,
        ]);
    }
}