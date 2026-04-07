<?php

namespace App\Http\Controllers;

use App\Models\CurvaVendaConfig;
use App\Models\CurvaVendaAuditoria;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\OrcamentoItens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CurvaVendaController extends Controller
{
    public function index()
    {
        $config = CurvaVendaConfig::latest()->first();
        $produtos = Produto::whereNotNull('classificacao_curva')
            ->orderBy('classificacao_curva')
            ->paginate(50);

        return view('curva_vendas.index', compact('config', 'produtos'));
    }

    public function processar(Request $request)
    {
        $request->validate([
            'periodo_inicio' => 'required|date|before_or_equal:today',
            'periodo_fim' => 'required|date|before_or_equal:today|after_or_equal:periodo_inicio',
            'parametros' => 'required|array|min:1|max:3',
            'parametros.*.criterio' => 'required|string',
            'parametros.*.classes' => 'required|array',
        ]);

        // Save configuration
        $config = CurvaVendaConfig::create([
            'periodo_inicio' => $request->periodo_inicio,
            'periodo_fim' => $request->periodo_fim,
            'parametros' => $request->parametros,
        ]);

        // Process products
        $inicio = $request->periodo_inicio;
        $fim = $request->periodo_fim;

        // Validation: Overlapping ranges
        foreach ($request->parametros as $param) {
            $classes = $param['classes'];
            $ranges = [];
            foreach ($classes as $classe => $faixa) {
                foreach ($ranges as $r) {
                    if (($faixa['min'] >= $r['min'] && $faixa['min'] <= $r['max']) ||
                        ($faixa['max'] >= $r['min'] && $faixa['max'] <= $r['max'])) {
                        return redirect()->back()->withErrors(['parametros' => "As faixas de valores para a classe $classe se sobrepõem a outra classe."])->withInput();
                    }
                }
                $ranges[] = ['min' => $faixa['min'], 'max' => $faixa['max']];
            }
        }

        // Get sales data per product
        $vendas = DB::table('orcamento_itens')
            ->join('orcamentos', 'orcamentos.id', '=', 'orcamento_itens.orcamento_id')
            ->join('vendas', 'vendas.orcamento_id', '=', 'orcamentos.id')
            ->whereBetween('vendas.data_venda', [$inicio, $fim])
            ->select(
                'orcamento_itens.produto_id',
                DB::raw('SUM(orcamento_itens.quantidade) as total_quantidade'),
                DB::raw('SUM(orcamento_itens.quantidade * orcamento_itens.valor_unitario) as total_valor')
            )
            ->groupBy('orcamento_itens.produto_id')
            ->get()
            ->keyBy('produto_id');

        Produto::where('classificacao_manual', false)->chunk(500, function ($produtos) use ($vendas, $request) {
            foreach ($produtos as $produto) {
                $stats = $vendas->get($produto->id);
                $classificacao = 'D'; // Default

                if ($stats) {
                    foreach ($request->parametros as $param) {
                        $criterio = $param['criterio'];
                        $valorParaComparar = ($criterio == 'quantidade') ? $stats->total_quantidade : $stats->total_valor;

                        foreach ($param['classes'] as $classe => $faixa) {
                            if ($valorParaComparar >= $faixa['min'] && $valorParaComparar <= $faixa['max']) {
                                $classificacao = $classe;
                                break 2;
                            }
                        }
                    }
                }

                $produto->update(['classificacao_curva' => $classificacao]);
            }
        });

        return redirect()->route('curva_vendas.index')->with('success', 'Curva de vendas processada com sucesso.');
    }

    public function reclassificar(Request $request, Produto $produto)
    {
        $request->validate([
            'nova_classificacao' => 'required|string|max:2',
            'justificativa' => 'required|string|min:5',
        ]);

        $de = $produto->classificacao_curva;
        
        $produto->update([
            'classificacao_curva' => $request->nova_classificacao,
            'classificacao_manual' => true,
            'justificativa_manual' => $request->justificativa,
        ]);

        CurvaVendaAuditoria::create([
            'produto_id' => $produto->id,
            'user_id' => Auth::id(),
            'de' => $de,
            'para' => $request->nova_classificacao,
            'justificativa' => $request->justificativa,
        ]);

        return redirect()->back()->with('success', 'Produto reclassificado com sucesso.');
    }

    public function auditoria()
    {
        $auditorias = CurvaVendaAuditoria::with(['produto', 'user'])
            ->latest()
            ->paginate(50);

        return view('curva_vendas.auditoria', compact('auditorias'));
    }
}
