<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDescontoRequest;
use App\Http\Requests\UpdateDescontoRequest;
use App\Models\Desconto;

class DescontoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $descontos = Desconto::paginate();
        return view('paginas.descontos.index', compact('descontos'));
    }

     public function avaliar(Request $request, Desconto $desconto)
    {
        $request->validate([
            'acao' => 'required|in:aprovar,rejeitar',
            'justificativa' => 'nullable|string|max:500',
        ]);

        if ($request->acao === 'aprovar') {
            $desconto->update([
                'aprovado_em'            => now(),
                'aprovado_por'           => auth()->id(),
                'justificativa_aprovacao'=> $request->justificativa,

                // garante limpeza
                'rejeitado_em'           => null,
                'rejeitado_por'          => null,
                'justificativa_rejeicao' => null,
            ]);
        }

        if ($request->acao === 'rejeitar') {
            $desconto->update([
                'rejeitado_em'            => now(),
                'rejeitado_por'           => auth()->id(),
                'justificativa_rejeicao'  => $request->justificativa,

                // garante limpeza
                'aprovado_em'             => null,
                'aprovado_por'            => null,
                'justificativa_aprovacao' => null,
            ]);
        }

        // 🔍 verifica se ainda existem descontos pendentes
        $orcamento = $desconto->orcamento;

        $existemPendentes = $orcamento->descontos()
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->exists();

        if (! $existemPendentes) {
            $orcamento->update([
                'status' => 'Descontos Avaliados',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Desconto avaliado com sucesso.');
    }

    public function desconto_orcamento($orcamento_id)
    {        
        return view('paginas.descontos.create', compact('orcamento_id'));
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
    public function store(StoreDescontoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Desconto $desconto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Desconto $desconto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDescontoRequest $request, Desconto $desconto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Desconto $desconto)
    {
        //
    }
}
