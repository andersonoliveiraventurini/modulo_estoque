<?php
namespace App\Http\Controllers;

use App\Models\Falta;
use App\Models\FaltaItem;
use App\Models\Produto;
use App\Models\Vendedor;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaltaController extends Controller
{
    public function index(Request $request)
    {
        $query = Falta::with(['itens.produto', 'user', 'vendedor', 'cliente'])
            ->latest();

        if ($request->filled('produto_id')) {
            $query->whereHas('itens', fn($q) => $q->where('produto_id', $request->produto_id));
        }
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('cliente')) {
            $query->where(function($q) use ($request) {
                $q->where('nome_cliente', 'like', '%'.$request->cliente.'%')
                  ->orWhereHas('cliente', fn($q2) => $q2->where('nome', 'like', '%'.$request->cliente.'%'));
            });
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $faltas = $query->paginate(20)->withQueryString();
        $vendedores = Vendedor::with('user')->get();
        $produtos = Produto::orderBy('nome')->get();

        return view('paginas.faltas.index', compact('faltas', 'vendedores', 'produtos'));
    }

    public function create()
    {
        $vendedores = Vendedor::with('user')->get();
        $clientes = Cliente::orderBy('nome')->get();
        return view('paginas.faltas.create', compact('vendedores', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'nullable|exists:produtos,id',
            'itens.*.descricao_produto' => 'nullable|string|max:255',
            'itens.*.quantidade' => 'required|numeric|min:0.001',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $falta = Falta::create([
                'user_id' => auth()->id(),
                'vendedor_id' => $request->vendedor_id,
                'nome_cliente' => $request->nome_cliente,
                'cliente_id' => $request->cliente_id,
                'observacao' => $request->observacao,
                'valor_total' => 0,
            ]);

            $total = 0;
            foreach ($request->itens as $item) {
                $valorTotal = $item['quantidade'] * $item['valor_unitario'];
                $total += $valorTotal;

                $descricao = $item['descricao_produto'] ?? null;
                if (!$descricao && !empty($item['produto_id'])) {
                    $produto = Produto::find($item['produto_id']);
                    $descricao = $produto?->nome;
                }

                FaltaItem::create([
                    'falta_id' => $falta->id,
                    'produto_id' => $item['produto_id'] ?? null,
                    'descricao_produto' => $descricao,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total' => $valorTotal,
                ]);
            }

            $falta->update(['valor_total' => $total]);
        });

        return redirect()->route('faltas.index')
            ->with('success', 'Falta registrada com sucesso!');
    }

    public function show(Falta $falta)
    {
        $falta->load(['itens.produto', 'user', 'vendedor', 'cliente']);
        return view('paginas.faltas.show', compact('falta'));
    }

    public function relatorio(Request $request)
    {
        $query = Falta::with(['itens.produto', 'vendedor.user', 'cliente', 'user'])
            ->latest();

        if ($request->filled('produto_id')) {
            $query->whereHas('itens', fn($q) => $q->where('produto_id', $request->produto_id));
        }
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('cliente')) {
            $query->where(function($q) use ($request) {
                $q->where('nome_cliente', 'like', '%'.$request->cliente.'%')
                  ->orWhereHas('cliente', fn($q2) => $q2->where('nome', 'like', '%'.$request->cliente.'%'));
            });
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $faltas = $query->get();
        $vendedores = Vendedor::with('user')->get();
        $produtos = Produto::orderBy('nome')->get();

        return view('paginas.faltas.relatorio', compact('faltas', 'vendedores', 'produtos'));
    }

    public function buscarProduto(Request $request)
    {
        $produtos = Produto::where(function($q) use ($request) {
                $q->where('nome', 'like', '%'.$request->q.'%')
                  ->orWhere('id', 'like', '%'.$request->q.'%')
                  ->orWhere('sku', 'like', '%'.$request->q.'%');
            })
            ->select('id', 'nome', 'sku', 'preco_venda')
            ->limit(15)
            ->get();

        return response()->json($produtos);
    }

    /**
     * Retorna faltas pendentes via JSON para preenchimento em Pedido de Compra.
     */
    public function pendentes()
    {
        $faltas = Falta::with(['itens.produto'])
            ->latest()
            ->get();

        return response()->json($faltas);
    }
}

