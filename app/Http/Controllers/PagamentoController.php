<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagamentoRequest;
use App\Http\Requests\UpdatePagamentoRequest;
use App\Models\CondicoesPagamento;
use App\Models\Orcamento;
use App\Models\Pagamento;

class PagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function create() {}

    /**
     * Show the form for creating a new resource.
     */
    public function realizar_pagamento($orcamento_id)
    {
        $orcamento = Orcamento::findOrFail($orcamento_id);
        $condicoesPagamento = CondicoesPagamento::all();
        return view('paginas.pagamentos.create', compact('orcamento', 'condicoesPagamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePagamentoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pagamento $pagamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pagamento $pagamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePagamentoRequest $request, Pagamento $pagamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pagamento $pagamento)
    {
        //
    }
}
