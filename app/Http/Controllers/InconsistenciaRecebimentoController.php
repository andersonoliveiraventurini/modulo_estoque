<?php

namespace App\Http\Controllers;

use App\Models\InconsistenciaRecebimento;
use Illuminate\Http\Request;

class InconsistenciaRecebimentoController extends Controller
{
    public function index()
    {
        $inconsistencias = InconsistenciaRecebimento::with(['pedidoCompra', 'produto', 'usuario', 'movimentacao'])
            ->latest()
            ->paginate(20);

        return view('paginas.inconsistencias.index', compact('inconsistencias'));
    }

    public function show(InconsistenciaRecebimento $inconsistencia)
    {
        $inconsistencia->load(['pedidoCompra', 'produto', 'usuario', 'movimentacao.itens']);
        return view('paginas.inconsistencias.show', compact('inconsistencia'));
    }

    public function destroy(InconsistenciaRecebimento $inconsistencia)
    {
        $inconsistencia->delete();
        return redirect()->route('inconsistencias.index')->with('success', 'Registro de inconsistência removido.');
    }
}
