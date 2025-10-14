<?php

namespace App\Livewire\Orcamentos;

use App\Models\EstoqueReserva;
use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Models\PickingItem;
use App\Services\EstoqueService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class SeparacaoPage extends Component
{
    public int $orcamentoId;
    public ?Orcamento $orcamento = null;
    public ?PickingBatch $batch = null;

    public array $inputs = [];

    public function mount(int $id)
    {
        $this->orcamentoId = $id;
        $this->carregar();
    }

    #[On('refresh')]
    public function carregar()
    {
        $this->orcamento = Orcamento::with(['cliente'])->findOrFail($this->orcamentoId);

        $this->batch = PickingBatch::with(['items.produto'])
            ->where('orcamento_id', $this->orcamentoId)
            ->whereIn('status', ['aberto', 'em_separacao'])
            ->latest('id')
            ->first();

        if ($this->batch) {
            $this->inputs = [];
            foreach ($this->batch->items as $it) {
                $this->inputs[$it->id] = [
                    'qty' => (float) $it->qty_separada,
                    'motivo' => $it->motivo_nao_separado ?? '',
                    'inconsistencia' => (bool) $it->inconsistencia_reportada,
                    'obs' => $it->inconsistencia_obs ?? '',
                ];
            }
        }
    }

    public function iniciarSeparacao(EstoqueService $estoque)
    {
        $orcamento = Orcamento::with(['itens.produto'])->findOrFail($this->orcamentoId);

       if ($orcamento->itens->count() === 0) {
            session()->flash('error', 'Este orçamento não possui itens para separar.');
            return;
        }

        DB::transaction(function () use ($orcamento, $estoque) {
            $batch = PickingBatch::create([
                'orcamento_id' => $orcamento->id,
                'status' => 'em_separacao',
                'started_at' => now(),
                'criado_por_id' => auth()->id(),
            ]);

            foreach ($orcamento->itens as $oi) {
                PickingItem::create([
                    'picking_batch_id' => $batch->id,
                    'orcamento_item_id' => $oi->id,
                    'produto_id' => $oi->produto_id,
                    'qty_solicitada' => $oi->quantidade,
                    'qty_separada' => 0,
                    'status' => 'pendente',
                ]);
            }

            $estoque->reservarParaOrcamento($orcamento);
            $orcamento->update(['workflow_status' => 'em_separacao']);
            $orcamento->save();
        });

        $this->dispatch('refresh');
        session()->flash('success', 'Separação iniciada.');
    }

    public function salvarItem(int $itemId)
    {
        // 1. Valida se o lote e o item existem para evitar erros.
        if (!$this->batch) {
            session()->flash('error', 'Lote de separação não encontrado.');
            return;
        }

        // 2. Pega os dados do formulário para este item específico.
        //    A propriedade $inputs contém os valores dos campos com wire:model.defer.
        $data = $this->inputs[$itemId] ?? null;
        if (!$data) {
            session()->flash('error', 'Dados do item não encontrados no formulário.');
            return;
        }

        // 3. Carrega o item do banco de dados para garantir que estamos atualizando o registro correto.
        $item = PickingItem::find($itemId);
        if (!$item) {
            session()->flash('error', 'Item de separação não encontrado no banco de dados.');
            return;
        }

        // 4. Valida e sanitiza a quantidade para evitar valores inválidos.
        $validatedQty = max(0, (float) ($data['qty'] ?? 0));
        $validatedQty = min($validatedQty, (float) $item->qty_solicitada); // Garante que não exceda o solicitado.

        // 5. Executa a atualização dentro de uma transação para segurança.
        DB::transaction(function () use ($item, $data, $validatedQty) {
            $item->qty_separada = $validatedQty;
            $item->separado_por_id = auth()->id();
            $item->separado_em = now();

            // Salva o motivo apenas se a quantidade for zero.
            $motivo = trim((string) ($data['motivo'] ?? ''));
            if ($validatedQty <= 0 && !empty($motivo)) {
                $item->motivo_nao_separado = $motivo;
            } elseif ($validatedQty > 0) {
                // Limpa o motivo se o item foi separado.
                $item->motivo_nao_separado = null;
            }

            // Salva os dados de inconsistência.
            $inconsistencia = (bool) ($data['inconsistencia'] ?? false);
            $item->inconsistencia_reportada = $inconsistencia;
            if ($inconsistencia) {
                $item->inconsistencia_por_id = auth()->id();
                $item->inconsistencia_obs = trim((string) ($data['obs'] ?? ''));
            } else {
                $item->inconsistencia_por_id = null;
                $item->inconsistencia_obs = null;
            }

            // Atualiza o status do item com base na quantidade separada.
            if ($item->qty_separada <= 0) {
                $item->status = 'pendente';
            } elseif ($item->qty_separada < $item->qty_solicitada) {
                $item->status = 'parcial';
            } else {
                $item->status = 'separado';
            }

            // Salva todas as alterações no banco de dados.
            $item->save();
        });

        // 6. Dispara um evento para recarregar os dados na tela e dá feedback ao usuário.
        $this->dispatch('refresh');
        session()->flash('success', 'Item #' . $item->id . ' atualizado com sucesso!');
    }

    public function concluirLote()
    {
        if (!$this->batch) {
            $this->addError('batch', 'Nenhum lote de separação ativo para concluir.');
            return;
        }

        // Recarrega os itens para garantir que temos os dados mais recentes
        $this->batch->load('items');
        $itens = $this->batch->items;

        // Validação: Verifica se algum item não separado está sem motivo
        foreach ($itens as $i) {
            // Considera tanto o que já está salvo no banco quanto o que está no formulário
            $motivoNoForm = $this->inputs[$i->id]['motivo'] ?? '';
            $motivoNoBanco = $i->motivo_nao_separado ?? '';

            if ($i->qty_separada <= 0 && empty($motivoNoForm) && empty($motivoNoBanco)) {
                // Adiciona o erro e destaca o campo do item problemático
                $this->addError("inputs.{$i->id}.motivo", 'Informe o motivo para não separar este item.');
                // Adiciona um erro geral para ser exibido no topo
                $this->addError('batch', 'Existem itens pendentes sem justificativa.');
                return;
            }
        }

        // Se a validação passou, executa a conclusão
        DB::transaction(function () use ($itens) {
            // Garante que os últimos motivos digitados sejam salvos
            foreach ($itens as $i) {
                $formMotivo = $this->inputs[$i->id]['motivo'] ?? null;
                if ($i->qty_separada <= 0 && !empty($formMotivo) && $i->motivo_nao_separado !== $formMotivo) {
                    $i->update(['motivo_nao_separado' => $formMotivo]);
                }
            }

            $this->batch->update([
                'status' => 'concluido',
                'finished_at' => now(),
            ]);

            $this->batch->orcamento->update([
                'workflow_status' => 'aguardando_conferencia'
            ]);
        });

        session()->flash('success', 'Separação concluída! Redirecionando para a conferência...');

        // Redireciona para a próxima etapa do fluxo
        return redirect()->route('orcamentos.conferencia.show', $this->orcamento->id);
    }

    public function render()
    {
        return view('livewire.orcamentos.separacao-page');
    }
}
