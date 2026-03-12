<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendedorRequest;
use App\Http\Requests\UpdateVendedorRequest;
use App\Models\User;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    public function index()
    {
        $vendedores = Vendedor::with('user')->get();
        return view('paginas.usuarios.vendedores.index', compact('vendedores'));
    }

    public function create()
    {
        $usuarios = User::whereDoesntHave('vendedor')->get();
        return view('paginas.usuarios.vendedores.create', compact('usuarios'));
    }

    public function store(StoreVendedorRequest $request)
    {
        Vendedor::create($request->except('_token'));

        return redirect()
            ->route('vendedores.index')
            ->with('success', 'Vendedor cadastrado com sucesso!');
    }

    public function show(Vendedor $vendedor)
    {
        $vendedor->load('user');
        return view('paginas.usuarios.vendedores.show', compact('vendedor'));
    }

   public function edit(Vendedor $vendedor)
{
    $vendedor->load('user');
    return view('paginas.usuarios.vendedores.edit', compact('vendedor'));
}

    public function update(UpdateVendedorRequest $request, Vendedor $vendedor)
    {
        $vendedor->update($request->only(['externo', 'desconto']));

        return redirect()
            ->route('vendedores.index')
            ->with('success', 'Vendedor atualizado com sucesso!');
    }

    public function destroy(Vendedor $vendedor)
    {
        $vendedor->delete();

        return redirect()
            ->route('vendedores.index')
            ->with('success', 'Vendedor removido com sucesso!');
    }
}