<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreblocokRequest;
use App\Http\Requests\UpdateblocokRequest;
use App\Models\Blocok;

class BlocokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $historico = Blocok::orderBy('created_at', 'desc')->paginate(10);
        return view('paginas.blocok.index', compact('historico'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreblocokRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Blocok $blocok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blocok $blocok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateblocokRequest $request, Blocok $blocok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blocok $blocok)
    {
        $blocok->delete();
        return redirect()->route('blocok.index')->with('success', 'Registro removido com sucesso.');
    }

    /**
     * Download the specified Bloco K file.
     */
    public function download(Blocok $blocok)
    {
        if (empty($blocok->arquivo_path) || !\Illuminate\Support\Facades\Storage::exists($blocok->arquivo_path)) {
            return back()->with('error', 'Arquivo não encontrado no servidor ou registro incompatível.');
        }

        return \Illuminate\Support\Facades\Storage::download($blocok->arquivo_path);
    }
}
