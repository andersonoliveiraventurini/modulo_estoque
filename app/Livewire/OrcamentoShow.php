<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orcamento;

class OrcamentoShow extends Component
{
    public $orcamento;
    public $status;
    public $desconto_aprovado;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount($id)
    {
        $this->orcamento = Orcamento::with(['cliente', 'itens', 'vidros', 'transporte'])->findOrFail($id);
        $this->status = $this->orcamento->status;
        $this->desconto_aprovado = $this->orcamento->desconto_aprovado;
    }

    public function atualizarStatus()
    {
        if ($this->status === 'Aprovado') {
            // Verifica disponibilidade descontando reservas de OUTROS orçamentos
            // mas ignorando reserva do próprio orçamento atual
            $itens = $this->orcamento->itens;
            $itensSemEstoque = [];
            $itensSemHub = [];
            $estoqueService = app(\App\Services\EstoqueService::class);

            foreach ($itens as $item) {
                $produto = $item->produto;
                if (!$produto) continue;

                $reservadoOutros = \App\Models\EstoqueReserva::where('produto_id', $produto->id)
                    ->where('status', 'ativa')
                    ->where('orcamento_id', '!=', $this->orcamento->id)
                    ->sum('quantidade');

                $disponivel = max(0, ($produto->estoque_atual ?? 0) - $reservadoOutros);
                $saldoHub = $estoqueService->getHubStock($produto->id);

                if ($disponivel < $item->quantidade) {
                    $itensSemEstoque[] = "{$produto->nome}: disponível {$disponivel}, necessário {$item->quantidade}";
                }

                if ($saldoHub < $item->quantidade) {
                    $itensSemHub[] = "{$produto->nome}: saldo no HUB {$saldoHub}, necessário {$item->quantidade}";
                }
            }

            if (!empty($itensSemEstoque) || !empty($itensSemHub)) {
                $statusAnterior = $this->orcamento->status;
                $this->orcamento->update([
                    'status' => 'Sem estoque',
                    'workflow_status' => null
                ]);
                $this->status = 'Sem estoque';
                
                $msg = '';
                if (!empty($itensSemEstoque)) {
                    $msg .= 'Estoque Global Insuficiente: ' . implode(' | ', $itensSemEstoque) . '. ';
                }
                if (!empty($itensSemHub)) {
                    $msg .= 'Sem saldo no HUB: ' . implode(' | ', $itensSemHub) . '. Solicite reposição ao operador de estoque.';
                }

                $this->addError('status', $msg . ' O status foi alterado para "Sem estoque".');
                return;
            }
        }

        $updateData = ['status' => $this->status];
        if ($this->status === 'Aprovado') {
            $updateData['workflow_status'] = 'aguardando_separacao';
        } elseif (in_array($this->status, ['Pendente', 'Sem estoque', 'Cancelado', 'Rejeitado'])) {
            $updateData['workflow_status'] = null;
        }

        $this->orcamento->update($updateData);
        $this->dispatch('notify', 'Status atualizado com sucesso!');
    }

    public function aprovarDesconto()
    {
        $this->orcamento->update(['desconto_aprovado' => $this->desconto_aprovado]);
        $this->dispatch('notify', 'Desconto aprovado com sucesso!');
    }

    public function render()
    {
        // Atualiza o relacionamento ao recarregar
        $this->orcamento->load(['cliente', 'itens', 'vidros', 'transporte']);
        return view('livewire.orcamento-show');
    }
}
