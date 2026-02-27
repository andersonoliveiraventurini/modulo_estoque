<?php

namespace App\Http\Controllers;

use App\Services\OrcamentoPdfService;

use App\Http\Requests\StoreDescontoRequest;
use App\Http\Requests\UpdateDescontoRequest;
use App\Models\Desconto;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SolicitacaoPagamento;

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

    public function descontosAprovados()
    {
        return view('paginas.descontos.aprovados');
    }

    public function descontosClientes()
    {
        return view('paginas.descontos.clientes');
    }


    private function aplicarAprovacaoNoItem(Desconto $desconto): void
    {
        // Desconto por produto: mantém os valores já salvos no item (não faz nada)
        // Os campos já foram gravados corretamente no store/duplicar
        // Apenas garante que o item reflita os valores com desconto
        if ($desconto->tipo === 'produto' && $desconto->produto_id) {
            $item = OrcamentoItens::where('orcamento_id', $desconto->orcamento_id)
                ->where('produto_id', $desconto->produto_id)
                ->first();

            if ($item) {
                // Recalcula valor_com_desconto = (valor_unitario - desconto_unitario) * quantidade
                $valorUnitarioOriginal    = (float) $item->valor_unitario;
                $quantidade               = (float) $item->quantidade;
                $valorDesconto            = (float) $desconto->valor; // total do desconto
                $valorUnitarioComDesconto = $valorUnitarioOriginal - ($valorDesconto / max($quantidade, 1));
                $valorComDesconto         = $valorUnitarioComDesconto * $quantidade;

                $item->update([
                    'valor_unitario_com_desconto' => round($valorUnitarioComDesconto, 2),
                    'valor_com_desconto'          => round($valorComDesconto, 2),
                    'desconto'                    => round($desconto->valor, 2),
                ]);
            }
        }
        // Desconto percentual/fixo não altera orcamento_itens diretamente
        // pois impacta o total do orçamento, não os itens individualmente
    }

    private function reverterItemAoOriginal(Desconto $desconto): void
    {
        if ($desconto->tipo === 'produto' && $desconto->produto_id) {
            $item = \App\Models\OrcamentoItens::where('orcamento_id', $desconto->orcamento_id)
                ->where('produto_id', $desconto->produto_id)
                ->first();

            if ($item) {
                $valorUnitario = (float) $item->valor_unitario;
                $item->update([
                    'valor_unitario_com_desconto' => $valorUnitario,
                    'valor_com_desconto'          => round($valorUnitario * (float) $item->quantidade, 2),
                    'desconto'                    => null,
                ]);
            }
        }

        if (in_array($desconto->tipo, ['percentual', 'fixo'])) {
            $produtosComDescontoIndividual = Desconto::where('orcamento_id', $desconto->orcamento_id)
                ->where('tipo', 'produto')
                ->whereNull('rejeitado_em')
                ->pluck('produto_id')
                ->filter()
                ->unique()
                ->toArray();

            $itens = \App\Models\OrcamentoItens::where('orcamento_id', $desconto->orcamento_id)
                ->whereNotIn('produto_id', $produtosComDescontoIndividual)
                ->get();

            foreach ($itens as $item) {
                $valorUnitario = (float) $item->valor_unitario;
                $item->update([
                    'valor_unitario_com_desconto' => $valorUnitario,
                    'valor_com_desconto'          => round($valorUnitario * (float) $item->quantidade, 2),
                    'desconto'                    => null,
                ]);
            }
        }
    }

    private function verificarEAtualizarOrcamento($orcamentoId)
    {
        if (!$orcamentoId) {
            return;
        }

        $orcamento = Orcamento::find($orcamentoId);
        if (!$orcamento) {
            return;
        }

        $descontosPendentes = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->count();

        $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNotNull('aprovado_em')
            ->sum('valor');

        $valorOriginal = $orcamento->valor_total_itens ?? 0;
        $valorFinal    = $valorOriginal - $totalDescontosAprovados;

        if ($descontosPendentes > 0) {
            $orcamento->update([
                'desconto_total'     => $totalDescontosAprovados,
                'valor_com_desconto' => $valorFinal,
            ]);
        } else {
            // ✅ Verifica condicao_id antes de definir o status
            $temPagamentoPendente = $orcamento->condicao_id == 20
                && SolicitacaoPagamento::where('orcamento_id', $orcamentoId)
                    ->pendentes()
                    ->exists();

            $novoStatus = $temPagamentoPendente ? 'Aprovar pagamento' : 'Pendente';

            $orcamento->update([
                'status'             => $novoStatus,
                'desconto_total'     => $totalDescontosAprovados,
                'valor_com_desconto' => $valorFinal,
            ]);

            // Gera PDF apenas quando vai para Pendente
            if ($novoStatus === 'Pendente') {
                $orcamento = Orcamento::find($orcamentoId);
                $pdfService = new OrcamentoPdfService();
                $pdfService->gerarOrcamentoPdf($orcamento);
            }
        }
    }

    public function aprovar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $desconto = Desconto::findOrFail($id);

            if ($desconto->aprovado_em) {
                return back()->with('error', 'Este desconto já foi aprovado anteriormente.');
            }

            if ($desconto->rejeitado_em) {
                return back()->with('error', 'Este desconto já foi rejeitado anteriormente.');
            }

            $desconto->update([
                'aprovado_em'             => now(),
                'aprovado_por'            => Auth::id(),
                'justificativa_aprovacao' => $request->justificativa,
            ]);

            // Atualiza o item do orçamento refletindo o desconto aprovado
            $this->aplicarAprovacaoNoItem($desconto);

            $this->verificarEAtualizarOrcamento($desconto->orcamento_id);

            DB::commit();

            return back()->with('success', 'Desconto aprovado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar desconto: ' . $e->getMessage());
        }
    }

    public function rejeitar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $desconto = Desconto::findOrFail($id);

            if ($desconto->aprovado_em) {
                return back()->with('error', 'Este desconto já foi aprovado e não pode ser rejeitado.');
            }

            if ($desconto->rejeitado_em) {
                return back()->with('error', 'Este desconto já foi rejeitado anteriormente.');
            }

            // ✅ 1º reverte o item ANTES de qualquer alteração no desconto
            $this->reverterItemAoOriginal($desconto);

            // ✅ 2º marca como rejeitado
            $desconto->update([
                'rejeitado_em'           => now(),
                'rejeitado_por'          => Auth::id(),
                'justificativa_rejeicao' => $request->justificativa,
            ]);

            // ✅ 3º deleta por último
            $desconto->delete();

            $this->verificarEAtualizarOrcamento($desconto->orcamento_id);

            DB::commit();

            return back()->with('success', 'Desconto rejeitado e removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao rejeitar desconto: ' . $e->getMessage());
        }
    }

    public function avaliar(Request $request, $id)
    {
        $request->validate([
            'acao'          => 'required|in:aprovar,rejeitar',
            'justificativa' => 'nullable|string|max:1000',
        ]);

        if ($request->acao === 'aprovar') {
            return $this->aprovar($request, $id);
        } else {
            return $this->rejeitar($request, $id);
        }
    }

    public function aprovarTodos(Request $request, $orcamentoId)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        $descontos = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->get();

        if ($descontos->isEmpty()) {
            return back()->with('warning', 'Não há descontos pendentes para aprovar.');
        }

        foreach ($descontos as $desconto) {
            $desconto->update([
                'aprovado_em'             => now(),
                'aprovado_por'            => Auth::id(),
                'justificativa_aprovacao' => $request->justificativa ?? 'Aprovação em lote',
            ]);

            $this->aplicarAprovacaoNoItem($desconto);
        }

        $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNotNull('aprovado_em')
            ->sum('valor');

        $orcamento     = Orcamento::findOrFail($orcamentoId);
        $valorOriginal = (float) ($orcamento->valor_total_itens ?? 0);
        $valorFinal    = $valorOriginal - (float) $totalDescontosAprovados;

        $temPagamentoPendente = $orcamento->condicao_id == 20
            && SolicitacaoPagamento::where('orcamento_id', $orcamentoId)
                ->pendentes()
                ->exists();

        $novoStatus = $temPagamentoPendente ? 'Aprovar pagamento' : 'Pendente';

        $orcamento->update([
            'status'             => $novoStatus,
            'desconto_total'     => $totalDescontosAprovados,
            'valor_com_desconto' => $valorFinal,
            'updated_at'         => now(),
        ]);
        
        if ($novoStatus === 'Pendente') {
            try {
                $orcamento = Orcamento::find($orcamentoId);
                $pdfService = new OrcamentoPdfService();
                $pdfService->gerarOrcamentoPdf($orcamento);
            } catch (\Exception $pdfEx) {
                return back()->with('warning', "Descontos aprovados com sucesso, mas ocorreu um erro ao gerar o PDF: " . $pdfEx->getMessage());
            }
        }

        return back()->with('success', "Total de {$descontos->count()} desconto(s) aprovado(s) com sucesso! Status atualizado para: {$novoStatus}");
    }

    public function rejeitarTodos(Request $request, $orcamentoId)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $descontos = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNull('aprovado_em')
                ->whereNull('rejeitado_em')
                ->get();

            if ($descontos->isEmpty()) {
                return back()->with('warning', 'Não há descontos pendentes para rejeitar.');
            }

            foreach ($descontos as $desconto) {
                // ✅ 1º reverte o item ANTES de marcar como rejeitado/deletar
                $this->reverterItemAoOriginal($desconto);

                // ✅ 2º marca como rejeitado
                $desconto->update([
                    'rejeitado_em'           => now(),
                    'rejeitado_por'          => Auth::id(),
                    'justificativa_rejeicao' => $request->justificativa ?? 'Rejeição em lote',
                ]);

                // ✅ 3º deleta por último
                $desconto->delete();
            }

            $this->gerarpdfeAtualizarOrcamento($orcamentoId);

            DB::commit();

            return back()->with('success', "Total de {$descontos->count()} desconto(s) rejeitado(s) com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao rejeitar descontos: ' . $e->getMessage());
        }
    }

    private function gerarpdfeAtualizarOrcamento($orcamentoId)
    {
        $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNotNull('aprovado_em')
            ->sum('valor');

        $orcamento     = Orcamento::find($orcamentoId);
        $valorOriginal = $orcamento->valor_total_itens ?? 0;
        $valorFinal    = $valorOriginal - $totalDescontosAprovados;

        $temDescontosPendentes = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->exists();

        $dadosAtualizacao = [
            'desconto_total'     => $totalDescontosAprovados,
            'valor_com_desconto' => $valorFinal,
        ];

        if ($temDescontosPendentes) {
            $dadosAtualizacao['status'] = 'Aprovar desconto';
        } elseif ($orcamento->condicao_id == 20) {
            // ✅ condicao_id 20 sempre vai para Aprovar pagamento,
            // independente de ter solicitação pendente
            $dadosAtualizacao['status'] = 'Aprovar pagamento';
        } else {
            $dadosAtualizacao['status'] = 'Pendente';
            $pdfService = new OrcamentoPdfService();
            $pdfService->gerarOrcamentoPdf($orcamento);
        }

        $orcamento->update($dadosAtualizacao);
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
