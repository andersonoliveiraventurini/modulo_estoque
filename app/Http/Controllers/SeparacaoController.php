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

    public function concluir(PickingBatch $batch)
    {
        $itens = $batch->items;

        // todos com status coerente e, se zero, motivo presente
        foreach ($itens as $i) {
            if ($i->qty_separada <= 0 && empty($i->motivo_nao_separado)) {
                return back()->with('error', 'Há item não separado sem motivo informado.');
            }
        }

        DB::transaction(function () use ($batch) {
            $batch->update([
                'status' => 'concluido',
                'finished_at' => now(),
            ]);

            $batch->orcamento->update([
                'workflow_status' => 'aguardando_conferencia'
            ]);
        });

        return back()->with('success', 'Separação concluída. Aguardando conferência.');
    }
}