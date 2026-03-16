<?php

namespace App\Http\Controllers;

use App\Models\Armazem;
use App\Models\Corredor;
use Illuminate\Http\Request;

class CorredorController extends Controller
{
    public function index()
    {
        $corredores = Corredor::with('armazem')->withCount('posicoes')->get();
        return view('paginas.corredores.index', compact('corredores'));
    }

    public function create()
    {
        $armazens = Armazem::all();
        return view('paginas.corredores.create', compact('armazens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'armazem_id' => 'required|exists:armazens,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        Corredor::create($request->all());
        return redirect()->route('corredores.index')->with('success', 'Corredor criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Corredor $corredor)
    {
        return view('paginas.corredores.show', compact('corredor'));
    }

    public function edit(Corredor $corredor)
    {
        $armazens = Armazem::all();
        return view('paginas.corredores.edit', compact('corredor', 'armazens'));
    }

    public function update(Request $request, Corredor $corredor)
    {
        $request->validate([
            'armazem_id' => 'required|exists:armazens,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $corredor->update($request->all());
        return redirect()->route('corredores.index')->with('success', 'Corredor atualizado com sucesso.');
    }

    public function destroy(Corredor $corredor)
    {
        $corredor->delete();
        return redirect()->route('corredores.index')->with('success', 'Corredor deletado com sucesso.');
    }
}
