<?php

namespace App\Livewire\Orcamentos;

use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Models\PickingItem;
use App\Services\EstoqueService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class SeparacaoPage extends Component
{
    public int $orcamentoId;
    public ?Orcamento $orcamento = null;
    public ?PickingBatch $batch = null;
    public ?Collection $concludedBatches = null;
    public array $inputs = [];

    // Campos de embalagem
    public $caixas = 0;
    public $sacos = 0;
    public $sacolas = 0;
    public $outros = '';

    public function mount(int $id)
    {
        $this->orcamentoId = $id;
        $this->carregar();
    }

    #[On('refresh')]
    public function carregar()
    {
        $this->orcamento = Orcamento::with(['cliente'])->findOrFail($this->orcamentoId);

        $this->batch = PickingBatch::with([
                'criadoPor',
                'items.produto',
                'items.separadoPor'
            ])
            ->where('orcamento_id', $this->orcamentoId)
            ->whereIn('status', ['aberto', 'em_separacao'])
            ->latest('id')
            ->first();

        if (!$this->batch) {
            $this->concludedBatches = PickingBatch::with([
                    'criadoPor',
                    'items.produto',
                    'items.separadoPor'
                ])
                ->where('orcamento_id', $this->orcamentoId)
                ->where('status', 'concluido')
                ->orderBy('finished_at', 'desc')
                ->get();
        }

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
            
            // Carrega dados de embalagem se já existirem
            $this->caixas = $this->batch->qtd_caixas ?? 0;
            $this->sacos = $this->batch->qtd_sacos ?? 0;
            $this->sacolas = $this->batch->qtd_sacolas ?? 0;
            $this->outros = $this->batch->outros_embalagem ?? '';
        }
    }

    public function iniciarSeparacao(EstoqueService $estoque)
    {
        $existingBatch = PickingBatch::where('orcamento_id', $this->orcamentoId)
            ->whereIn('status', ['aberto', 'em_separacao'])
            ->exists();

        if ($existingBatch) {
            session()->flash('error', 'Já existe um lote de separação em andamento para este orçamento.');
            $this->dispatch('refresh');
            return;
        }

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
        });

        $this->dispatch('refresh');
        session()->flash('success', 'Separação iniciada com sucesso.');
    }

    public function salvarItem(int $itemId)
    {
        if (!$this->batch) {
            session()->flash('error', 'Lote de separação não encontrado.');
            return;
        }

        $data = $this->inputs[$itemId] ?? null;
        if (!$data) {
            session()->flash('error', 'Dados do item não encontrados no formulário.');
            return;
        }

        $item = PickingItem::find($itemId);
        if (!$item) {
            session()->flash('error', 'Item de separação não encontrado no banco de dados.');
            return;
        }

        $validatedQty = max(0, (float) ($data['qty'] ?? 0));
        $validatedQty = min($validatedQty, (float) $item->qty_solicitada);

        DB::transaction(function () use ($item, $data, $validatedQty) {
            $item->qty_separada = $validatedQty;
            $item->separado_por_id = auth()->id();
            $item->separado_em = now();

            $motivo = trim((string) ($data['motivo'] ?? ''));
            $item->motivo_nao_separado = ($validatedQty <= 0 && !empty($motivo)) ? $motivo : null;

            $inconsistencia = (bool) ($data['inconsistencia'] ?? false);
            $item->inconsistencia_reportada = $inconsistencia;
            if ($inconsistencia) {
                $item->inconsistencia_por_id = auth()->id();
                $item->inconsistencia_obs = trim((string) ($data['obs'] ?? ''));
            } else {
                $item->inconsistencia_por_id = null;
                $item->inconsistencia_obs = null;
            }

            if ($item->qty_separada <= 0) {
                $item->status = 'pendente';
            } elseif ($item->qty_separada < $item->qty_solicitada) {
                $item->status = 'parcial';
            } else {
                $item->status = 'separado';
            }

            $item->save();
        });

        $this->dispatch('refresh');
        session()->flash('success', 'Item #' . $item->id . ' atualizado com sucesso!');
    }

    public function concluirLote()
    {
        if (!$this->batch) {
            $this->addError('batch', 'Nenhum lote de separação ativo para concluir.');
            return;
        }

        $this->batch->load('items');

        foreach ($this->batch->items as $item) {
            $qtySeparada = $this->inputs[$item->id]['qty'] ?? $item->qty_separada;
            $motivo = trim($this->inputs[$item->id]['motivo'] ?? $item->motivo_nao_separado ?? '');

            if ((float)$qtySeparada <= 0 && empty($motivo)) {
                $this->addError("inputs.{$item->id}.motivo", 'Informe o motivo para não separar este item.');
                $this->addError('batch', 'Existem itens pendentes sem justificativa.');
                return;
            }
        }

        DB::transaction(function () {
            foreach ($this->batch->items as $item) {
                $formMotivo = $this->inputs[$item->id]['motivo'] ?? null;
                if ((float)$item->qty_separada <= 0 && !empty($formMotivo) && $item->motivo_nao_separado !== $formMotivo) {
                    $item->update(['motivo_nao_separado' => $formMotivo]);
                }
            }

            // Atualiza o batch com os dados de embalagem
            $this->batch->update([
                'status' => 'concluido',
                'finished_at' => now(),
                'qtd_caixas' => $this->caixas ? (int) $this->caixas : null,
                'qtd_sacos' => $this->sacos ? (int) $this->sacos : null,
                'qtd_sacolas' => $this->sacolas ? (int) $this->sacolas : null,
                'outros_embalagem' => !empty($this->outros) ? trim($this->outros) : null,
            ]);

            $this->batch->orcamento()->update([
                'workflow_status' => 'aguardando_conferencia'
            ]);
        });

        session()->flash('success', 'Separação concluída! Redirecionando para a conferência...');
        return redirect()->route('orcamentos.conferencia.show', $this->orcamento->id);
    }

    public function render()
    {
        return view('livewire.orcamentos.separacao-page');
    }
}