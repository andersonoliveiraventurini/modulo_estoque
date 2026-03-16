<?php

namespace App\Http\Controllers;

use App\Models\PickingBatch;
use App\Models\Produto;
use App\Models\PedidoCompra;
use App\Models\Fornecedor;
use App\Models\Cliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\PickingItem;
use App\Models\ConferenciaItem;
use Illuminate\Support\Facades\DB;
use App\Models\Movimentacao;
use App\Models\MovimentacaoProduto;
use App\Models\InconsistenciaRecebimento;
use App\Models\OrcamentoItens;
use App\Models\User;

class RelatorioController extends Controller
{
    /**
     * Dashboard de relatórios.
     */
    public function index()
    {
        return view('paginas.relatorios.index');
    }

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
     * Relatório de Fila de Entrega (Pedidos concluídos aguardando romaneio).
     */
    public function separacaoPorRoteiro(Request $request)
    {
        $query = $this->buildSeparacaoQuery($request);

        // Apenas lotes que NÃO estão em nenhum romaneio
        $query->whereNull('romaneio_id');

        $batches = $query->with([
                'orcamento.cliente.enderecos',
                'orcamento.vendedor',
                'criadoPor',
                'items.produto'
            ])
            ->get();

        // Agrupamos os lotes por Cidade/Bairro já que não usamos mais rota fixa no cliente
        $groupedBatches = $batches->groupBy(function($batch) {
            $cliente = $batch->orcamento?->cliente;
            $endereco = $cliente?->enderecos->where('tipo', 'entrega')->first() 
                     ?? $cliente?->enderecos->where('tipo', 'comercial')->first();
            
            return $endereco ? "{$endereco->cidade} - {$endereco->bairro}" : 'SEM ENDEREÇO';
        });

        $stats = $batches->count();

        return view('paginas.relatorios.separacao_por_roteiro', compact('groupedBatches', 'stats'));
    }

    /**
     * Exporta Separações concluídas em CSV, agrupadas por Roteiro.
     */
    public function exportarSeparacaoPorRoteiro(Request $request): StreamedResponse
    {
        $batches = $this->buildSeparacaoQuery($request)
            ->whereNull('romaneio_id')
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
            ->where('picking_batches.status', 'concluido')
            ->when($request->filled('roteiro'), function ($q) use ($request) {
                $q->whereHas('orcamento.cliente.enderecos', function ($q2) use ($request) {
                    $q2->where('cidade', 'like', '%' . $request->roteiro . '%')
                       ->orWhere('bairro', 'like', '%' . $request->roteiro . '%');
                });
            })
            ->when($request->filled('data_inicio'), function ($q) use ($request) {
                $q->whereDate('finished_at', '>=', $request->data_inicio);
            })
            ->when($request->filled('data_fim'), function ($q) use ($request) {
                $q->whereDate('finished_at', '<=', $request->data_fim);
            })
            ->orderBy('finished_at', 'desc');
    }

    /**
     * Exporta Separações concluídas em PDF para o motorista.
     */
    public function exportarSeparacaoPorRoteiroPdf(Request $request)
    {
        $batches = $this->buildSeparacaoQuery($request)
            ->with([
                'orcamento.cliente.enderecos',
                'orcamento.vendedor',
                'items.produto',
                'criadoPor'
            ])
            ->get();

        $pdf = Pdf::loadView('paginas.relatorios.pdf.separacao_roteiro_motorista', compact('batches'));

        // Configurações para paisagem se houver muitas colunas ou papel A4 normal
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('separacao_roteiro_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Relatório de Divergências Logísticas (Inconsistências de Separação e Conferência).
     */
    public function divergencias(Request $request)
    {
        // 1. Inconsistências na Separação (PickingItem onde qty_separada < qty_solicitada)
        $separacoes = PickingItem::where('qty_separada', '<', DB::raw('qty_solicitada'))
            ->where('status', 'concluido')
            ->with(['produto', 'batch.orcamento.cliente', 'separador'])
            ->get()
            ->map(function ($item) {
                return (object) [
                    'data' => $item->updated_at,
                    'tipo' => 'Separação',
                    'produto_nome' => $item->produto->nome ?? 'N/A',
                    'produto_sku' => $item->produto->sku ?? 'N/A',
                    'orcamento_id' => $item->batch->orcamento_id ?? 'N/A',
                    'cliente' => $item->batch->orcamento->cliente->nome ?? 'N/A',
                    'qtd_esperada' => $item->qty_solicitada,
                    'qtd_real' => $item->qty_separada,
                    'motivo' => $item->motivo_nao_separado ?? 'Não informado',
                    'responsavel' => $item->separador->name ?? 'N/A',
                ];
            });

        // 2. Divergências na Conferência (ConferenciaItem onde divergencia = true)
        $conferencias = ConferenciaItem::where('divergencia', true)
            ->with(['produto', 'conferencia.orcamento.cliente', 'conferidoPor'])
            ->get()
            ->map(function ($item) {
                return (object) [
                    'data' => $item->created_at,
                    'tipo' => 'Conferência',
                    'produto_nome' => $item->produto->nome ?? 'N/A',
                    'produto_sku' => $item->produto->sku ?? 'N/A',
                    'orcamento_id' => $item->conferencia->orcamento_id ?? 'N/A',
                    'cliente' => $item->conferencia->orcamento->cliente->nome ?? 'N/A',
                    'qtd_esperada' => $item->qty_separada,
                    'qtd_real' => $item->qty_conferida,
                    'motivo' => $item->motivo_divergencia ?? 'Não informado',
                    'responsavel' => $item->conferidoPor->name ?? 'N/A',
                ];
            });

        // Unificar e ordenar por data decrescente
        $divergencias = $separacoes->concat($conferencias)->sortByDesc('data');

        return view('paginas.relatorios.divergencias', compact('divergencias'));
    }

    /**
     * Relatório de Data de Vencimento de Produtos.
     */
    public function vencimentoProdutos(Request $request)
    {
        $query = MovimentacaoProduto::query()
            ->join('movimentacoes', 'movimentacoes.id', '=', 'movimentacao_produtos.movimentacao_id')
            ->join('produtos', 'produtos.id', '=', 'movimentacao_produtos.produto_id')
            ->where('movimentacoes.status', 'aprovado')
            ->whereNotNull('movimentacao_produtos.data_vencimento')
            ->select('movimentacao_produtos.*');

        if ($request->filled('fornecedor_id')) {
            $query->where('movimentacao_produtos.fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('produto_nome')) {
            $query->where('produtos.nome', 'like', '%' . $request->produto_nome . '%');
        }

        if ($request->filled('sku')) {
            $query->where('produtos.sku', 'like', '%' . $request->sku . '%');
        }

        if ($request->filled('data_inicio')) {
            $query->where('movimentacao_produtos.data_vencimento', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('movimentacao_produtos.data_vencimento', '<=', $request->data_fim);
        }

        $vencimentos = $query->with(['produto', 'fornecedor'])->paginate(20);
        $fornecedores = Fornecedor::all(['id', 'nome_fantasia']);

        return view('paginas.relatorios.vencimento_produtos', compact('vencimentos', 'fornecedores'));
    }

    /**
     * Relatório de Movimentação de Estoque - Reposição de Estoque.
     */
    public function reposicaoEstoque(Request $request)
    {
        $query = Movimentacao::where('is_reposicao', true)
            ->where('status', 'aprovado')
            ->with(['itens.produto', 'fornecedor', 'usuario', 'supervisor']);

        if ($request->filled('data_inicio')) {
            $query->where('data_movimentacao', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->where('data_movimentacao', '<=', $request->data_fim);
        }
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('repositor_id')) {
            $query->where('usuario_id', $request->repositor_id);
        }
        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        $movimentacoes = $query->latest('data_movimentacao')->paginate(20);
        $fornecedores = Fornecedor::all(['id', 'nome_fantasia']);
        $usuarios = User::all(['id', 'name']);

        return view('paginas.relatorios.reposicao_estoque', compact('movimentacoes', 'fornecedores', 'usuarios'));
    }

    /**
     * Relatório de Recebimento de Produtos.
     */
    public function recebimentoProdutos(Request $request)
    {
        $query = Movimentacao::where('tipo', 'entrada')
            ->where('status', 'aprovado')
            ->with(['itens.produto', 'fornecedor', 'usuario', 'pedidoCompra']);

        if ($request->filled('data_inicio')) $query->where('data_movimentacao', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->where('data_movimentacao', '<=', $request->data_fim);
        if ($request->filled('fornecedor_id')) $query->where('fornecedor_id', $request->fornecedor_id);
        if ($request->filled('nf')) $query->where('nota_fiscal_fornecedor', 'like', '%' . $request->nf . '%');
        if ($request->filled('romaneio')) $query->where('romaneiro', 'like', '%' . $request->romaneio . '%');

        $recebimentos = $query->latest('data_movimentacao')->paginate(20);
        $fornecedores = Fornecedor::all(['id', 'nome_fantasia']);
        $vendedores = User::all(['id', 'name']); // Assuming sellers are users

        return view('paginas.relatorios.recebimento_produtos', compact('recebimentos', 'fornecedores', 'vendedores'));
    }

    /**
     * Relatório de Devoluções.
     */
    public function devolucoes(Request $request)
    {
        $query = Movimentacao::where('is_devolucao', true)
            ->where('status', 'aprovado')
            ->with(['itens.produto', 'fornecedor', 'usuario']);

        if ($request->filled('data_inicio')) $query->where('data_movimentacao', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->where('data_movimentacao', '<=', $request->data_fim);

        $devolucoes = $query->latest('data_movimentacao')->paginate(20);

        return view('paginas.relatorios.devolucoes', compact('devolucoes'));
    }

    /**
     * Relatório de Não Conformidade.
     */
    public function naoConformidade(Request $request)
    {
        $query = InconsistenciaRecebimento::with(['pedidoCompra', 'produto', 'usuario', 'movimentacao']);

        if ($request->filled('data_inicio')) $query->where('created_at', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->where('created_at', '<=', $request->data_fim);

        $inconsistencias = $query->latest()->paginate(20);

        return view('paginas.relatorios.nao_conformidade', compact('inconsistencias'));
    }

    /**
     * Relatório de Saída de Produtos.
     */
    public function saidaProdutos(Request $request)
    {
        $query = Movimentacao::where('tipo', 'saida')
            ->where('status', 'aprovado')
            ->with(['itens.produto', 'usuario']);

        if ($request->filled('data_inicio')) $query->where('data_movimentacao', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->where('data_movimentacao', '<=', $request->data_fim);

        $saidas = $query->latest('data_movimentacao')->paginate(20);

        return view('paginas.relatorios.saida_produtos', compact('saidas'));
    }

    /**
     * Relatório de Produtos Vendas Margem.
     */
    public function vendasMargem(Request $request)
    {
        $vendas = collect();
        $produto = null;

        if ($request->filled('produto_id')) {
            $produto = Produto::find($request->produto_id);
            $query = OrcamentoItens::where('produto_id', $request->produto_id)
                ->whereHas('orcamento', function($q) use ($request) {
                    $q->where('status', 'concluido');
                    if ($request->filled('data_inicio')) $q->where('created_at', '>=', $request->data_inicio);
                    if ($request->filled('data_fim')) $q->where('created_at', '<=', $request->data_fim);
                })
                ->with(['orcamento.cliente', 'orcamento.vendedor']);
            
            $vendas = $query->get();
        }

        $produtos = Produto::all(['id', 'nome', 'sku']);

        return view('paginas.relatorios.vendas_margem', compact('vendas', 'produto', 'produtos'));
    }
}
