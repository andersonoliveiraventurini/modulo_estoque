<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnaliseCreditoRequest;
use App\Http\Requests\UpdateAnaliseCreditoRequest;
use App\Models\AnaliseCredito;
use App\Models\Cliente;

class AnaliseCreditoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $analises = AnaliseCredito::all();
        return view('paginas.analise_credito.index', compact('analises'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.analise_credito.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnaliseCreditoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AnaliseCredito $analiseCredito)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnaliseCredito $analiseCredito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnaliseCreditoRequest $request, AnaliseCredito $analiseCredito)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnaliseCredito $analiseCredito)
    {
        //
    }
}
