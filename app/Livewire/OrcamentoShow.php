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

            foreach ($itens as $item) {
                $produto = $item->produto;
                if (!$produto) continue;

                $reservadoOutros = \App\Models\EstoqueReserva::where('produto_id', $produto->id)
                    ->where('status', 'ativa')
                    ->where('orcamento_id', '!=', $this->orcamento->id)
                    ->sum('quantidade');

                $disponivel = max(0, ($produto->estoque_atual ?? 0) - $reservadoOutros);

                if ($disponivel < $item->quantidade) {
                    $itensSemEstoque[] = "{$produto->descricao}: "
                        . "disponível {$disponivel}, necessário {$item->quantidade}";
                }
            }

            if (!empty($itensSemEstoque)) {
                $this->addError(
                    'status',
                    'Estoque insuficiente: ' . implode(' | ', $itensSemEstoque)
                );
                return;
            }
        }

        $this->orcamento->update(['status' => $this->status]);
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
