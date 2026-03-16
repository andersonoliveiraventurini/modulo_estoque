<?php

namespace App\Http\Controllers;

use App\Models\Corredor;
use App\Models\Posicao;
use Illuminate\Http\Request;

class PosicaoController extends Controller
{
    public function index()
    {
        $posicoes = Posicao::with('corredor.armazem')->get();
        return view('paginas.posicoes.index', compact('posicoes'));
    }

    public function create()
    {
        $corredores = Corredor::with('armazem')->get();
        return view('paginas.posicoes.create', compact('corredores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'corredor_id' => 'required|exists:corredores,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        Posicao::create($request->all());
        return redirect()->route('posicoes.index')->with('success', 'Posição criada com sucesso.');
    }

    public function edit(Posicao $posicao)
    {
        $corredores = Corredor::with('armazem')->get();
        return view('paginas.posicoes.edit', compact('posicao', 'corredores'));
    }

    public function update(Request $request, Posicao $posicao)
    {
        $request->validate([
            'corredor_id' => 'required|exists:corredores,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $posicao->update($request->all());
        return redirect()->route('posicoes.index')->with('success', 'Posição atualizada com sucesso.');
    }

    public function destroy(Posicao $posicao)
    {
        $posicao->delete();
        return redirect()->route('posicoes.index')->with('success', 'Posição deletada com sucesso.');
    }
}
