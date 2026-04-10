<?php

namespace App\Livewire;

use App\Models\Orcamento;
use Livewire\Component;

class KanbanOrcamentos extends Component
{
    public $search = '';
    public $statusFilter = '';
    public $clienteFilter = '';
    public $vendedorFilter = '';

    protected $listeners = ['orcamentoAtualizado' => '$refresh'];

    public function columns()
    {
        return [
            [
                'id' => 'aguardando_separacao',
                'title' => 'Aguardando Separação',
                'workflow_status' => 'aguardando_separacao',
                'color' => 'zinc',
                'icon' => 'clock',
                'description' => 'Orçamentos ainda não aprovados'
            ],
            [
                'id' => 'em_separacao',
                'title' => 'Em Separação',
                'workflow_status' => 'em_separacao',
                'color' => 'blue',
                'icon' => 'cube',
                'description' => 'Itens sendo separados no estoque'
            ],
            [
                'id' => 'aguardando_conferencia',
                'title' => 'Aguardando Conferência',
                'workflow_status' => 'aguardando_conferencia',
                'color' => 'yellow',
                'icon' => 'pause',
                'description' => 'Separação concluída, aguardando conferência'
            ],
            [
                'id' => 'em_conferencia',
                'title' => 'Em Conferência',
                'workflow_status' => 'em_conferencia',
                'color' => 'purple',
                'icon' => 'check-circle',
                'description' => 'Conferindo itens separados'
            ],
            [
                'id' => 'conferido',
                'title' => 'Conferido',
                'workflow_status' => 'conferido',
                'color' => 'green',
                'icon' => 'shield-check',
                'description' => 'Conferência aprovada, pronto para finalizar'
            ],
            [
                'id' => 'finalizado',
                'title' => 'Finalizado',
                'workflow_status' => 'finalizado',
                'color' => 'emerald',
                'icon' => 'check-badge',
                'description' => 'Processo completo'
            ]
        ];
    }

    public function getOrcamentosPorWorkflow($workflowStatus)
    {
        return Orcamento::query()
            ->with(['cliente', 'vendedor', 'endereco'])
            ->where('workflow_status', $workflowStatus)
            ->where('status', '!=', 'Cancelado') // Não mostrar cancelados no board
            ->where('status', '!=', 'Pendente') // Não mostrar pendentes no board
            
            // 🔎 Busca geral
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('id', 'like', "%{$normalizedTerm}%")
                            ->orWhere('obra', 'like', "%{$normalizedTerm}%")
                            ->orWhere('valor_total_itens', 'like', "%{$normalizedTerm}%")
                            ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('cliente', fn($q2) => $q2->where('nome', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('vendedor', fn($q2) => $q2->where('name', 'like', "%{$normalizedTerm}%"))
                            ->orWhereHas('endereco', fn($q2) => $q2
                                ->where('logradouro', 'like', "%{$normalizedTerm}%")
                                ->orWhere('cidade', 'like', "%{$normalizedTerm}%"));
                    });
                }
            })
            
            // Filtro por cliente
            ->when($this->clienteFilter, function ($query) {
                $query->whereHas('cliente', fn($q) =>
                    $q->where('nome', 'like', "%{$this->clienteFilter}%")
                );
            })
            
            // Filtro por vendedor
            ->when($this->vendedorFilter, function ($query) {
                $query->whereHas('vendedor', fn($q) =>
                    $q->where('name', 'like', "%{$this->vendedorFilter}%")
                );
            })
            
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateWorkflowStatus($orcamentoId, $newWorkflowStatus)
    {
        $orcamento = Orcamento::with(['consultaPrecoGrupo.itens'])->find($orcamentoId);
        
        if ($orcamento) {
            // ✅ Validação de Encomenda: Não permite avançar se houver encomendas não recebidas
            if ($newWorkflowStatus !== 'cancelado' && $orcamento->possuiEncomenda() && !$orcamento->encomendaTotalmenteRecebida()) {
                $this->dispatch('showNotification', [
                    'message' => 'Este orçamento possui itens de encomenda que ainda não foram totalmente recebidos no estoque.',
                    'type' => 'error'
                ]);
                return;
            }

            // ✅ Validação de Finalização: Só permite finalizar se o status for 'conferido'
            if ($newWorkflowStatus === 'finalizado' && !$orcamento->prontoParaFinalizar()) {
                $this->dispatch('showNotification', [
                    'message' => 'O orçamento deve passar pela conferência antes de ser finalizado.',
                    'type' => 'error'
                ]);
                return;
            }

            // Validar transições permitidas
            $validTransitions = $this->getValidTransitions($orcamento->workflow_status);
            
            if (in_array($newWorkflowStatus, $validTransitions) || $newWorkflowStatus === 'cancelado') {
                $orcamento->update([
                    'workflow_status' => $newWorkflowStatus
                ]);
                
                // Log de auditoria para rastreabilidade crítica
                \App\Models\AcaoAtualizar::create([
                    'descricao' => "Workflow do orçamento #{$orcamento->id} alterado manualmente para: {$newWorkflowStatus}",
                    'user_id' => auth()->id(),
                ]);

                $this->dispatch('orcamentoAtualizado');
                $this->dispatch('showNotification', [
                    'message' => "Orçamento #{$orcamento->id} movido para {$this->getWorkflowLabel($newWorkflowStatus)}",
                    'type' => 'success'
                ]);
            } else {
                $this->dispatch('showNotification', [
                    'message' => 'Transição não permitida pelo fluxo operacional.',
                    'type' => 'error'
                ]);
            }
        }
    }

    private function getValidTransitions($currentStatus)
    {
        $transitions = [
            'aguardando_separacao' => ['em_separacao', 'cancelado'],
            'em_separacao' => ['aguardando_conferencia', 'aguardando_separacao', 'cancelado'],
            'aguardando_conferencia' => ['em_conferencia', 'em_separacao', 'cancelado'],
            'em_conferencia' => ['conferido', 'aguardando_conferencia', 'cancelado'],
            'conferido' => ['finalizado', 'em_conferencia', 'cancelado'],
            'finalizado' => [],
            'cancelado' => []
        ];

        return $transitions[$currentStatus] ?? [];
    }

    private function getWorkflowLabel($status)
    {
        $labels = [
            'aguardando_separacao' => 'Aguardando Separação',
            'em_separacao' => 'Em Separação',
            'aguardando_conferencia' => 'Aguardando Conferência',
            'em_conferencia' => 'Em Conferência',
            'conferido' => 'Conferido',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado'
        ];

        return $labels[$status] ?? $status;
    }

    public function getTotaisPorWorkflow()
    {
        $totais = [];
        foreach ($this->columns() as $column) {
            $orcamentos = $this->getOrcamentosPorWorkflow($column['workflow_status']);
            $totais[$column['workflow_status']] = [
                'quantidade' => $orcamentos->count(),
                'valor_total' => $orcamentos->sum('valor_total_itens')
            ];
        }
        return $totais;
    }

    public function limparFiltros()
    {
        $this->reset(['search', 'clienteFilter', 'vendedorFilter', 'statusFilter']);
    }

    public function render()
    {
        $columns = collect($this->columns())->map(function ($column) {
            $orcamentos = $this->getOrcamentosPorWorkflow($column['workflow_status']);
            $valorTotal = $orcamentos->sum('valor_total_itens');
            
            return array_merge($column, [
                'count' => $orcamentos->count(),
                'valor_total' => $valorTotal,
                'orcamentos' => $orcamentos
            ]);
        });

        return view('livewire.kanban-orcamentos', [
            'columns' => $columns,
            'totais' => $this->getTotaisPorWorkflow()
        ]);
    }
}