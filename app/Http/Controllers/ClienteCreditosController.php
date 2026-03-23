<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteCreditosRequest;
use App\Http\Requests\UpdateClienteCreditosRequest;
use App\Models\ClienteCreditos;
use App\Models\Cliente;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteCreditosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request, Cliente $cliente)
    {
        $this->authorize('gerenciarCredito', $cliente);

        $validated = $request->validate([
            'valor'  => 'required|numeric|min:0.01',
            'motivo' => 'required|string|min:5',
            'tipo'   => 'required|in:ajuste,bonificacao,outro',
        ]);

        app(CreditoService::class)->adicionarCredito(
            $cliente,
            $validated['valor'],
            $validated['motivo'],
            null,
            $validated['tipo']
        );

        Log::info('Crédito adicionado manualmente', [
            'cliente_id' => $cliente->id,
            'valor'      => $validated['valor'],
            'tipo'       => $validated['tipo'],
            'user_id'    => auth()->id(),
        ]);

        return back()->with('success', 'Crédito adicionado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteCreditos $clienteCreditos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClienteCreditos $clienteCreditos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteCreditosRequest $request, ClienteCreditos $clienteCreditos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteCreditos $clienteCreditos)
    {
        //
    }
}
