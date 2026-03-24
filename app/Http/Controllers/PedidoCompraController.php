<?php

namespace App\Http\Controllers;

use App\Models\CondicoesPagamento;
use App\Models\Fornecedor;
use App\Models\PedidoCompra;
use App\Models\PedidoCompraItem;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PedidoCompraController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCompra::with(['fornecedor', 'itens', 'usuario'])
            ->latest()
            ->paginate(20);

        return view('paginas.pedido_compras.index', compact('pedidos'));
    }

    public function create(Request $request, \App\Services\CnpjService $cnpjService)
    {
        $fornecedores = Fornecedor::select('id', 'nome_fantasia', 'razao_social', 'cnpj')->get();
        $produtos = Produto::select('id', 'nome', 'sku')->with('cor')->get();
        $condicoes = CondicoesPagamento::all();

        $fornecedorStatus = null;
        if ($request->has('fornecedor_id')) {
            $fornecedor = Fornecedor::find($request->fornecedor_id);
            if ($fornecedor && $fornecedor->cnpj) {
                $fornecedorStatus = $cnpjService->consultarCnpj($fornecedor->cnpj);
            }
        }

        return view('paginas.pedido_compras.create', compact('fornecedores', 'produtos', 'condicoes', 'fornecedorStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_id'   => 'required|exists:fornecedores,id',
            'data_pedido'     => 'required|date',
            'previsao_entrega' => 'nullable|date',
            'numero_pedido'   => 'nullable|string|max:100',
            'arquivo_pedido'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'condicao_pagamento_id' => 'required|exists:condicoes_pagamento,id',
            'forma_pagamento_descricao' => 'nullable|string',
            'observacao'      => 'nullable|string',
            'itens'           => 'required|array|min:1',
            'itens.*.produto_id'     => 'nullable|exists:produtos,id',
            'itens.*.descricao_livre' => 'nullable|string|max:500',
            'itens.*.quantidade'     => 'required|numeric|min:0.001',
            'itens.*.valor_unitario' => 'nullable|numeric|min:0',
            'itens.*.valor_total'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $arquivoPath = null;
            if ($request->hasFile('arquivo_pedido')) {
                $arquivoPath = $request->file('arquivo_pedido')->store('pedido_compras', 'public');
            }

            $valorTotal = collect($request->itens)->sum('valor_total');

            $pedido = PedidoCompra::create([
                'fornecedor_id'    => $request->fornecedor_id,
                'usuario_id'       => auth()->id(),
                'data_pedido'      => $request->data_pedido,
                'previsao_entrega' => $request->previsao_entrega,
                'status'           => 'aguardando',
                'numero_pedido'    => $request->numero_pedido,
                'arquivo_pedido'   => $arquivoPath,
                'condicao_pagamento_id' => $request->condicao_pagamento_id,
                'forma_pagamento_descricao' => $request->forma_pagamento_descricao,
                'valor_total'      => $valorTotal,
                'observacao'       => $request->observacao,
            ]);

            foreach ($request->itens as $item) {
                $produto = !empty($item['produto_id']) ? Produto::find($item['produto_id']) : null;
                $precoCustoAnterior = $produto ? $produto->preco_custo : null;

                PedidoCompraItem::create([
                    'pedido_compra_id' => $pedido->id,
                    'produto_id'       => $item['produto_id'] ?? null,
                    'descricao_livre'  => $item['descricao_livre'] ?? null,
                    'quantidade'       => $item['quantidade'],
                    'valor_unitario'   => $item['valor_unitario'] ?? null,
                    'valor_total'      => $item['valor_total'] ?? null,
                    'observacao'       => $item['observacao'] ?? null,
                    'preco_custo_anterior' => $precoCustoAnterior,
                ]);

                // Atualiza o preço de custo do produto conforme solicitado
                if ($produto && !empty($item['valor_unitario'])) {
                    $produto->update([
                        'preco_custo' => $item['valor_unitario']
                    ]);
                }
            }
        });

        return redirect()->route('pedido_compras.index')->with('success', 'Pedido de compra criado com sucesso.');
    }

    public function show(PedidoCompra $pedidoCompra)
    {
        $pedidoCompra->load(['fornecedor', 'itens.produto', 'condicaoPagamento', 'usuario']);
        return view('paginas.pedido_compras.show', compact('pedidoCompra'));
    }

    public function edit(PedidoCompra $pedidoCompra, \App\Services\CnpjService $cnpjService)
    {
        $pedidoCompra->load('itens');
        $fornecedores = Fornecedor::select('id', 'nome_fantasia', 'razao_social', 'cnpj')->get();
        $produtos = Produto::select('id', 'nome', 'sku')->with('cor')->get();
        $condicoes = CondicoesPagamento::all();

        $fornecedorStatus = null;
        if ($pedidoCompra->fornecedor && $pedidoCompra->fornecedor->cnpj) {
            $fornecedorStatus = $cnpjService->consultarCnpj($pedidoCompra->fornecedor->cnpj);
        }

        return view('paginas.pedido_compras.edit', compact('pedidoCompra', 'fornecedores', 'produtos', 'condicoes', 'fornecedorStatus'));
    }

    public function update(Request $request, PedidoCompra $pedidoCompra)
    {
        $request->validate([
            'fornecedor_id'   => 'required|exists:fornecedores,id',
            'data_pedido'     => 'required|date',
            'previsao_entrega' => 'nullable|date',
            'status'          => 'required|in:aguardando,parcialmente_recebido,recebido,cancelado',
            'numero_pedido'   => 'nullable|string|max:100',
            'arquivo_pedido'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'condicao_pagamento_id' => 'required|exists:condicoes_pagamento,id',
            'forma_pagamento_descricao' => 'nullable|string',
            'observacao'      => 'nullable|string',
            'itens'           => 'required|array|min:1',
            'itens.*.produto_id'     => 'nullable|exists:produtos,id',
            'itens.*.descricao_livre' => 'nullable|string|max:500',
            'itens.*.quantidade'     => 'required|numeric|min:0.001',
            'itens.*.valor_unitario' => 'nullable|numeric|min:0',
            'itens.*.valor_total'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pedidoCompra) {
            $arquivoPath = $pedidoCompra->arquivo_pedido;
            if ($request->hasFile('arquivo_pedido')) {
                if ($arquivoPath) Storage::disk('public')->delete($arquivoPath);
                $arquivoPath = $request->file('arquivo_pedido')->store('pedido_compras', 'public');
            }

            $valorTotal = collect($request->itens)->sum('valor_total');

            $pedidoCompra->update([
                'fornecedor_id'    => $request->fornecedor_id,
                'data_pedido'      => $request->data_pedido,
                'previsao_entrega' => $request->previsao_entrega,
                'status'           => $request->status,
                'numero_pedido'    => $request->numero_pedido,
                'arquivo_pedido'   => $arquivoPath,
                'condicao_pagamento_id' => $request->condicao_pagamento_id,
                'forma_pagamento_descricao' => $request->forma_pagamento_descricao,
                'valor_total'      => $valorTotal,
                'observacao'       => $request->observacao,
                'editor_usuario_id' => auth()->id(),
                'editado_em'       => now(),
            ]);

            // Salva o caminho do arquivo antigo antes de atualizar
            $arquivoPath = $pedidoCompra->arquivo_pedido;

            // Reverte os preços dos itens ANTIGOS antes de deletá-los
            foreach ($pedidoCompra->itens as $itemAntigo) {
                if ($itemAntigo->produto_id && $itemAntigo->preco_custo_anterior !== null) {
                    Produto::where('id', $itemAntigo->produto_id)->update([
                        'preco_custo' => $itemAntigo->preco_custo_anterior
                    ]);
                }
            }

            $pedidoCompra->itens()->delete();

            foreach ($request->itens as $item) {
                $produto = !empty($item['produto_id']) ? Produto::find($item['produto_id']) : null;
                $precoCustoAnterior = $produto ? $produto->preco_custo : null;

                PedidoCompraItem::create([
                    'pedido_compra_id' => $pedidoCompra->id,
                    'produto_id'       => $item['produto_id'] ?? null,
                    'descricao_livre'  => $item['descricao_livre'] ?? null,
                    'quantidade'       => $item['quantidade'],
                    'valor_unitario'   => $item['valor_unitario'] ?? null,
                    'valor_total'      => $item['valor_total'] ?? null,
                    'observacao'       => $item['observacao'] ?? null,
                    'preco_custo_anterior' => $precoCustoAnterior,
                ]);

                // Atualiza o preço de custo do produto conforme solicitado
                if ($produto && !empty($item['valor_unitario'])) {
                    $produto->update([
                        'preco_custo' => $item['valor_unitario']
                    ]);
                }
            }
        });

        return redirect()->route('pedido_compras.show', $pedidoCompra)->with('success', 'Pedido de compra atualizado com sucesso!');
    }

    public function destroy(PedidoCompra $pedidoCompra)
    {
        DB::transaction(function () use ($pedidoCompra) {
            // Reverte os preços dos produtos conforme solicitado
            // Percorre os itens para restaurar o preço de custo anterior
            foreach ($pedidoCompra->itens as $item) {
                if ($item->produto_id && $item->preco_custo_anterior !== null) {
                    Produto::where('id', $item->produto_id)->update([
                        'preco_custo' => $item->preco_custo_anterior
                    ]);
                }
            }

            // Remove o arquivo se existir
            if ($pedidoCompra->arquivo_pedido) {
                Storage::disk('public')->delete($pedidoCompra->arquivo_pedido);
            }

            $pedidoCompra->delete();
        });

        return redirect()->route('pedido_compras.index')->with('success', 'Pedido de compra deletado e preços de custo restaurados!');
    }

    /**
     * Retorna itens de um pedido de compra para preenchimento automático em movimentações.
     */
    public function itensJson(PedidoCompra $pedidoCompra)
    {
        $pedidoCompra->load(['itens.produto.cor', 'itens.produto.fornecedor']);
        return response()->json($pedidoCompra->itens->map(function($item) {
            return [
                'produto_id'   => $item->produto_id,
                'nome'         => optional($item->produto)->nome,
                'sku'          => optional($item->produto)->sku,
                'cor'          => optional($item->produto->cor)->nome,
                'fornecedor_id'=> optional($item->produto)->fornecedor_id ?? null,
                'quantidade'   => $item->quantidade,
                'valor_unitario'=> $item->valor_unitario,
            ];
        }));
    }

    public function consultaPrazo(Request $request)
    {
        $query = \App\Models\PedidoCompra::with([
            'fornecedor', 'usuario', 'itens.produto', 'followUps.user'
        ])->whereNotIn('status', ['recebido', 'cancelado']);

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('numero_pedido')) {
            $query->where('numero_pedido', 'like', '%'.$request->numero_pedido.'%');
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_pedido', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_pedido', '<=', $request->data_fim);
        }
        if ($request->filled('produto_id')) {
            $query->whereHas('itens', fn($q) => $q->where('produto_id', $request->produto_id));
        }

        $pedidos = $query->orderBy('previsao_entrega')->paginate(30)->withQueryString();
        $fornecedores = \App\Models\Fornecedor::where('ativo', true)->orderBy('nome')->get();
        $produtos = \App\Models\Produto::where('ativo', true)->orderBy('nome')->get();

        return view('paginas.pedido_compras.consulta_prazo', compact('pedidos', 'fornecedores', 'produtos'));
    }

    public function relatorio(Request $request)
    {
        $query = \App\Models\PedidoCompra::with(['fornecedor', 'usuario', 'itens.produto']);

        if ($request->filled('fornecedor_id')) $query->where('fornecedor_id', $request->fornecedor_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('numero_pedido')) $query->where('numero_pedido', 'like', '%'.$request->numero_pedido.'%');
        if ($request->filled('data_inicio')) $query->whereDate('data_pedido', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('data_pedido', '<=', $request->data_fim);
        if ($request->filled('valor_min')) $query->where('valor_total', '>=', $request->valor_min);
        if ($request->filled('valor_max')) $query->where('valor_total', '<=', $request->valor_max);
        if ($request->filled('previsao_inicio')) $query->whereDate('previsao_entrega', '>=', $request->previsao_inicio);
        if ($request->filled('previsao_fim')) $query->whereDate('previsao_entrega', '<=', $request->previsao_fim);
        if ($request->filled('produto_id')) {
            $query->whereHas('itens', fn($q) => $q->where('produto_id', $request->produto_id));
        }

        $pedidos = $query->latest('data_pedido')->get();
        $fornecedores = \App\Models\Fornecedor::where('ativo', true)->orderBy('nome')->get();
        $produtos = \App\Models\Produto::where('ativo', true)->orderBy('nome')->get();

        return view('paginas.pedido_compras.relatorio', compact('pedidos', 'fornecedores', 'produtos'));
    }

    public function estoqueMinimo(Request $request)
    {
        $query = \App\Models\Produto::where('ativo', true)
            ->whereRaw('estoque_atual <= estoque_minimo')
            ->with(['fornecedor', 'cor']);

        if ($request->filled('fornecedor_id')) $query->where('fornecedor_id', $request->fornecedor_id);
        if ($request->filled('cor_id')) $query->where('cor_id', $request->cor_id);

        return response()->json($query->get([
            'id', 'nome', 'sku', 'estoque_minimo', 'estoque_atual',
            'preco_custo', 'fornecedor_id', 'cor_id'
        ]));
    }
}

