<?php

namespace App\Livewire\Orcamentos;

use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Services\EstoqueService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ConferenciaPage extends Component
{
    public int $orcamentoId;
    public ?Orcamento $orcamento = null;
    public ?Conferencia $conferencia = null;
    public ?Collection $concludedConferencias = null;

    #[Validate(['array'])]
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

        // Carrega a conferência ativa com as relações necessárias
        $this->conferencia = Conferencia::with([
                'conferente',
                'itens.produto',
                'itens.conferidoPor',
                'batch'
            ])
            ->where('orcamento_id', $this->orcamentoId)
            ->whereIn('status', ['aberta', 'em_conferencia'])
            ->latest('id')
            ->first();

        // Se não houver conferência ativa, carrega o histórico de concluídas
        if (!$this->conferencia) {
            $this->concludedConferencias = Conferencia::with([
                    'conferente',
                    'itens.produto',
                    'itens.conferidoPor',
                    'batch'
                ])
                ->where('orcamento_id', $this->orcamentoId)
                ->where('status', 'concluida')
                ->orderBy('finished_at', 'desc')
                ->get();
        }

        // Preenche os inputs do formulário se houver conferência ativa
        if ($this->conferencia) {
            $this->inputs = [];
            foreach ($this->conferencia->itens as $it) {
                $this->inputs[$it->id] = [
                    'qty' => (float) $it->qty_conferida,
                    'motivo' => $it->motivo_divergencia ?? '',
                ];
            }
        }
    }

    public function iniciarConferencia()
    {
        // Impede a criação de uma nova conferência se já existir uma ativa
        $existingConferencia = Conferencia::where('orcamento_id', $this->orcamentoId)
            ->whereIn('status', ['aberta', 'em_conferencia'])
            ->exists();

        if ($existingConferencia) {
            session()->flash('error', 'Já existe uma conferência em andamento para este orçamento.');
            $this->dispatch('refresh');
            return;
        }

        $batch = PickingBatch::with('items')
            ->where('orcamento_id', $this->orcamento->id)
            ->where('status', 'concluido')
            ->latest('id')
            ->first();

        if (!$batch) {
            session()->flash('error', 'Não há lote de separação concluído para iniciar a conferência.');
            return;
        }

        DB::transaction(function () use ($batch) {
            $conf = Conferencia::create([
                'orcamento_id' => $this->orcamento->id,
                'picking_batch_id' => $batch->id,
                'status' => 'em_conferencia',
                'conferente_id' => auth()->id(),
                'started_at' => now(),
            ]);

            foreach ($batch->items as $pi) {
                ConferenciaItem::create([
                    'conferencia_id' => $conf->id,
                    'picking_item_id' => $pi->id,
                    'produto_id' => $pi->produto_id,
                    'qty_separada' => $pi->qty_separada,
                    'qty_conferida' => 0,
                    'status' => 'ok',
                    'divergencia' => 0,
                ]);
            }

            $this->orcamento->update(['workflow_status' => 'em_conferencia']);
        });

        $this->dispatch('refresh');
        session()->flash('success', 'Conferência iniciada com sucesso.');
    }

    public function salvarItem(int $itemId)
    {
        if (!$this->conferencia) {
            session()->flash('error', 'Conferência não encontrada.');
            return;
        }

        $data = $this->inputs[$itemId] ?? null;
        if (!$data) {
            session()->flash('error', 'Dados do item não encontrados no formulário.');
            return;
        }

        $item = ConferenciaItem::find($itemId);
        if (!$item) {
            session()->flash('error', 'Item de conferência não encontrado no banco de dados.');
            return;
        }

        DB::transaction(function () use ($item, $data) {
            $q = max(0, (float) ($data['qty'] ?? 0));
            $div = $q - (float) $item->qty_separada;

            $item->qty_conferida = $q;
            $item->divergencia = $div;
            $item->status = abs($div) > 0 ? 'divergente' : 'ok';
            $item->motivo_divergencia = trim((string) ($data['motivo'] ?? ''));
            $item->conferido_por_id = auth()->id();
            $item->conferido_em = now();
            $item->save();
        });

        $this->dispatch('refresh');
        session()->flash('success', 'Item #' . $item->id . ' conferido com sucesso!');
    }

    public function concluir(EstoqueService $estoque)
    {
        if (!$this->conferencia) {
            session()->flash('error', 'Nenhuma conferência ativa para concluir.');
            return;
        }

        $this->conferencia->load('itens', 'orcamento');
        $divergente = $this->conferencia->itens->firstWhere('status', 'divergente');

        DB::transaction(function () use ($divergente, $estoque) {
            $this->conferencia->update([
                'status' => 'concluida',
                'finished_at' => now(),
            ]);

            if ($divergente) {
                $this->orcamento->update(['workflow_status' => 'conferido_com_divergencia']);
            } else {
                $estoque->baixarSaida($this->conferencia);
                $this->orcamento->update(['workflow_status' => 'finalizado']);
            }
        });

        session()->flash('success', 'Conferência concluída com sucesso!');
        return redirect()->route('orcamentos.show', $this->orcamento->id);
    }

    public function render()
    {
        return view('livewire.orcamentos.conferencia-page');
    }
}