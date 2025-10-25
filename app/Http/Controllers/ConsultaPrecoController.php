<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaPrecoRequest;
use App\Http\Requests\UpdateConsultaPrecoRequest;
use App\Models\ConsultaPreco;
use App\Models\Cor;
use App\Models\Fornecedor;
use App\Models\Orcamento;

class ConsultaPrecoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $precos = ConsultaPreco::all();
        return view('paginas.produtos.consulta_precos.index', compact('precos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedores = Fornecedor::all();
        $cores = Cor::orderBy('nome')->get();
        $orcamentos = Orcamento::where('status', '<>', 'Aprovado')->where('status', '<>', 'Cancelado')->get();
        return view('paginas.produtos.consulta_precos.create', compact('fornecedores', 'cores', 'orcamentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultaPrecoRequest $request)
    {
        $request->merge(['usuario_id' => auth()->id()]);
        $consultaPreco = ConsultaPreco::create($request->except('_token'));
        return redirect()->route('consulta_preco.show', $consultaPreco)->with('success', 'Consulta de Preço criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultaPreco $consultaPreco)
    {
        return view('paginas.produtos.consulta_precos.show', compact('consultaPreco'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($consult_id)
    {
        $consulta = ConsultaPreco::findOrFail($consult_id);

        $fornecedores = Fornecedor::all();
        $cores = Cor::orderBy('nome')->get();
        $orcamentos = Orcamento::where('status', '<>', 'Aprovado')->where('status', '<>', 'Cancelado')->get();
        return view('paginas.produtos.consulta_precos.edit', compact('consulta', 'fornecedores', 'cores', 'orcamentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultaPrecoRequest $request, $consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);
        $consultaPreco->update($request->except('_token', '_method'));
        return redirect()->route('consulta_preco.show', $consultaPreco)->with('success', 'Consulta de Preço atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);
        $consultaPreco->delete();
        return redirect()->route('consulta_preco.index')->with('success', 'Consulta de Preço excluída com sucesso.');
    }
}
