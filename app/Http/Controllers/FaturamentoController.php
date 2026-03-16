<?php

namespace App\Http\Controllers;

use App\Models\Fatura;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Services\FaturaService;
use Illuminate\Support\Facades\Log;

class FaturamentoController extends Controller
{
    protected FaturaService $faturaService;

    public function __construct(FaturaService $faturaService)
    {
        $this->faturaService = $faturaService;
    }

    public function index(Request $request)
    {
        return view('faturamento.index');
    }

    /**
     * Relatório de Inadimplência via Tela (poderá abrigar outro LW Component)
     */
    public function inadimplencia()
    {
        return view('faturamento.inadimplencia');
    }

    /**
     * Histórico financeiro de um cliente específico
     */
    public function historicoCliente(Cliente $cliente)
    {
        $faturas = Fatura::where('cliente_id', $cliente->id)
            ->with(['orcamento', 'pedido'])
            ->latest()
            ->get();

        $stats = [
            'total_pago' => $faturas->where('status', 'pago')->sum('valor_total'),
            'total_pendente' => $faturas->whereIn('status', ['pendente', 'parcial'])->sum('valor_total'),
            'total_vencido' => $faturas->where('status', 'vencido')->sum('valor_total'),
        ];

        return view('paginas.faturamento.cliente-historico', compact('cliente', 'faturas', 'stats'));
    }

    public function conferidos()
    {
        $orcamentos = \App\Models\Orcamento::whereNotNull('enviado_financeiro_em')
            ->with(['cliente', 'vendedor'])
            ->latest('enviado_financeiro_em')
            ->get();

        return view('paginas.faturamento.conferidos', compact('orcamentos'));
    }
}
