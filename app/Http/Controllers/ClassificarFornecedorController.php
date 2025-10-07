<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassificarFornecedorRequest;
use App\Http\Requests\UpdateClassificarFornecedorRequest;
use App\Models\ClassificarFornecedor;
use App\Models\Fornecedor;

class ClassificarFornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($fornecedor_id)
    {
        $fornecedor = Fornecedor::find($fornecedor_id);
        return view('paginas.fornecedores.classificacao.create', compact('fornecedor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassificarFornecedorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassificarFornecedor $classificarFornecedor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassificarFornecedor $classificarFornecedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassificarFornecedorRequest $request, ClassificarFornecedor $classificarFornecedor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassificarFornecedor $classificarFornecedor)
    {
        //
    }
}
