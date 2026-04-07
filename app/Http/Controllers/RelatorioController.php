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
use App\Models\RelatorioEstoqueMinimo;
use App\Models\Categoria;
use App\Models\Armazem;
use App\Models\Venda;
use App\Models\ProjecaoCompra;
use App\Models\ProjecaoCompraItem;

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
     * Relatório dinâmico de estoque mínimo baseado em vendas.
     */
    public function relatorioEstoqueMinimo(Request $request)
    {
        $request->validate([
            'inicio' => 'nullable|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
        ]);

        $inicio = $request->input('inicio', now()->subMonths(1)->toDateString());
        $fim = $request->input('fim', now()->toDateString());

        $carbonInicio = \Carbon\Carbon::parse($inicio);
        $carbonFim = \Carbon\Carbon::parse($fim);
        
        // Cálculo mais preciso do número de meses (dias / 30)
        $diffDias = $carbonInicio->diffInDays($carbonFim);
        $numMeses = max(0.1, round($diffDias / 30, 2));

        // Filtrar produtos que têm alguma movimentação de venda no período
        $query = Produto::with(['cor', 'fornecedor'])
            ->select('produtos.*')
            ->selectRaw('(SELECT COALESCE(SUM(oi.quantidade), 0) 
                          FROM orcamento_itens oi 
                          JOIN orcamentos o ON o.id = oi.orcamento_id 
                          JOIN vendas v ON v.orcamento_id = o.id 
                          WHERE oi.produto_id = produtos.id 
                          AND v.data_venda BETWEEN ? AND ?) as total_vendido', [$inicio, $fim]);

        if ($request->boolean('apenas_abaixo')) {
            // Esta condição será aplicada após o cálculo manual da média
        }

        $produtos = $query->get()->map(function ($produto) use ($numMeses) {
             $produto->qtd_por_mes = $produto->total_vendido / $numMeses;
             $produto->estoque_minimo_calculado = round($produto->qtd_por_mes, 2);
             return $produto;
        });

        if ($request->boolean('apenas_abaixo')) {
            $produtos = $produtos->filter(function ($p) {
                return $p->estoque_atual < $p->estoque_minimo_calculado;
            });
        }

        if (!$request->boolean('incluir_sem_giro')) {
            $produtos = $produtos->filter(function ($p) {
                return $p->total_vendido > 0;
            });
        }

        if (count($produtos) > 1000) {
             session()->flash('warning', 'O relatório possui muitos itens (' . count($produtos) . '). Mostrando apenas os primeiros 1000. Use filtros mais específicos para ver todos.');
             $produtos = $produtos->take(1000);
         }

         // Registrar no histórico
         $this->registrarSolicitacaoEstoqueMinimo($request, count($produtos));

         return view('paginas.relatorios.estoque_minimo', compact('produtos', 'inicio', 'fim', 'numMeses'));
    }

    /**
     * Relatório de Vendas com Estoque Sugerido.
     */
    public function relatorioVendasEstoqueSugerido(Request $request)
    {
        $request->validate([
            'inicio' => 'required|date',
            'fim' => 'required|date|after_or_equal:inicio',
            'categoria_id' => 'nullable|exists:categorias,id',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'armazem_id' => 'nullable|exists:armazens,id',
        ]);

        $inicio = $request->input('inicio', now()->subMonths(1)->toDateString());
        $fim = $request->input('fim', now()->toDateString());

        $carbonInicio = \Carbon\Carbon::parse($inicio);
        $carbonFim = \Carbon\Carbon::parse($fim);
        $diffDias = $carbonInicio->diffInDays($carbonFim);
        $numMeses = max(0.1, round($diffDias / 30, 2));

        $query = Produto::with(['cor', 'fornecedor', 'categoria'])
            ->select('produtos.*')
            ->selectRaw('(SELECT COALESCE(SUM(oi.quantidade), 0) 
                          FROM orcamento_itens oi 
                          JOIN orcamentos o ON o.id = oi.orcamento_id 
                          JOIN vendas v ON v.orcamento_id = o.id 
                          WHERE oi.produto_id = produtos.id 
                          AND v.data_venda BETWEEN ? AND ?) as total_vendido', [$inicio, $fim]);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('armazem_id')) {
            $query->whereHas('movimentacoes', function ($q) use ($request) {
                $q->where('armazem_id', $request->armazem_id);
            });
        }

        $produtos = $query->get()->map(function ($produto) use ($numMeses) {
            $produto->qtd_por_mes = $produto->total_vendido / $numMeses;
            $produto->estoque_minimo_sugerido = round($produto->qtd_por_mes, 2);
            return $produto;
        });

        $categorias = \App\Models\Categoria::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $armazens = \App\Models\Armazem::orderBy('nome')->get();

        return view('paginas.relatorios.vendas_estoque_sugerido', compact(
             'produtos', 'inicio', 'fim', 'numMeses', 
             'categorias', 'fornecedores', 'armazens'
         ));
     }

     /**
      * Exportar Relatório de Vendas e Estoque Sugerido.
      */
     public function exportarVendasEstoqueSugerido(Request $request)
     {
         $request->validate([
             'inicio' => 'required|date',
             'fim' => 'required|date|after_or_equal:inicio',
             'format' => 'required|in:pdf,excel',
         ]);

         $inicio = $request->input('inicio');
         $fim = $request->input('fim');

         $carbonInicio = \Carbon\Carbon::parse($inicio);
         $carbonFim = \Carbon\Carbon::parse($fim);
         $diffDias = $carbonInicio->diffInDays($carbonFim);
         $numMeses = max(0.1, round($diffDias / 30, 2));

         $query = Produto::with(['cor', 'fornecedor', 'categoria'])
             ->select('produtos.*')
             ->selectRaw('(SELECT COALESCE(SUM(oi.quantidade), 0) 
                           FROM orcamento_itens oi 
                           JOIN orcamentos o ON o.id = oi.orcamento_id 
                           JOIN vendas v ON v.orcamento_id = o.id 
                           WHERE oi.produto_id = produtos.id 
                           AND v.data_venda BETWEEN ? AND ?) as total_vendido', [$inicio, $fim]);

         if ($request->filled('categoria_id')) {
             $query->where('categoria_id', $request->categoria_id);
         }

         if ($request->filled('fornecedor_id')) {
             $query->where('fornecedor_id', $request->fornecedor_id);
         }

         if ($request->filled('armazem_id')) {
             $query->whereHas('movimentacoes', function ($q) use ($request) {
                 $q->where('armazem_id', $request->armazem_id);
             });
         }

         $produtos = $query->get()->map(function ($produto) use ($numMeses) {
             $produto->qtd_por_mes = $produto->total_vendido / $numMeses;
             $produto->estoque_minimo_sugerido = round($produto->qtd_por_mes, 2);
             return $produto;
         });

         if ($request->format === 'pdf') {
             $pdf = Pdf::loadView('paginas.relatorios.pdf.vendas_estoque_sugerido', compact('produtos', 'inicio', 'fim', 'numMeses'));
             return $pdf->download('vendas_estoque_sugerido.pdf');
         }

         // Exportação Excel (CSV)
         $filename = "vendas_estoque_sugerido_" . now()->format('YmdHis') . ".csv";
         $handle = fopen('php://output', 'w');
         
         // Adicionar BOM para Excel reconhecer caracteres especiais
         fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
         
         header('Content-Type: text/csv; charset=utf-8');
         header("Content-Disposition: attachment; filename=\"$filename\"");

         // Cabeçalho
         fputcsv($handle, [
             'Produto', 'SKU', 'Categoria', 'Fornecedor', 
             'Total Vendido', 'Media Mensal', 'Estoque Sugerido', 'Estoque Atual'
         ], ';');

         foreach ($produtos as $p) {
             fputcsv($handle, [
                 $p->nome,
                 $p->sku,
                 $p->categoria->nome ?? 'N/A',
                 $p->fornecedor->nome_fantasia ?? 'N/A',
                 number_format($p->total_vendido, 2, ',', ''),
                 number_format($p->qtd_por_mes, 2, ',', ''),
                 number_format($p->estoque_minimo_sugerido, 2, ',', ''),
                 number_format($p->estoque_atual, 2, ',', ''),
             ], ';');
         }

         fclose($handle);
         exit;
     }

    /**
     * Auxiliar para registrar histórico de geração de relatório de estoque mínimo.
     */
    private function registrarSolicitacaoEstoqueMinimo(Request $request, int $totalItens)
    {
        // Obter o próximo número sequencial global
        $proximoNumero = RelatorioEstoqueMinimo::withTrashed()->count() + 1;
        $codigo = "RELATÓRIO {$proximoNumero}";

        RelatorioEstoqueMinimo::create([
            'codigo' => $codigo,
            'user_id' => auth()->id() ?? 1,
            'parametros' => [
                'inicio' => $request->input('inicio'),
                'fim' => $request->input('fim'),
                'apenas_abaixo' => $request->boolean('apenas_abaixo'),
                'incluir_sem_giro' => $request->boolean('incluir_sem_giro'),
                'num_meses' => max(0.1, round(\Carbon\Carbon::parse($request->input('inicio'))->diffInDays(\Carbon\Carbon::parse($request->input('fim'))) / 30, 2)),
            ],
            'status' => 'concluido',
            'total_itens' => $totalItens,
        ]);

        return $codigo;
    }

    /**
     * Histórico de solicitações de relatórios de estoque mínimo.
     */
    public function historicoEstoqueMinimo(Request $request)
    {
        $query = RelatorioEstoqueMinimo::with('user')->latest();

        if ($request->filled('inicio')) {
            $query->whereDate('created_at', '>=', $request->inicio);
        }
        if ($request->filled('fim')) {
            $query->whereDate('created_at', '<=', $request->fim);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $solicitacoes = $query->paginate(20);
        $usuarios = User::all(['id', 'name']);

        return view('paginas.relatorios.historico_estoque_minimo', compact('solicitacoes', 'usuarios'));
    }

    /**
     * Exporta o relatório de estoque mínimo em PDF.
     */
    public function exportarEstoqueMinimo(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Iniciando exportação de estoque mínimo', $request->all());

        try {
            // Aumentar limites para PDF pesado
            ini_set('memory_limit', '512M');
            set_time_limit(180);

            $request->validate([
                'inicio' => 'nullable|date',
                'fim' => 'nullable|date|after_or_equal:inicio',
            ]);

            $inicio = $request->input('inicio', now()->subMonths(1)->toDateString());
            $fim = $request->input('fim', now()->toDateString());

            $carbonInicio = \Carbon\Carbon::parse($inicio);
            $carbonFim = \Carbon\Carbon::parse($fim);
            $diffDias = $carbonInicio->diffInDays($carbonFim);
            $numMeses = max(0.1, round($diffDias / 30, 2));

            \Illuminate\Support\Facades\Log::info('Buscando produtos no banco');

            // Filtrar produtos que têm alguma movimentação de venda no período
            // para evitar processar milhares de produtos sem giro
            $query = Produto::with(['cor', 'fornecedor'])
                ->select('produtos.*')
                ->selectRaw('(SELECT COALESCE(SUM(oi.quantidade), 0) 
                              FROM orcamento_itens oi 
                              JOIN orcamentos o ON o.id = oi.orcamento_id 
                              JOIN vendas v ON v.orcamento_id = o.id 
                              WHERE oi.produto_id = produtos.id 
                              AND v.data_venda BETWEEN ? AND ?) as total_vendido', [$inicio, $fim]);

            // Se o usuário não pediu todos, vamos focar nos que tiveram giro
            if (!$request->has('incluir_sem_giro')) {
                $query->whereExists(function ($q) use ($inicio, $fim) {
                    $q->select(DB::raw(1))
                      ->from('orcamento_itens')
                      ->join('vendas', 'vendas.orcamento_id', '=', 'orcamento_itens.orcamento_id')
                      ->whereRaw('orcamento_itens.produto_id = produtos.id')
                      ->whereBetween('vendas.data_venda', [$inicio, $fim]);
                });
            }

            $produtos = $query->get()->map(function ($produto) use ($numMeses) {
                $produto->qtd_por_mes = $produto->total_vendido / $numMeses;
                $produto->estoque_minimo_calculado = round($produto->qtd_por_mes, 2);
                $produto->abaixo_minimo = $produto->estoque_atual < $produto->estoque_minimo_calculado;
                return $produto;
            });

            if ($request->filled('apenas_abaixo')) {
                 $produtos = $produtos->where('abaixo_minimo', true);
            }

            \Illuminate\Support\Facades\Log::info('Gerando PDF com ' . count($produtos) . ' produtos');

            if (count($produtos) > 1000) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'O relatório possui muitos itens (' . count($produtos) . '). Por favor, use filtros mais específicos (como "Apenas abaixo do mínimo") ou um período menor.'
                    ], 422);
                }
                return back()->with('error', 'O relatório possui muitos itens (' . count($produtos) . '). Por favor, use filtros mais específicos ou um período menor.');
            }

            // Registrar no histórico e obter código único
            $codigo = $this->registrarSolicitacaoEstoqueMinimo($request, count($produtos));
            $nomeArquivo = str_replace(' ', '_', strtolower($codigo));

            $pdf = Pdf::loadView('paginas.relatorios.pdf.estoque_minimo', compact('produtos', 'inicio', 'fim', 'numMeses', 'codigo'));
            
            \Illuminate\Support\Facades\Log::info('PDF gerado com sucesso, iniciando download');
            return $pdf->download("{$nomeArquivo}.pdf");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro na exportação de estoque mínimo: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Erro interno ao gerar relatório: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tela inicial e processamento do Relatório de Projeção de Compra.
     */
    public function relatorioProjecaoCompra(Request $request)
    {
        // Se houver parâmetros, validamos e processamos
        if ($request->has(['data_pedido', 'previsao_recebimento', 'meses_compra'])) {
            $request->validate([
                'data_pedido' => 'required|date_format:Y-m-d',
                'previsao_recebimento' => 'required|date_format:Y-m-d|after:data_pedido',
                'meses_compra' => 'required|integer|min:1|max:24',
                'abater_estoque_atual' => 'nullable|boolean',
                'abater_consumo_ate_recebimento' => 'nullable|boolean',
                'categoria_id' => 'nullable|exists:categorias,id',
                'apenas_criticos' => 'nullable|boolean',
            ]);

            $dataPedido = \Carbon\Carbon::parse($request->data_pedido);
            $previsaoRecebimento = \Carbon\Carbon::parse($request->previsao_recebimento);
            $mesesCompra = (int) $request->meses_compra;
            $abaterEstoque = $request->boolean('abater_estoque_atual', true);
            $abaterConsumo = $request->boolean('abater_consumo_ate_recebimento', true);
            
            // Período de análise de curva (padrão 6 meses antes do pedido)
            $inicioAnalise = $dataPedido->copy()->subMonths(6)->toDateString();
            $fimAnalise = $dataPedido->toDateString();
            
            // Diferença em dias até o recebimento para o cálculo do consumo previsto
            $diasAteRecebimento = $dataPedido->diffInDays($previsaoRecebimento);
            $mesesAteRecebimento = $diasAteRecebimento / 30;

            // Query base de produtos
            $query = Produto::with(['categoria', 'fornecedor'])
                ->select('produtos.*')
                ->selectRaw("(SELECT COALESCE(SUM(oi.quantidade), 0) 
                              FROM orcamento_itens oi 
                              JOIN orcamentos o ON o.id = oi.orcamento_id 
                              JOIN vendas v ON v.orcamento_id = o.id 
                              WHERE oi.produto_id = produtos.id 
                              AND v.data_venda BETWEEN ? AND ?) as vendas_analise", [$inicioAnalise, $fimAnalise]);

            if ($request->filled('categoria_id')) {
                $query->where('categoria_id', $request->categoria_id);
            }

            $produtos = $query->get()->map(function ($produto) use ($mesesCompra, $abaterEstoque, $abaterConsumo, $mesesAteRecebimento) {
                // Curva de consumo (vendas nos últimos 6 meses / 6)
                $produto->consumo_mensal = $produto->vendas_analise / 6;
                
                // Cálculo da Projeção
                // Projeção = (Consumo Mensal * Meses Compra) - Estoque (se selecionado) - Consumo até Receber (se selecionado)
                $projecao = $produto->consumo_mensal * $mesesCompra;
                
                $produto->detalhe_estoque = $abaterEstoque ? $produto->estoque_atual : 0;
                $produto->detalhe_consumo_previsao = $abaterConsumo ? ($produto->consumo_mensal * $mesesAteRecebimento) : 0;
                
                $sugerido = $projecao - $produto->detalhe_estoque - $produto->detalhe_consumo_previsao;
                
                $produto->quantidade_sugerida = max(0, round($sugerido, 2));
                $produto->abaixo_minimo = $produto->estoque_atual < $produto->estoque_minimo;
                
                return $produto;
            });

            if ($request->boolean('apenas_criticos')) {
                $produtos = $produtos->where('abaixo_minimo', true);
            }

            // Ordenação: Críticos primeiro, depois por volume sugerido
            $produtos = $produtos->sortByDesc(function($p) {
                return [$p->abaixo_minimo, $p->quantidade_sugerida];
            });

            // Registrar no histórico se não for apenas visualização rápida
            if ($request->has('save_history')) {
                $this->salvarHistoricoProjecao($request, $produtos);
            }

            $categorias = Categoria::orderBy('nome')->get();
            return view('paginas.relatorios.projecao_compra', compact('produtos', 'categorias'));
        }

        $categorias = Categoria::orderBy('nome')->get();
        return view('paginas.relatorios.projecao_compra', compact('categorias'));
    }

    /**
     * Salva a projeção no banco para consulta posterior.
     */
    private function salvarHistoricoProjecao(Request $request, $produtos)
    {
        $codigo = 'PROJ-' . now()->format('Ymd') . '-' . (ProjecaoCompra::whereDate('created_at', today())->count() + 1);
        
        $projecao = ProjecaoCompra::create([
            'codigo' => $codigo,
            'user_id' => auth()->id() ?? 1,
            'data_pedido' => $request->data_pedido,
            'previsao_recebimento' => $request->previsao_recebimento,
            'meses_compra' => $request->meses_compra,
            'abater_estoque_atual' => $request->boolean('abater_estoque_atual'),
            'abater_consumo_ate_recebimento' => $request->boolean('abater_consumo_ate_recebimento'),
            'filtros' => $request->except(['_token', 'save_history']),
            'total_itens' => $produtos->where('quantidade_sugerida', '>', 0)->count(),
            'valor_total_estimado' => $produtos->sum(function($p) { return $p->quantidade_sugerida * $p->preco_custo; }),
        ]);

        foreach ($produtos->where('quantidade_sugerida', '>', 0) as $p) {
            ProjecaoCompraItem::create([
                'projecao_compra_id' => $projecao->id,
                'produto_id' => $p->id,
                'consumo_mensal' => $p->consumo_mensal,
                'estoque_atual' => $p->estoque_atual,
                'previsao_consumo_recebimento' => $p->detalhe_consumo_previsao,
                'quantidade_sugerida' => $p->quantidade_sugerida,
                'valor_unitario' => $p->preco_custo,
                'abaixo_minimo' => $p->abaixo_minimo,
            ]);
        }
    }

    /**
     * Exporta o relatório de projeção de compra em PDF.
     */
    public function exportarProjecaoCompra(Request $request)
    {
        $request->validate([
            'data_pedido' => 'required|date_format:Y-m-d',
            'previsao_recebimento' => 'required|date_format:Y-m-d|after:data_pedido',
            'meses_compra' => 'required|integer|min:1|max:24',
            'format' => 'required|in:pdf,excel',
        ]);

        $dataPedido = \Carbon\Carbon::parse($request->data_pedido);
        $previsaoRecebimento = \Carbon\Carbon::parse($request->previsao_recebimento);
        $mesesCompra = (int) $request->meses_compra;
        $abaterEstoque = $request->boolean('abater_estoque_atual', true);
        $abaterConsumo = $request->boolean('abater_consumo_ate_recebimento', true);
        
        $inicioAnalise = $dataPedido->copy()->subMonths(6)->toDateString();
        $fimAnalise = $dataPedido->toDateString();
        $diasAteRecebimento = $dataPedido->diffInDays($previsaoRecebimento);
        $mesesAteRecebimento = $diasAteRecebimento / 30;

        $query = Produto::with(['categoria', 'fornecedor'])
            ->select('produtos.*')
            ->selectRaw("(SELECT COALESCE(SUM(oi.quantidade), 0) 
                          FROM orcamento_itens oi 
                          JOIN orcamentos o ON o.id = oi.orcamento_id 
                          JOIN vendas v ON v.orcamento_id = o.id 
                          WHERE oi.produto_id = produtos.id 
                          AND v.data_venda BETWEEN ? AND ?) as vendas_analise", [$inicioAnalise, $fimAnalise]);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $produtos = $query->get()->map(function ($produto) use ($mesesCompra, $abaterEstoque, $abaterConsumo, $mesesAteRecebimento) {
            $produto->consumo_mensal = $produto->vendas_analise / 6;
            $projecao = $produto->consumo_mensal * $mesesCompra;
            $produto->detalhe_estoque = $abaterEstoque ? $produto->estoque_atual : 0;
            $produto->detalhe_consumo_previsao = $abaterConsumo ? ($produto->consumo_mensal * $mesesAteRecebimento) : 0;
            $sugerido = $projecao - $produto->detalhe_estoque - $produto->detalhe_consumo_previsao;
            $produto->quantidade_sugerida = max(0, round($sugerido, 2));
            $produto->abaixo_minimo = $produto->estoque_atual < $produto->estoque_minimo;
            return $produto;
        });

        if ($request->boolean('apenas_criticos')) {
            $produtos = $produtos->where('abaixo_minimo', true);
        }

        $produtos = $produtos->sortByDesc(function($p) {
            return [$p->abaixo_minimo, $p->quantidade_sugerida];
        });

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('paginas.relatorios.pdf.projecao_compra', compact(
                'produtos', 'dataPedido', 'previsaoRecebimento', 'mesesCompra', 'abaterEstoque', 'abaterConsumo'
            ));
            return $pdf->download('projecao_compra.pdf');
        }

        // Exportação Excel (CSV)
        $filename = "projecao_compra_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        fputcsv($handle, [
            'Produto', 'SKU', 'Consumo Mensal', 'Estoque Atual', 
            'Consumo Previsto (até recebimento)', 'Sugestão Compra', 'Preço Custo', 'Subtotal Estimado'
        ], ';');

        foreach ($produtos->where('quantidade_sugerida', '>', 0) as $p) {
            fputcsv($handle, [
                $p->nome,
                $p->sku,
                number_format($p->consumo_mensal, 2, ',', ''),
                number_format($p->estoque_atual, 2, ',', ''),
                number_format($p->detalhe_consumo_previsao, 2, ',', ''),
                number_format($p->quantidade_sugerida, 2, ',', ''),
                number_format($p->preco_custo, 2, ',', ''),
                number_format($p->quantidade_sugerida * $p->preco_custo, 2, ',', ''),
            ], ';');
        }

        fclose($handle);
        exit;
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
        $ranking = collect();

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
            ->where('produtos.estoque_atual', '>', 0)
            ->whereNotNull('movimentacao_produtos.data_vencimento')
            ->select('movimentacao_produtos.*', 'produtos.estoque_atual as estoque_total_produto');

        if ($request->filled('fornecedor_id')) {
            $query->where('movimentacao_produtos.fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('produto_nome')) {
            $query->where('produtos.nome', 'like', '%' . $request->produto_nome . '%');
        }

        if ($request->filled('sku')) {
            $query->where('produtos.sku', 'like', '%' . $request->sku . '%');
        }

        if ($request->filled('tipo_produto')) {
            $query->where('produtos.tipo_produto_sped', $request->tipo_produto);
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

        if ($request->filled('sku')) {
            $query->whereHas('itens.produto', function($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->sku . '%');
            });
        }

        if ($request->filled('tipo_produto')) {
            $query->whereHas('itens.produto', function($q) use ($request) {
                $q->where('tipo_produto_sped', $request->tipo_produto);
            });
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

        if ($request->filled('produto_nome')) {
            $query->whereHas('itens.produto', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->produto_nome . '%');
            });
        }
        if ($request->filled('sku')) {
            $query->whereHas('itens.produto', function($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->sku . '%');
            });
        }
        if ($request->filled('responsavel_id')) {
            $query->where('usuario_id', $request->responsavel_id);
        }
        if ($request->filled('pedido_compra_id')) {
            $query->where('pedido_compra_id', $request->pedido_compra_id);
        }
        if ($request->filled('vendedor_id')) {
            $query->whereHas('pedidoCompra', function($q) use ($request) {
                $q->where('usuario_id', $request->vendedor_id); // In PedidoCompra, usuario_id is the buyer
            })->orWhereHas('pedido.vendedor', function($q) use ($request) {
                $q->where('id', $request->vendedor_id);
            });
        }

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

    /**
     * Relatório de Fluxo de Caixa (Previsto vs Realizado).
     */
    public function fluxoCaixa(Request $request, \App\Services\FluxoCaixaService $service)
    {
        $dataInicio = $request->filled('data_inicio') ? \Carbon\Carbon::parse($request->data_inicio) : now()->startOfMonth();
        $dataFim = $request->filled('data_fim') ? \Carbon\Carbon::parse($request->data_fim) : now()->endOfMonth();

        $dados = $service->obterDadosFluxo($dataInicio, $dataFim);

        return view('paginas.relatorios.fluxo_caixa', compact('dados'));
    }
}
