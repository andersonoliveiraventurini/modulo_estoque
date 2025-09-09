<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBloqueioRequest;
use App\Http\Requests\UpdateBloqueioRequest;
use App\Models\Bloqueio;
use App\Models\Cliente;

class BloqueioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bloqueios = Bloqueio::all();
        return view('paginas.clientes.bloqueios.index', compact('bloqueios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBloqueioRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bloqueio $bloqueio)
    {
        //
    }

    public function bloquear($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.create', compact('cliente'));
    }

    public function bloqueios($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.mostrar', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bloqueio $bloqueio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBloqueioRequest $request, Bloqueio $bloqueio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bloqueio $bloqueio)
    {
        //
    }
}
