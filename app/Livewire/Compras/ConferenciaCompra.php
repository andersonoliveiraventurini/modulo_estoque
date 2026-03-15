<?php

namespace App\Livewire\Compras;

use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\ConferenciaItemFoto;
use App\Models\PedidoCompra;
use App\Models\Movimentacao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConferenciaCompra extends Component
{
    use WithFileUploads;

    public PedidoCompra $pedido;
    public array $inputs = [];
    public array $novasFotos = [];
    public array $legendas = [];
    public ?Conferencia $conferencia = null;

    protected function rules(): array
    {
        $rules = [];
        if ($this->conferencia) {
            foreach ($this->conferencia->itens as $it) {
                $rules["inputs.{$it->id}.qty"] = 'nullable|numeric|min:0';
                $rules["inputs.{$it->id}.motivo"] = 'nullable|string|max:500';
            }
            foreach (array_keys($this->novasFotos) as $itemId) {
                $rules["novasFotos.{$itemId}.*"] = 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240';
            }
        }
        return $rules;
    }

    public function mount(PedidoCompra $pedido): void
    {
        $this->pedido = $pedido;
        $this->carregarConferencia();
    }

    private function carregarConferencia(): void
    {
        $this->conferencia = Conferencia::with([
                'itens.produto',
                'itens.pedidoCompraItem',
                'itens.fotos.enviadoPor',
                'conferente',
            ])
            ->where('pedido_compra_id', $this->pedido->id)
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->latest()
            ->first();

        if ($this->conferencia) {
            foreach ($this->conferencia->itens as $it) {
                $this->inputs[$it->id] = [
                    'qty' => $it->qty_conferida > 0 ? (string) $it->qty_conferida : '',
                    'motivo' => $it->motivo_divergencia ?? '',
                ];
                $this->novasFotos[$it->id] ??= [];
                $this->legendas[$it->id] ??= '';
            }
        }
    }

    public function iniciarConferencia(): void
    {
        DB::transaction(function () {
            $conf = Conferencia::create([
                'pedido_compra_id' => $this->pedido->id,
                'status' => 'em_conferencia',
                'conferente_id' => Auth::id(),
                'started_at' => now(),
            ]);

            foreach ($this->pedido->itens as $item) {
                ConferenciaItem::create([
                    'conferencia_id' => $conf->id,
                    'pedido_compra_item_id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'qty_separada' => $item->quantidade,
                    'qty_conferida' => 0,
                    'status' => 'pendente',
                    'divergencia' => 0,
                ]);
            }

            $this->pedido->update(['status' => 'em_conferencia']);
        });

        $this->carregarConferencia();
        session()->flash('success', 'Conferência de recebimento iniciada!');
    }

    public function salvarItem(int $itemId): void
    {
        $this->validateOnly("inputs.{$itemId}.*");
        $item = ConferenciaItem::findOrFail($itemId);
        $qty = (float) ($this->inputs[$itemId]['qty'] ?? 0);
        $divergencia = $qty - (float) $item->qty_separada;
        $status = abs($divergencia) < 0.001 ? 'ok' : 'divergente';

        $item->update([
            'qty_conferida' => $qty,
            'status' => $status,
            'divergencia' => $divergencia,
            'motivo_divergencia' => $this->inputs[$itemId]['motivo'] ?? null,
            'conferido_por_id' => Auth::id(),
            'conferido_em' => now(),
        ]);

        $this->processarFotos($item);
        $this->carregarConferencia();
        session()->flash('success', 'Item conferido!');
    }

    public function concluir(): void
    {
        if (!$this->conferencia) return;

        DB::transaction(function () {
            $divergente = $this->conferencia->itens->firstWhere('status', 'divergente');
            
            $this->conferencia->update([
                'status' => 'concluida',
                'finished_at' => now(),
            ]);

            // Gerar movimentação de entrada (pendente de aprovação)
            $this->gerarMovimentacaoEntrada($this->conferencia);
            
            $this->pedido->update(['status' => ($divergente ? 'parcialmente_recebido' : 'recebido')]);
        });

        $this->conferencia = null;
        $this->carregarConferencia();
        session()->flash('success', 'Conferência concluída e entrada de estoque gerada!');
    }

    private function gerarMovimentacaoEntrada(Conferencia $conf)
    {
        $movimentacao = Movimentacao::create([
            'tipo' => 'entrada',
            'status' => 'pendente',
            'data_movimentacao' => now()->toDateString(),
            'pedido_compra_id' => $conf->pedido_compra_id,
            'observacao' => "Entrada automática via Conferência #{$conf->id}",
            'usuario_id' => Auth::id(),
        ]);

        foreach ($conf->itens as $item) {
            if ($item->qty_conferida <= 0) continue;
            $movimentacao->itens()->create([
                'produto_id' => $item->produto_id,
                'quantidade' => $item->qty_conferida,
                'valor_unitario' => $item->pedidoCompraItem->valor_unitario ?? 0,
                'valor_total' => $item->qty_conferida * ($item->pedidoCompraItem->valor_unitario ?? 0),
                'observacao' => $item->motivo_divergencia,
            ]);
        }
    }

    private function processarFotos(ConferenciaItem $item): void
    {
        $arquivos = $this->novasFotos[$item->id] ?? [];
        $legenda = trim($this->legendas[$item->id] ?? '');
        foreach ((array) $arquivos as $file) {
            if (!$file) continue;
            $path = $file->store("conferencias/{$this->conferencia->id}/itens/{$item->id}", 'public');
            ConferenciaItemFoto::create([
                'conferencia_item_id' => $item->id,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'legenda' => $legenda ?: null,
                'enviado_por_id' => Auth::id(),
            ]);
        }
        $this->novasFotos[$item->id] = [];
        $this->legendas[$item->id] = '';
    }

    public function render()
    {
        return view('livewire.compras.conferencia-compra');
    }
}
