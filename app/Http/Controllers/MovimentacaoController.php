<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimentacaoRequest;
use App\Http\Requests\UpdateMovimentacaoRequest;
use App\Models\Fornecedor;
use App\Models\Movimentacao;
use App\Models\Pedido;

class MovimentacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movimentacao = Movimentacao::all();
        return view('paginas.movimentacao.index', compact('movimentacao'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedores = Fornecedor::all();
        $pedidos = Pedido::all();
        return view('paginas.movimentacao.create', compact('fornecedores', 'pedidos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovimentacaoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Movimentacao $movimentacao)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movimentacao $movimentacao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovimentacaoRequest $request, Movimentacao $movimentacao)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimentacao $movimentacao)
    {
        //
    }
}
