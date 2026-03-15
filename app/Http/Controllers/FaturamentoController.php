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

    /**
     * Listagem geral de faturas (Contas a Receber)
     */
    public function index(Request $request)
    {
        $query = Fatura::with(['cliente', 'orcamento', 'pedido']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $faturas = $query->latest('data_vencimento')->paginate(20);

        return view('paginas.faturamento.index', compact('faturas'));
    }

    /**
     * Relatório de Inadimplência
     */
    public function inadimplencia()
    {
        $this->faturaService->verificarInadimplencia();

        $faturas = Fatura::with('cliente')
            ->where('status', 'vencido')
            ->latest('data_vencimento')
            ->get();

        $totalVencido = $faturas->sum('valor_total');

        return view('paginas.faturamento.inadimplencia', compact('faturas', 'totalVencido'));
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
}
