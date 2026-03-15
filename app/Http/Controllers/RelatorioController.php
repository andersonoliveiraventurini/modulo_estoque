<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\PedidoCompra;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
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
}
