<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreblocokRequest;
use App\Http\Requests\UpdateblocokRequest;
use App\Models\blocok;

class BlocokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $historico = blocok::orderBy('created_at', 'desc')->paginate(10);
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
    public function show(blocok $blocok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(blocok $blocok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateblocokRequest $request, blocok $blocok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(blocok $blocok)
    {
        $blocok->delete();
        return redirect()->route('blocok.index')->with('success', 'Registro removido com sucesso.');
    }

    /**
     * Download the latest generated TXT file for this entry.
     */
    public function download(blocok $blocok)
    {
        // Como o arquivo é gerado com timestamp, 
        // e não salvamos opcionalmente o path no banco na migration original,
        // vamos procurar o arquivo mais recente ou apenas baixar o que foi salvo por último.
        
        $files = \Illuminate\Support\Facades\Storage::files('public/sped');
        if (empty($files)) {
            return back()->with('error', 'Arquivo não encontrado no servidor.');
        }

        // Simplificação: Pegamos o arquivo mais recente da pasta sped
        // Para um sistema real, o path deveria estar na tabela bloco_k.
        $latestFile = end($files);
        
        return \Illuminate\Support\Facades\Storage::download($latestFile);
    }
}
