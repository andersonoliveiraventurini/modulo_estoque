<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Http\Requests\UpdateOrcamentoRequest;
use App\Models\Cliente;
use App\Models\Cor;
use App\Models\Fornecedor;
use App\Models\Orcamento;
use App\Models\Produto;

class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.orcamentos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    public function criarOrcamento($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create', compact('produtos', 'cliente', 'fornecedores', 'cores'));

    }
    public function criarOrcamentoTeste($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create_teste', compact('produtos', 'cliente', 'fornecedores', 'cores'));

    }

    public function criarOrcamentoRapido($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create_rapido', compact('produtos', 'cliente', 'fornecedores', 'cores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrcamentoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrcamentoRequest $request, Orcamento $orcamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orcamento $orcamento)
    {
        //
    }
}
