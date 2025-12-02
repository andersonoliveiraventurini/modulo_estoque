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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProdutoRequest $request)
    {
        $produto = Produto::create($request->except('_token'));

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
        $produto->update($request->validated());

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
        $produto->delete();

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto deletado com sucesso!');
    }

    public function inativar($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        $produto->update(['status' => 'inativo']); 

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto desativado com sucesso!');
    }

    public function ativar($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        $produto->update(['status' => 'ativo']); 

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto ativado com sucesso!');
    }
}
