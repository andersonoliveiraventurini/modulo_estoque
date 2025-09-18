<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorRequest;
use App\Http\Requests\UpdateCorRequest;
use App\Models\Cor;

class CorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.adm.cores.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('paginas.adm.cores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorRequest $request)
    {
        Cor::create($request->validated());

        return redirect()->route('cores.index')->with('success', 'Cor criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cor $cor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($cor_id)
    {
        $cor = Cor::findOrFail($cor_id);
        return view('paginas.adm.cores.edit', compact('cor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCorRequest $request, $cor_id)
    {
        $cor = Cor::findOrFail($cor_id);
        $cor->update($request->validated());

        return redirect()->route('cores.index')->with('success', 'Cor atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($cor_id)
    {
        $cor = Cor::findOrFail($cor_id);
        $cor->delete();

        return redirect()->route('cores.index')->with('success', 'Cor deletada com sucesso.');
    }
}
