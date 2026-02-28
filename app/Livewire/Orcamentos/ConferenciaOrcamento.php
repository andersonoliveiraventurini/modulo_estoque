<?php

namespace App\Livewire\Orcamentos;

use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\ConferenciaItemFoto;
use App\Models\Orcamento;
use App\Models\PickingBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConferenciaOrcamento extends Component
{
    use WithFileUploads;

    // ─── Props ─────────────────────────────────────────────────────────────────

    public Orcamento $orcamento;

    // ─── Estado dos itens ──────────────────────────────────────────────────────

    /** [ item_id => ['qty' => '', 'motivo' => ''] ] */
    public array $inputs = [];

    // ─── Upload de fotos ───────────────────────────────────────────────────────

    /** [ item_id => [TemporaryUploadedFile, ...] ] */
    public array $novasFotos = [];

    /** [ item_id => string ] */
    public array $legendas = [];

    // ─── Embalagem ─────────────────────────────────────────────────────────────

    public ?int    $caixas   = null;
    public ?int    $sacos    = null;
    public ?int    $sacolas  = null;
    public ?string $outros   = null;

    // ─── Estado carregado ──────────────────────────────────────────────────────

    public ?Conferencia $conferencia      = null;
    public $concludedConferencias         = null;

    // ─── Validação ─────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [];

        if ($this->conferencia) {
            foreach ($this->conferencia->itens as $it) {
                $rules["inputs.{$it->id}.qty"]    = 'nullable|numeric|min:0';
                $rules["inputs.{$it->id}.motivo"] = 'nullable|string|max:500';
            }
            foreach (array_keys($this->novasFotos) as $itemId) {
                $rules["novasFotos.{$itemId}.*"] = 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240';
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'caixas'  => 'caixas',
            'sacos'   => 'sacos',
            'sacolas' => 'sacolas',
            'outros'  => 'outros',
        ];
    }

    // ─── Mount ─────────────────────────────────────────────────────────────────

    public function mount(Orcamento $orcamento): void
    {
        $this->orcamento = $orcamento instanceof Orcamento
            ? $orcamento
            : Orcamento::findOrFail($orcamento);

        $this->carregarConferencia();
    }

    // ─── Carregamento ──────────────────────────────────────────────────────────

    private function carregarConferencia(): void
    {
        $this->conferencia = Conferencia::with([
                'itens.produto',
                'itens.conferidoPor',
                'itens.fotos.enviadoPor',
                'conferente',
            ])
            ->where('orcamento_id', $this->orcamento->id)
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->latest()
            ->first();

        $this->concludedConferencias = Conferencia::with([
                'itens.produto',
                'itens.fotos',
                'conferente',
            ])
            ->where('orcamento_id', $this->orcamento->id)
            ->where('status', 'concluida')
            ->orderByDesc('finished_at')
            ->get();

        if ($this->conferencia) {
            foreach ($this->conferencia->itens as $it) {
                $this->inputs[$it->id] = [
                    'qty'    => $it->qty_conferida > 0 ? (string) $it->qty_conferida : '',
                    'motivo' => $it->motivo_divergencia ?? '',
                ];
                $this->novasFotos[$it->id] ??= [];
                $this->legendas[$it->id]   ??= '';
            }

            $this->caixas  = $this->conferencia->qtd_caixas;
            $this->sacos   = $this->conferencia->qtd_sacos;
            $this->sacolas = $this->conferencia->qtd_sacolas;
            $this->outros  = $this->conferencia->outros_embalagem;
        }
    }

    // ─── Permissão ─────────────────────────────────────────────────────────────

    private function podeEditar(): bool
    {
        $aprovados = ['aprovado', 'approved'];

        return $this->orcamento->validade >= now()
            || in_array(strtolower($this->orcamento->status ?? ''), $aprovados);
    }

    // ─── Validação de embalagem ────────────────────────────────────────────────

    /**
     * Retorna true se ao menos um campo de embalagem foi preenchido.
     */
    private function embalagemPreenchida(): bool
    {
        return ($this->caixas  > 0)
            || ($this->sacos   > 0)
            || ($this->sacolas > 0)
            || (!empty(trim($this->outros ?? '')));
    }

    // ─── Ações ─────────────────────────────────────────────────────────────────

    public function iniciarConferencia(): void
    {
        $batch = PickingBatch::where('orcamento_id', $this->orcamento->id)
            ->where('status', 'concluido')
            ->latest()
            ->first();

        if (!$batch) {
            session()->flash('error', 'Nenhum lote de separação concluído encontrado.');
            return;
        }

        DB::transaction(function () use ($batch) {
            $conf = Conferencia::create([
                'orcamento_id'     => $this->orcamento->id,
                'picking_batch_id' => $batch->id,
                'status'           => 'em_conferencia',
                'conferente_id'    => Auth::id(),
                'started_at'       => now(),
            ]);

            foreach ($batch->items as $pi) {
                ConferenciaItem::create([
                    'conferencia_id'     => $conf->id,
                    'picking_item_id'    => $pi->id,
                    'produto_id'         => $pi->produto_id,         // null para encomendas
                    'consulta_preco_id'  => $pi->consulta_preco_id,  // ✅
                    'is_encomenda'       => $pi->is_encomenda,        // ✅
                    'descricao_encomenda'=> $pi->descricao_encomenda, // ✅
                    'qty_separada'       => $pi->qty_separada,
                    'qty_conferida'      => 0,
                    'status'             => 'pendente',
                    'divergencia'        => 0,
                ]);
            }
        });

        $this->carregarConferencia();
        session()->flash('success', 'Conferência iniciada com sucesso!');
    }

    public function salvarItem(int $itemId): void
    {
        $this->validateOnly("inputs.{$itemId}.*");

        $item = ConferenciaItem::findOrFail($itemId);
        $qty  = (float) ($this->inputs[$itemId]['qty'] ?? 0);

        $divergencia = $qty - (float) $item->qty_separada;
        $status      = abs($divergencia) < 0.001 ? 'ok' : 'divergente';

        $item->update([
            'qty_conferida'      => $qty,
            'status'             => $status,
            'divergencia'        => $divergencia,
            'motivo_divergencia' => $this->inputs[$itemId]['motivo'] ?? null,
            'conferido_por_id'   => Auth::id(),
            'conferido_em'       => now(),
        ]);

        $this->processarFotos($item);
        $this->carregarConferencia();

        session()->flash('success', 'Item salvo com sucesso!');
    }

    public function removerFoto(int $fotoId): void
    {
        $foto = ConferenciaItemFoto::findOrFail($fotoId);

        abort_unless(
            $foto->conferenciaItem->conferencia_id === $this->conferencia?->id,
            403
        );

        Storage::disk($foto->disk)->delete($foto->path);
        $foto->delete();

        $this->carregarConferencia();
    }

    public function concluir(): void
    {
        if (!$this->conferencia) return;

        // Embalagem obrigatória: ao menos um campo preenchido
        if (!$this->embalagemPreenchida()) {
            session()->flash('error', 'Informe ao menos um tipo de embalagem (caixas, sacos, sacolas ou outros) antes de concluir.');
            return;
        }

        DB::transaction(function () {
            $this->conferencia->update([
                'status'           => 'concluida',
                'finished_at'      => now(),
                'qtd_caixas'       => $this->caixas  ?: null,
                'qtd_sacos'        => $this->sacos   ?: null,
                'qtd_sacolas'      => $this->sacolas ?: null,
                'outros_embalagem' => trim($this->outros ?? '') ?: null,
            ]);

            $this->orcamento->update(['workflow_status' => 'conferido']);
        });

        $this->conferencia = null;
        $this->carregarConferencia();

        session()->flash('success', 'Conferência concluída com sucesso!');
    }

    // ─── Upload ────────────────────────────────────────────────────────────────

    private function processarFotos(ConferenciaItem $item): void
    {
        $arquivos = $this->novasFotos[$item->id] ?? [];
        $legenda  = trim($this->legendas[$item->id] ?? '');

        foreach ((array) $arquivos as $file) {
            if (!$file) continue;

            $path = $file->store(
                "conferencias/{$this->conferencia->id}/itens/{$item->id}",
                'public'
            );

            ConferenciaItemFoto::create([
                'conferencia_item_id' => $item->id,
                'path'                => $path,
                'disk'                => 'public',
                'mime_type'           => $file->getMimeType(),
                'size'                => $file->getSize(),
                'legenda'             => $legenda ?: null,
                'enviado_por_id'      => Auth::id(),
            ]);
        }

        $this->novasFotos[$item->id] = [];
        $this->legendas[$item->id]   = '';
    }

    // ─── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.conferencia-orcamento', [
            'orcamento'             => $this->orcamento,
            'conferencia'           => $this->conferencia,
            'concludedConferencias' => $this->concludedConferencias,
            'podeEditar'            => $this->podeEditar(),
            'embalagemOk'           => $this->embalagemPreenchida(),
        ]);
    }
}
