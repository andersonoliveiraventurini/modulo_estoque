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
        //
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
    public function edit(Cor $cor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCorRequest $request, Cor $cor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cor $cor)
    {
        //
    }
}
