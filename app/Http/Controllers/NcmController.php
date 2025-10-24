<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNcmRequest;
use App\Http\Requests\UpdateNcmRequest;
use App\Models\Ncm;

class NcmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.adm.ncm.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('paginas.adm.ncm.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNcmRequest $request)
    {
        Ncm::create($request->validated());

        return redirect()->route('ncm.index')
            ->with('success', 'NCM cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ncm $ncm)
    {
        return view('paginas.adm.ncm.show', compact('ncm'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ncm $ncm)
    {
        return view('paginas.adm.ncm.edit', compact('ncm'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNcmRequest $request, Ncm $ncm)
    {
        $ncm->update($request->validated());

        return redirect()->route('ncm.index')
            ->with('success', 'NCM atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ncm $ncm)
    {
        $ncm->delete();

        return redirect()->route('ncm.index')
            ->with('success', 'NCM excluído com sucesso!');
    }
}
