<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendedorRequest;
use App\Http\Requests\UpdateVendedorRequest;
use App\Models\User;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.usuarios.vendedores.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuarios = User::whereDoesntHave('vendedor')->get();
        return view('paginas.usuarios.vendedores.create', compact('usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendedorRequest $request)
    {
        Vendedor::create($request->except([
            '_token'
        ]));

        return redirect()
        ->route('vendedores.index') // 👈 manda para a listagem de clientes
        ->with('success', 'Vendedor cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendedor $vendedor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendedor $vendedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendedorRequest $request, Vendedor $vendedor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendedor $vendedor)
    {
        //
    }
}
