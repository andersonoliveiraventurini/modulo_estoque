<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Contato;
use App\Models\Vendedor;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::paginate();
        return view('paginas.clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendedores = Vendedor::all();
        return view('paginas.clientes.create', compact('vendedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_completo()
    {
        $vendedores = Vendedor::all();
        return view('paginas.clientes.create_completo', compact('vendedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $contatos = Contato::where('cliente_id', $cliente->id)->get();
        return view('paginas.clientes.show', compact('cliente', 'contatos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $contatos = Contato::where('cliente_id', $cliente->id)->get();
        $vendedores = Vendedor::all();
        return view('paginas.clientes.edit', compact('cliente', 'contatos', 'vendedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
