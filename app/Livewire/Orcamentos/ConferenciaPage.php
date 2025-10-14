<?php

namespace App\Livewire\Orcamentos;

use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Services\EstoqueService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ConferenciaPage extends Component
{
    public Orcamento $orcamento;
    public ?Conferencia $conferencia = null;

    // inputs por item de conferência
    #[Validate(['array'])]
    public array $inputs = [];

    public function mount(int $id)
    {
        $this->orcamento = Orcamento::findOrFail($id);

        $this->conferencia = Conferencia::with(['itens.produto', 'batch'])
            ->where('orcamento_id', $id)
            ->whereIn('status', ['aberta','em_conferencia'])
            ->first();

        if ($this->conferencia) {
            foreach ($this->conferencia->itens as $it) {
                $this->inputs[$it->id] = [
                    'qty' => (float)$it->qty_conferida,
                    'motivo' => $it->motivo_divergencia,
                ];
            }
        }
    }

    public function iniciarConferencia()
    {
        $batch = PickingBatch::with('items')
            ->where('orcamento_id', $this->orcamento->id)
            ->where('status', 'concluido')
            ->latest('id')
            ->firstOrFail();

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

            $this->conferencia = $conf->fresh(['itens.produto', 'batch']);

            foreach ($this->conferencia->itens as $it) {
                $this->inputs[$it->id] = ['qty' => 0, 'motivo' => null];
            }
        });

        session()->flash('success', 'Conferência iniciada.');
    }

    public function salvarItem(int $itemId)
    {
        //$this->validate([
        //]);

        $item = ConferenciaItem::findOrFail($itemId);
        $data = $this->inputs[$itemId];

        DB::transaction(function () use ($item, $data) {
            $q = (float) $data['qty'];
            $div = $q - (float) $item->qty_separada;

            $item->qty_conferida = $q;
            $item->divergencia = $div;
            $item->status = abs($div) > 0 ? 'divergente' : 'ok';
            $item->motivo_divergencia = $data['motivo'] ?? null;
            $item->conferido_por_id = auth()->id();
            $item->conferido_em = now();
            $item->save();
        });

        $this->conferencia = $this->conferencia->fresh(['itens.produto']);
        session()->flash('success', 'Item conferido.');
    }

    public function concluir(EstoqueService $estoque)
    {
        $this->conferencia->load('itens', 'orcamento');
        $divergente = $this->conferencia->itens->firstWhere('status', 'divergente');

        DB::transaction(function () use ($divergente, $estoque) {
            $this->conferencia->update([
                'status' => 'concluida',
                'finished_at' => now(),
            ]);

            if ($divergente) {
                $this->orcamento->update(['workflow_status' => 'conferido']);
            } else {
                $estoque->baixarSaida($this->conferencia);
                $this->orcamento->update(['workflow_status' => 'finalizado']);
            }
        });

        session()->flash('success', 'Conferência concluída.');
        return redirect()->route('orcamentos.show', $this->orcamento->id);
    }

    public function render()
    {
        return view('livewire.orcamentos.conferencia-page');
    }
}