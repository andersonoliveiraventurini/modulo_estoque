<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\PickingBatch;
use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\PedidoCompra;
use App\Models\Movimentacao;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ConferenciaPdfService;  
use Illuminate\Support\Facades\Storage;

class ConferenciaController extends Controller
{
    public function index()
    {
        return view('paginas.conferencia.index');
    }
   public function downloadPdf(Orcamento $orcamento)
{
    $service = new \App\Services\ConferenciaPdfService();
    
    $path = $service->gerarRelatorioPdf($orcamento);

    if (!$path) {
        return back()->with('error', 'Não foi possível gerar o PDF. Verifique se há conferências registradas para este orçamento.');
    }

    $nomeArquivo = "conferencia_orcamento_{$orcamento->id}.pdf";

    return \Illuminate\Support\Facades\Storage::disk('public')->download($path, $nomeArquivo);
}
    public function show(int $orcamentoId)
    {
        $orcamento = Orcamento::findOrFail($orcamentoId);
        $conf = Conferencia::with(['itens.produto', 'batch'])
            ->where('orcamento_id', $orcamentoId)
            ->whereIn('status', ['aberta','em_conferencia'])
            ->first();

        return view('paginas.conferencia.create', [
            'orcamento' => $orcamento,
            'conferencia' => $conf,
        ]);
    }

    public function showCompra(int $pedidoId)
    {
        $pedido = PedidoCompra::findOrFail($pedidoId);
        $conf = Conferencia::with(['itens.produto'])
            ->where('pedido_compra_id', $pedidoId)
            ->whereIn('status', ['aberta', 'em_conferencia'])
            ->first();

        return view('paginas.conferencia.create_compra', [
            'pedido' => $pedido,
            'conferencia' => $conf,
        ]);
    }

    public function iniciar(int $orcamentoId)
    {
        $orcamento = Orcamento::findOrFail($orcamentoId);
        $batch = PickingBatch::with('items')
            ->where('orcamento_id', $orcamentoId)
            ->where('status', 'concluido')
            ->latest('id')
            ->firstOrFail();

        DB::transaction(function () use ($orcamento, $batch) {
            $conf = Conferencia::create([
                'orcamento_id' => $orcamento->id,
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

            $orcamento->update(['workflow_status' => 'em_conferencia']);
        });

        return back()->with('success', 'Conferência iniciada.');
    }

    public function iniciarCompra(int $pedidoId)
    {
        $pedido = PedidoCompra::with('itens')->findOrFail($pedidoId);
        
        DB::transaction(function () use ($pedido) {
            $conf = Conferencia::create([
                'pedido_compra_id' => $pedido->id,
                'status' => 'em_conferencia',
                'conferente_id' => auth()->id(),
                'started_at' => now(),
            ]);

            foreach ($pedido->itens as $item) {
                ConferenciaItem::create([
                    'conferencia_id' => $conf->id,
                    'pedido_compra_item_id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'qty_separada' => $item->quantidade, // Qtd esperada do pedido
                    'qty_conferida' => 0,
                    'status' => 'ok',
                    'divergencia' => 0,
                ]);
            }

            $pedido->update(['status' => 'em_conferencia']);
        });

        return back()->with('success', 'Conferência de recebimento iniciada.');
    }

    public function conferirItem(Conferencia $conf, ConferenciaItem $item, Request $request)
    {
        $data = $request->validate([
        ]);

        DB::transaction(function () use ($item, $data) {
            $q = (float) $data['qty_conferida'];
            $div = $q - (float) $item->qty_separada;

            $item->qty_conferida = $q;
            $item->divergencia = $div;
            $item->status = abs($div) > 0 ? 'divergente' : 'ok';
            $item->motivo_divergencia = $data['motivo_divergencia'] ?? null;
            $item->conferido_por_id = auth()->id();
            $item->conferido_em = now();
            $item->save();
        });

        return response()->json(['ok' => true]);
    }

    public function concluir(Conferencia $conf, EstoqueService $estoque)
    {
        $conf->load('itens', 'orcamento');
        $divergente = $conf->itens->firstWhere('status', 'divergente');

        DB::transaction(function () use ($conf, $divergente, $estoque) {
            $conf->update([
                'status' => 'concluida',
                'finished_at' => now(),
            ]);

            if ($conf->pedido_compra_id) {
                // FLUXO DE COMPRA (Entrada)
                $this->gerarMovimentacaoEntrada($conf);
                $conf->pedidoCompra->update(['status' => ($divergente ? 'parcialmente_recebido' : 'recebido')]);
            } else {
                // FLUXO DE ORÇAMENTO (Saída)
                if ($divergente) {
                    $conf->orcamento->update(['workflow_status' => 'conferido']);
                } else {
                    $estoque->baixarSaida($conf);
                    $conf->orcamento->update(['workflow_status' => 'finalizado']);
                }
            }
        });

        return back()->with('success', 'Conferência concluída e processada.');
    }

    private function gerarMovimentacaoEntrada(Conferencia $conf)
    {
        $movimentacao = Movimentacao::create([
            'tipo' => 'entrada',
            'status' => 'pendente', // Segue o fluxo de aprovação
            'data_movimentacao' => now()->toDateString(),
            'pedido_compra_id' => $conf->pedido_compra_id,
            'observacao' => "Entrada gerada via Conferência #{$conf->id}",
            'usuario_id' => auth()->id(),
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

        \Illuminate\Support\Facades\Log::info("Movimentação de entrada gerada a partir da conferência #{$conf->id}", [
            'movimentacao_id' => $movimentacao->id
        ]);
    }
}