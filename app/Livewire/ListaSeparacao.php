<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaSeparacao extends Component
{
    use WithPagination;

    public $search = '';
    public $cliente = '';
    public $vendedor = '';
    public $dataInicio = '';
    public $dataFim = '';
    public $workflowStatus = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'cliente' => ['except' => ''],
        'vendedor' => ['except' => ''],
        'dataInicio' => ['except' => ''],
        'dataFim' => ['except' => ''],
        'workflowStatus' => ['except' => ''],
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

    public function updatingWorkflowStatus()
    {
        $this->resetPage();
    }

    public function limparFiltros()
    {
        $this->reset(['search', 'cliente', 'vendedor', 'dataInicio', 'dataFim', 'workflowStatus']);
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

    public function iniciarSeparacao($orcamentoId)
    {
        $orcamento = Orcamento::findOrFail($orcamentoId);
        $orcamento->update(['workflow_status' => 'em_separacao']);
        
        session()->flash('message', 'Separação iniciada com sucesso!');
    }

    public function finalizarSeparacao($orcamentoId)
    {
        $orcamento = Orcamento::findOrFail($orcamentoId);
        $orcamento->update(['workflow_status' => 'aguardando_conferencia']);
        
        session()->flash('message', 'Separação finalizada! Orçamento enviado para conferência.');
    }

    public function render()
    {
        $orcamentos = Orcamento::query()
            ->with(['cliente', 'vendedor', 'endereco'])
            
            // Filtrar apenas orçamentos em separação
            ->whereIn('workflow_status', ['aguardando_separacao', 'em_separacao'])

            // Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('obra', 'like', "%{$normalizedTerm}%")
                            ->orWhere('valor_total', 'like', "%{$normalizedTerm}%")
                            ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cliente', fn($q2) => $q2->where('nome', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('vendedor', fn($q2) => $q2->where('name', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('endereco', fn($q2) => $q2
                                ->where('logradouro', 'like', "%{$normalizedTerm}%")
                                ->orWhere('cidade', 'like', "%{$normalizedTerm}%"));
                    });
                }
            })

            // Filtro específico por cliente
            ->when($this->cliente, function ($query) {
                $query->whereHas('cliente', fn($q) =>
                    $q->where('nome', 'like', "%{$this->cliente}%"));
            })

            // Filtro específico por vendedor
            ->when($this->vendedor, function ($query) {
                $query->whereHas('vendedor', fn($q) =>
                    $q->where('name', 'like', "%{$this->vendedor}%"));
            })

            // Filtro por workflow_status
            ->when($this->workflowStatus, function ($query) {
                $query->where('workflow_status', $this->workflowStatus);
            })

            // Filtro por intervalo de datas
            ->when($this->dataInicio, function ($query) {
                $query->whereDate('created_at', '>=', $this->dataInicio);
            })
            ->when($this->dataFim, function ($query) {
                $query->whereDate('created_at', '<=', $this->dataFim);
            })

            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-separacao', [
            'orcamentos' => $orcamentos,
        ]);
    }
}