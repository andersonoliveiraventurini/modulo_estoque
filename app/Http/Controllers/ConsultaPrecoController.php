<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaPrecoRequest;
use App\Http\Requests\UpdateConsultaPrecoRequest;
use App\Models\ConsultaPreco;
use App\Models\Fornecedor;

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
        return view('paginas.produtos.consulta_precos.create', compact('fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultaPrecoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultaPreco $consultaPreco)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsultaPreco $consultaPreco)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultaPrecoRequest $request, ConsultaPreco $consultaPreco)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsultaPreco $consultaPreco)
    {
        //
    }
}
