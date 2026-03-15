<?php

namespace App\Http\Controllers;

use App\Models\PickingBatch;
use App\Models\Produto;
use App\Models\PedidoCompra;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Lista produtos com estoque abaixo do mínimo.
     */
    public function estoqueCritico()
    {
        $produtos = Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')
            ->with(['cor', 'fornecedor'])
            ->paginate(20);

        return view('paginas.relatorios.estoque_critico', compact('produtos'));
    }

    /**
     * Histórico de compras por período.
     */
    public function historicoCompras(Request $request)
    {
        $query = PedidoCompra::with(['fornecedor', 'usuario'])->latest();

        if ($request->filled('inicio')) {
            $query->where('data_pedido', '>=', $request->inicio);
        }

        if ($request->filled('fim')) {
            $query->where('data_pedido', '<=', $request->fim);
        }

        $pedidos = $query->paginate(20);

        return view('paginas.relatorios.historico_compras', compact('pedidos'));
    }

    /**
     * Fornecedores mais utilizados.
     */
    public function fornecedoresFrequentes()
    {
        $fornecedores = Fornecedor::withCount('pedidosCompra')
            ->orderBy('pedidos_compra_count', 'desc')
            ->paginate(20);

        return view('paginas.relatorios.fornecedores_frequentes', compact('fornecedores'));
    }

    /**
     * Comparativo de preços de produtos entre fornecedores.
     */
    public function comparativoPrecos(Request $request)
    {
        $produtos = Produto::select('id', 'nome', 'sku')->get();
        $ranking = [];

        if ($request->filled('produto_id')) {
            $ranking = DB::table('pedido_compra_itens')
                ->join('pedido_compras', 'pedido_compras.id', '=', 'pedido_compra_itens.pedido_compra_id')
                ->join('fornecedores', 'fornecedores.id', '=', 'pedido_compras.fornecedor_id')
                ->where('pedido_compra_itens.produto_id', $request->produto_id)
                ->select(
                    'fornecedores.nome_fantasia',
                    DB::raw('MIN(pedido_compra_itens.valor_unitario) as preco_min'),
                    DB::raw('MAX(pedido_compra_itens.valor_unitario) as preco_max'),
                    DB::raw('AVG(pedido_compra_itens.valor_unitario) as preco_medio'),
                    DB::raw('COUNT(*) as total_compras'),
                    DB::raw('(SELECT valor_unitario FROM pedido_compra_itens pi 
                              JOIN pedido_compras pc ON pc.id = pi.pedido_compra_id 
                              WHERE pi.produto_id = pedido_compra_itens.produto_id 
                              AND pc.fornecedor_id = fornecedores.id 
                              ORDER BY pc.data_pedido DESC LIMIT 1) as ultimo_preco')
                )
                ->groupBy('fornecedores.id', 'fornecedores.nome_fantasia', 'pedido_compra_itens.produto_id')
                ->orderBy('preco_medio', 'asc')
                ->get();
        }

        return view('paginas.relatorios.comparativo_precos', compact('produtos', 'ranking'));
    }

    // ── Logística / Separação ────────────────────────────────────────────────

    /**
     * Relatório de Separações concluídas agrupadas por Roteiro.
     */
    public function separacaoPorRoteiro(Request $request)
    {
        $query = $this->buildSeparacaoQuery($request);

        $batches = $query->with([
                'orcamento.cliente.enderecos',
                'orcamento.vendedor',
                'criadoPor',
            ])
            ->paginate(25);

        // Agrupa por roteiro para sumário
        $roteiros = (clone $query->getQuery())
            ->select('enderecos.roteiro', DB::raw('COUNT(picking_batches.id) as total_lotes'))
            ->join('orcamentos', 'orcamentos.id', '=', 'picking_batches.orcamento_id')
            ->join('enderecos', 'enderecos.cliente_id', '=', 'orcamentos.cliente_id')
            ->groupBy('enderecos.roteiro')
            ->orderBy('enderecos.roteiro')
            ->get();

        return view('paginas.relatorios.separacao_por_roteiro', compact('batches', 'roteiros'));
    }

    /**
     * Exporta Separações concluídas em CSV, agrupadas por Roteiro.
     */
    public function exportarSeparacaoPorRoteiro(Request $request): StreamedResponse
    {
        $batches = $this->buildSeparacaoQuery($request)
            ->with(['orcamento.cliente.enderecos', 'orcamento.vendedor', 'criadoPor'])
            ->get();

        $filename = 'separacao_roteiro_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($batches) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeçalho
            fputcsv($handle, [
                'Roteiro',
                'Lote #',
                'Orçamento #',
                'Cliente',
                'Endereço',
                'Vendedor',
                'Separador',
                'Concluído em',
                'Caixas',
                'Sacos',
                'Sacolas',
                'Outros',
            ], ';');

            foreach ($batches as $batch) {
                $orcamento  = $batch->orcamento;
                $cliente    = $orcamento?->cliente;
                $endereco   = $cliente?->enderecos?->first();
                $roteiro    = $endereco?->roteiro ?? '(sem roteiro)';
                $endStr     = $endereco
                    ? "{$endereco->logradouro}, {$endereco->numero} — {$endereco->bairro}, {$endereco->cidade}/{$endereco->uf}"
                    : '—';

                fputcsv($handle, [
                    $roteiro,
                    $batch->id,
                    $orcamento?->id ?? '—',
                    $cliente?->nome ?? '—',
                    $endStr,
                    $orcamento?->vendedor?->name ?? '—',
                    $batch->criadoPor?->name ?? '—',
                    $batch->finished_at?->format('d/m/Y H:i') ?? '—',
                    $batch->qtd_caixas   ?? 0,
                    $batch->qtd_sacos    ?? 0,
                    $batch->qtd_sacolas  ?? 0,
                    $batch->outros_embalagem ?? '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildSeparacaoQuery(Request $request)
    {
        return PickingBatch::query()
            ->where('status', 'concluido')
            ->when($request->filled('roteiro'), function ($q) use ($request) {
                $q->whereHas('orcamento.cliente.enderecos', function ($q2) use ($request) {
                    $q2->where('roteiro', 'like', '%' . $request->roteiro . '%');
                });
            })
            ->when($request->filled('data_inicio'), function ($q) use ($request) {
                $q->whereDate('finished_at', '>=', $request->data_inicio);
            })
            ->when($request->filled('data_fim'), function ($q) use ($request) {
                $q->whereDate('finished_at', '<=', $request->data_fim);
            })
            ->orderByRaw("
                (SELECT roteiro FROM enderecos
                 JOIN orcamentos ON orcamentos.cliente_id = enderecos.cliente_id
                 WHERE orcamentos.id = picking_batches.orcamento_id
                 LIMIT 1) ASC
            ")
            ->latest('finished_at');
    }
}
