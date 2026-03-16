<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBloqueioRequest;
use App\Http\Requests\UpdateBloqueioRequest;
use App\Models\Bloqueio;
use App\Models\Cliente;

class BloqueioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bloqueios = Bloqueio::all();
        return view('paginas.clientes.bloqueios.index', compact('bloqueios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'bloqueado' => 'required|boolean',
            'motivo_bloqueio' => 'required_if:bloqueado,1|nullable|string|max:1000',
        ]);

        $cliente = Cliente::findOrFail($request->cliente_id);

        if ($request->bloqueado) {
            Bloqueio::create([
                'cliente_id' => $cliente->id,
                'motivo' => $request->motivo_bloqueio,
                'user_id' => auth()->id(),
            ]);

            $cliente->update(['bloqueado' => true]);
            $mensagem = 'Cliente bloqueado com sucesso.';
        } else {
            // Se enviar 0, significa que o usuário quer garantir o desbloqueio
            $bloqueioAtivo = $cliente->ultimoBloqueio;
            if ($bloqueioAtivo && is_null($bloqueioAtivo->desbloqueado_por_id)) {
                $bloqueioAtivo->update(['desbloqueado_por_id' => auth()->id()]);
                $bloqueioAtivo->delete();
            }
            $cliente->update(['bloqueado' => false]);
            $mensagem = 'Cliente mantido/definido como desbloqueado.';
        }

        return redirect()->route('bloqueios.mostrar', $cliente->id)
            ->with('success', $mensagem);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bloqueio $bloqueio)
    {
        //
    }

    public function bloquear($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.create', compact('cliente'));
    }

    public function bloqueios($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        return view('paginas.clientes.bloqueios.mostrar', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bloqueio $bloqueio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBloqueioRequest $request, Bloqueio $bloqueio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bloqueio $bloqueio)
    {
        $bloqueio->update(['desbloqueado_por_id' => auth()->id()]);
        $bloqueio->delete();

        $cliente = $bloqueio->cliente;
        $bloqueiosAtivos = $cliente->bloqueios()->whereNull('desbloqueado_por_id')->count();

        if ($bloqueiosAtivos === 0) {
            $cliente->update(['bloqueado' => false]);
        }

        return redirect()->back()->with('success', 'Bloqueio desfeito com sucesso.');
    }
}
