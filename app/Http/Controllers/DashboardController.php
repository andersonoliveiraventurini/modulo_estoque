<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\RequisicaoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'clientes_count' => Cliente::count(),
            'produtos_count' => Produto::count(),
            'vendas_count'   => Venda::count(),
            'estoque_critico' => Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')->count(),
            'requisicoes_pendentes' => RequisicaoCompra::where('status', 'pendente')->count(),
            'vendas_mensal' => Venda::whereMonth('data_venda', now()->month)->count(),
        ];

        // Últimas movimentações críticas
        $alertas = Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')
            ->with(['cor'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'alertas'));
    }
}
