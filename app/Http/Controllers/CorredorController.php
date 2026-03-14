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
        ]);

        Corredor::create($request->all());
        return redirect()->route('corredores.index')->with('success', 'Corredor criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // This method was not provided in the new content, keeping it as is or removing it based on user's intent.
        // For this instruction, I will remove it as it's not in the provided "Code Edit".
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
