<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Models\Categoria;
use App\Models\Cor;
use App\Models\Fornecedor;
use App\Models\Imagem;
use App\Models\Produto;
use App\Models\SubCategoria;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::paginate();
        return view('paginas.produtos.index', compact('produtos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedores = Fornecedor::where('status', 'ativo')->get();
        $categorias = Categoria::all();
        $subcategorias = SubCategoria::all();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.produtos.create', compact('fornecedores', 'categorias', 'subcategorias', 'cores'));
    }
    public function createFromItem(\App\Models\ConsultaPreco $consultaPreco)
    {
        $consultaPreco->load([
            'cor',
            'fornecedorSelecionado.fornecedor',
            'grupo.itens',
        ]);

        $forn        = $consultaPreco->fornecedorSelecionado;
        $fornecedor  = $forn?->fornecedor;

        // Monta array com todos os dados disponíveis do item cotado
        // + dados preenchidos no recebimento (entrada_encomenda_itens)
        $itemRecebimento = \App\Models\EntradaEncomendaItem::where('consulta_preco_id', $consultaPreco->id)
            ->whereNotNull('ncm')           // pega o primeiro com dados preenchidos
            ->orWhere('consulta_preco_id', $consultaPreco->id)
            ->orderByDesc('created_at')
            ->first();

        // Flash dos dados para a view de criação de produto
        session()->flash('prefill_produto', [
            // Dados básicos da cotação
            'nome'          => $consultaPreco->descricao,
            'part_number'   => $consultaPreco->part_number,
            'cor_id'        => $consultaPreco->cor_id,
            'fornecedor_id' => $fornecedor?->id,
            'preco_custo'   => $forn?->preco_compra,
            'preco_venda'   => $forn?->preco_venda,
            // Dados do recebimento (se preenchidos)
            'ncm'           => $itemRecebimento?->ncm        ?? $consultaPreco->ncm ?? null,
            'codigo_barras' => $itemRecebimento?->codigo_barras ?? null,
            'sku'           => $itemRecebimento?->sku           ?? null,
            'unidade'       => $itemRecebimento?->unidade_medida ?? null,
            'peso'          => $itemRecebimento?->peso           ?? null,
            'categoria_id'  => $itemRecebimento?->categoria_id  ?? null,
            'subcategoria_id' => $itemRecebimento?->sub_categoria_id ?? null,
            // Referência de origem
            '_origem_consulta_preco_id' => $consultaPreco->id,
            '_origem_grupo_id'          => $consultaPreco->grupo_id,
        ]);

        return redirect()->route('produtos.create');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProdutoRequest $request)
    {
        $produto = Produto::create($request->except('_token'));

        Log::info('Produto criado com sucesso', [
            'user' => auth()->user()->name,
            'produto_id' => $produto->id,
            'sku' => $produto->sku,
            'payload' => $request->except(['images'])
        ]);

        // Processar múltiplas imagens
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('produtos', 'public');

                $produto->images()->create([
                    'caminho' => $path,
                    'principal' => $index === 0, // primeira imagem como principal
                ]);
            }
        }

        return redirect()->route('produtos.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        return view('paginas.produtos.show', compact('produto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $fornecedores = Fornecedor::all();
        $categorias = Categoria::all();
        $subcategorias = SubCategoria::all();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.produtos.edit', compact('produto', 'fornecedores', 'categorias', 'subcategorias', 'cores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $payloadOld = $produto->getOriginal();
        $produto->update($request->validated());

        Log::info('Produto atualizado com sucesso', [
            'user' => auth()->user()->name,
            'produto_id' => $produto->id,
            'sku' => $produto->sku,
            'changes' => $produto->getChanges(),
            'payload_old' => $payloadOld
        ]);

        // Upload de novas imagens
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('produtos', 'public');
                $produto->images()->create([
                    'caminho' => $path,
                    'principal' => false,
                ]);
            }
        }

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Definir imagem principal
    public function definirPrincipal(Produto $produto, Imagem $imagem)
    {
        // Zera as outras
        $produto->images()->update(['principal' => false]);
        $imagem->update(['principal' => true]);

        return back()->with('success', 'Imagem principal atualizada!');
    }

    // Remover imagem
    public function destroyImagem(Produto $produto, Imagem $imagem)
    {
        Storage::disk('public')->delete($imagem->caminho);
        $imagem->delete();

        return back()->with('success', 'Imagem removida!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        
        Log::warning('Produto deletado (SoftDelete)', [
            'user' => auth()->user()->name,
            'produto_id' => $produto->id,
            'sku' => $produto->sku
        ]);

        $produto->delete();

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto deletado com sucesso!');
    }

    public function inativar($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        $produto->update(['status' => 'inativo']);

        Log::info('Produto inativado', [
            'user' => auth()->user()->name,
            'produto_id' => $produto->id
        ]);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto desativado com sucesso!');
    }

    public function ativar($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        $produto->update(['status' => 'ativo']);

        Log::info('Produto ativado', [
            'user' => auth()->user()->name,
            'produto_id' => $produto->id
        ]);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto ativado com sucesso!');
    }
}
