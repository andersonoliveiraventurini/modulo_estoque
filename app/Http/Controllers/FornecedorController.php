<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Contato;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fornecedores = Fornecedor::paginate();
        return view('paginas.fornecedores.index', compact('fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('paginas.fornecedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFornecedorRequest $request)
    {
        //
    }

    public function tabelaPrecos($fornecedor_id)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        return view('paginas.fornecedores.tabela_preco.mostrar', compact('fornecedor'));
    }

    /**
     * Display the specified resource.
     */
    public function show($fornecedor_id)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        $contatos = Contato::where('fornecedor_id', $fornecedor->id)->get();
        return view('paginas.fornecedores.show', compact('fornecedor', 'contatos'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fornecedor $fornecedor)
    {
        //
    }
}
