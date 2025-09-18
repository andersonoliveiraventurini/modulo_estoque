<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubCategoriaRequest;
use App\Http\Requests\UpdateSubCategoriaRequest;
use App\Models\Categoria;
use App\Models\SubCategoria;

class SubCategoriaController extends Controller
{

    public function subcategorias($id)
    {
        $subcategorias = SubCategoria::where('categoria_id', $id)->get();

        return response()->json($subcategorias);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.adm.subcategorias.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        return view('paginas.adm.subcategorias.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubCategoriaRequest $request)
    {
        SubCategoria::create($request->validated());

        return redirect()
            ->route('subcategorias.index')
            ->with('success', 'Subcategoria criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategoria $subCategoria)
    {
        return view('paginas.adm.subcategorias.show', compact('subCategoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($subcategoria_id)
    {
        $subcategoria = SubCategoria::findOrFail($subcategoria_id);
        $categorias = Categoria::all();
        return view('paginas.adm.subcategorias.edit', compact('subcategoria', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubCategoriaRequest $request, $subCategoria_id)
    {
        $subCategoria = SubCategoria::findOrFail($subCategoria_id);
        $subCategoria->update($request->validated());

        return redirect()
            ->route('subcategorias.index')
            ->with('success', 'Subcategoria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($subCategoria_id)
    {
        $subCategoria = SubCategoria::findOrFail($subCategoria_id);
        $subCategoria->delete();

        return redirect()
            ->route('subcategorias.index')
            ->with('success', 'Subcategoria deletada com sucesso!');
    }
}
