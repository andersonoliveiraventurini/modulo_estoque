<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorearmazemRequest;
use App\Http\Requests\UpdatearmazemRequest;
use App\Models\armazem;

class ArmazemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $armazens = armazem::all();
        return view('paginas.armazens.index', compact('armazens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('paginas.armazens.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorearmazemRequest $request)
    {
        armazem::create($request->validated());
        return redirect()->route('armazens.index')->with('success', 'Armazém criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(armazem $armazem)
    {
        return view('paginas.armazens.show', compact('armazem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(armazem $armazem)
    {
        return view('paginas.armazens.edit', compact('armazem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatearmazemRequest $request, armazem $armazem)
    {
        $armazem->update($request->validated());
        return redirect()->route('armazens.index')->with('success', 'Armazém atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(armazem $armazem)
    {
        $armazem->delete();
        return redirect()->route('armazens.index')->with('success', 'Armazém deletado com sucesso.');
    }
}
