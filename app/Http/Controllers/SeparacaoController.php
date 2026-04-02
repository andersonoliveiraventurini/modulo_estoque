<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Models\PickingItem;
use App\Models\EstoqueReserva;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeparacaoController extends Controller
{
    public function show(int $orcamentoId)
    {
        $orcamento = Orcamento::with(['cliente'])->findOrFail($orcamentoId);
        $batch = PickingBatch::with(['items.produto'])
            ->where('orcamento_id', $orcamentoId)
            ->whereIn('status', ['aberto','em_separacao'])
            ->first();

        return view('paginas.separacao.create', [
            'orcamento' => $orcamento,
            'batch' => $batch,
        ]);
    }

    public function iniciar(int $orcamentoId, Request $request, EstoqueService $estoque)
    {
        $orcamento = Orcamento::with(['itens.produto'])->findOrFail($orcamentoId);

        DB::transaction(function () use ($orcamento, $estoque) {
            // Garante que apenas um processo mexa neste orçamento por vez
            $orcamento = Orcamento::where('id', $orcamento->id)->lockForUpdate()->first();

            // Evita criar múltiplos lotes se dois cliques ocorrerem quase ao mesmo tempo
            $existe = PickingBatch::where('orcamento_id', $orcamento->id)
                ->whereIn('status', ['aberto', 'em_separacao'])
                ->exists();

            if ($existe) return;

            $batch = PickingBatch::create([
                'orcamento_id' => $orcamento->id,
                'status' => 'em_separacao',
                'started_at' => now(),
                'criado_por_id' => auth()->id(),
            ]);

            foreach ($orcamento->itens as $oi) {
                $restante = $oi->quantidade_restante;

                if ($restante > 0) {
                    PickingItem::create([
                        'picking_batch_id' => $batch->id,
                        'orcamento_item_id' => $oi->id,
                        'produto_id' => $oi->produto_id,
                        'qty_solicitada' => $restante,
                        'qty_separada' => 0,
                        'status' => 'pendente',
                    ]);
                }
            }

            // cria reservas
            $estoque->reservarParaOrcamento($orcamento);

            $orcamento->update(['workflow_status' => 'em_separacao']);
        });

        return back()->with('success', 'Separação iniciada.');
    }

    public function separarItem(PickingBatch $batch, PickingItem $item, Request $request)
    {
        $data = $request->validate([
            'inconsistencia' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($item, $data) {
            $nova = (float) $data['qty'];
            $max = (float) $item->qty_solicitada;

            // não exceder
            $nova = min($nova, $max);

            $item->qty_separada = $nova;
            $item->separado_por_id = auth()->id();
            $item->separado_em = now();

            if ($nova <= 0 && !empty($data['motivo_nao_separado'])) {
                $item->motivo_nao_separado = $data['motivo_nao_separado'];
            }

            if (!empty($data['inconsistencia'])) {
                $item->inconsistencia_reportada = true;
                $item->inconsistencia_por_id = auth()->id();
                $item->inconsistencia_obs = $data['inconsistencia_obs'] ?? null;
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

        return response()->json(['ok' => true]);
    }

    public function concluir(PickingBatch $batch, Request $request)
    {
        $data = $request->validate([
            'qtd_caixas' => 'nullable|integer|min:0',
            'qtd_sacos' => 'nullable|integer|min:0',
            'qtd_sacolas' => 'nullable|integer|min:0',
            'outros_embalagem' => 'nullable|string|max:255',
        ]);

        $itens = $batch->items;

        // todos com status coerente e, se zero, motivo presente
        foreach ($itens as $i) {
            if ($i->qty_separada <= 0 && empty($i->motivo_nao_separado)) {
                return back()->with('error', 'Há item não separado sem motivo informado.');
            }
        }

        DB::transaction(function () use ($batch, $data) {
            $batch->update([
                'status' => 'concluido',
                'finished_at' => now(),
                'qtd_caixas' => $data['qtd_caixas'] ?? 0,
                'qtd_sacos' => $data['qtd_sacos'] ?? 0,
                'qtd_sacolas' => $data['qtd_sacolas'] ?? 0,
                'outros_embalagem' => $data['outros_embalagem'],
            ]);

            // Removido o update automático do workflow_status para 'aguardando_conferencia'
            // O orçamento permanece em 'em_separacao' até que o usuário clique em 'Finalizar Separação'
        });

        // Log de auditoria
        \App\Models\AcaoAtualizar::create([
            'descricao' => "Lote de separação #{$batch->id} concluído para o orçamento #{$batch->orcamento_id} (via Controller)",
            'user_id' => auth()->id(),
        ]);

        \Illuminate\Support\Facades\Log::info("Lote {$batch->id} de separacao finalizado. Volumes registrados.");

        return back()->with('success', 'Separação concluída. Aguardando conferência.');
    }
}