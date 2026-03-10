<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitacaoPagamentoRequest;
use App\Http\Requests\UpdateSolicitacaoPagamentoRequest;
use App\Models\SolicitacaoPagamento;

use App\Models\Orcamento;
use App\Models\Desconto;
use App\Services\OrcamentoPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SolicitacaoPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solicitacoes = SolicitacaoPagamento::with(['orcamento', 'solicitante'])
            ->pendentes()
            ->latest()
            ->paginate(15);

        return view('paginas.pagamentos.solicitacoes-pagamento', compact('solicitacoes'));
    }

    /**
     * Solicitações aprovadas
     */
    public function aprovadas()
    {
        return view('paginas.pagamentos.solicitacoes-pagamento.aprovadas');
    }

    /**
     * Tela de aprovação de uma solicitação específica
     */
    public function solicitacao_orcamento($orcamento_id)
    {
        return view('paginas.pagamentos.solicitacoes-pagamento', compact('orcamento_id'));
    }

    /**
     * Aprova uma solicitação de pagamento
     */
    public function aprovar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $solicitacao = SolicitacaoPagamento::findOrFail($id);

            // Verifica se já foi aprovada ou rejeitada
            if ($solicitacao->aprovado_em) {
                return back()->with('error', 'Esta solicitação já foi aprovada anteriormente.');
            }

            if ($solicitacao->rejeitado_em) {
                return back()->with('error', 'Esta solicitação já foi rejeitada anteriormente.');
            }

            // Atualiza a solicitação
            $solicitacao->update([
                'aprovado_em' => now(),
                'aprovado_por' => Auth::id(),
                'justificativa_aprovacao' => $request->justificativa,
                'status' => 'Aprovado',
            ]);

            // Atualiza o orçamento
            $this->atualizarOrcamentoAposAprovacao($solicitacao->orcamento_id);

            DB::commit();

            Log::info("Solicitação de pagamento #{$id} aprovada", [
                'solicitacao_id' => $id,
                'orcamento_id' => $solicitacao->orcamento_id,
                'aprovado_por' => Auth::id(),
            ]);

            return back()->with('success', 'Solicitação de pagamento aprovada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao aprovar solicitação de pagamento', [
                'solicitacao_id' => $id,
                'erro' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao aprovar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita uma solicitação de pagamento
     */
    public function rejeitar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'required|string|max:1000',
        ], [
            'justificativa.required' => 'A justificativa é obrigatória para rejeitar uma solicitação.',
        ]);

        try {
            DB::beginTransaction();

            $solicitacao = SolicitacaoPagamento::findOrFail($id);

            // Verifica se já foi aprovada ou rejeitada
            if ($solicitacao->aprovado_em) {
                return back()->with('error', 'Esta solicitação já foi aprovada e não pode ser rejeitada.');
            }

            if ($solicitacao->rejeitado_em) {
                return back()->with('error', 'Esta solicitação já foi rejeitada anteriormente.');
            }

            // Atualiza a solicitação
            $solicitacao->update([
                'rejeitado_em' => now(),
                'rejeitado_por' => Auth::id(),
                'justificativa_rejeicao' => $request->justificativa,
                'status' => 'Rejeitado',
            ]);

            // Atualiza o orçamento para voltar ao status anterior
            $this->atualizarOrcamentoAposRejeicao($solicitacao->orcamento_id);

            DB::commit();

            Log::info("Solicitação de pagamento #{$id} rejeitada", [
                'solicitacao_id' => $id,
                'orcamento_id' => $solicitacao->orcamento_id,
                'rejeitado_por' => Auth::id(),
            ]);

            return back()->with('success', 'Solicitação de pagamento rejeitada!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao rejeitar solicitação de pagamento', [
                'solicitacao_id' => $id,
                'erro' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao rejeitar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Avalia uma solicitação (aprovar ou rejeitar) - rota única
     */
    public function avaliar(Request $request, $id)
    {
        $request->validate([
            'acao' => 'required|in:aprovar,rejeitar',
            'justificativa' => 'nullable|string|max:1000',
        ]);

        if ($request->acao === 'aprovar') {
            return $this->aprovar($request, $id);
        } else {
            return $this->rejeitar($request, $id);
        }
    }

    /**
     * Atualiza o orçamento após aprovação da solicitação
     */
    private function atualizarOrcamentoAposAprovacao($orcamentoId)
    {
        $orcamento = Orcamento::with(['itens', 'itens.produto'])->find($orcamentoId);

        if (!$orcamento) {
            return;
        }

        // Verifica se ainda existem solicitações pendentes
        $solicitacoesPendentes = SolicitacaoPagamento::where('orcamento_id', $orcamentoId)
            ->pendentes()
            ->count();

        if ($solicitacoesPendentes > 0) {
            Log::info("Pagamento aprovado, mas ainda há {$solicitacoesPendentes} solicitações pendentes no orçamento #{$orcamentoId}");
            return;
        }

        // Verifica se ainda há descontos pendentes
        $descontosPendentes = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->count();

        if ($descontosPendentes > 0) {
            $orcamento->update(['status' => 'Aprovar desconto']);
            Log::info("Pagamento aprovado, mas ainda há descontos pendentes no orçamento #{$orcamentoId}");
            return;
        }

        // Verifica estoque
        $temItensSemEstoque = false;
        foreach ($orcamento->itens as $item) {
            $produto = $item->produto;
            if ($produto && $produto->estoque_atual !== null) {
                if ((float) $item->quantidade > (float) $produto->estoque_atual) {
                    $temItensSemEstoque = true;
                    break;
                }
            }
        }

        if ($temItensSemEstoque) {
            $orcamento->update(['status' => 'Sem estoque']);
            Log::info("Pagamento aprovado, mas orçamento #{$orcamentoId} ficou como 'Sem estoque'");
            return;
        }

        // ── STATUS: Pago para encomenda, Pendente para orçamento normal ────
        $novoStatus = $orcamento->encomenda !== null ? 'Pago' : 'Pendente';
        $orcamento->update(['status' => $novoStatus]);

        // Gera o PDF
        $pdfService = new OrcamentoPdfService();
        $pdfService->gerarOrcamentoPdf($orcamento->fresh());

        Log::info("Pagamento aprovado para orçamento #{$orcamentoId} → status: {$novoStatus}");

        // ── ENCOMENDA: inicia separação agora que está pago ─────────────────
        if ($orcamento->encomenda !== null) {
            $this->iniciarSeparacaoEncomenda($orcamento->fresh());
        }
    }

    private function iniciarSeparacaoEncomenda(Orcamento $orcamento): void
    {
        try {
            DB::transaction(function () use ($orcamento) {

                // Evita lote duplicado
                $existe = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                    ->whereIn('status', ['aberto', 'em_separacao'])
                    ->exists();

                if ($existe) {
                    Log::warning("Tentativa de criar PickingBatch duplicado para encomenda #{$orcamento->id}");
                    return;
                }

                $batch = \App\Models\PickingBatch::create([
                    'orcamento_id'  => $orcamento->id,
                    'status'        => 'em_separacao',
                    'started_at'    => now(),
                    'criado_por_id' => Auth::id(),
                ]);

                // ── Produtos físicos ─────────────────────────────────────
                $itensProduto = $orcamento->itens->whereNotNull('produto_id');
                foreach ($itensProduto as $oi) {
                    \App\Models\PickingItem::create([
                        'picking_batch_id'  => $batch->id,
                        'orcamento_item_id' => $oi->id,
                        'produto_id'        => $oi->produto_id,
                        'is_encomenda'      => false,
                        'qty_solicitada'    => $oi->quantidade,
                        'qty_separada'      => 0,
                        'status'            => 'pendente',
                    ]);
                }

                // ── Itens de consulta de preço (encomenda) ───────────────
                $grupo = \App\Models\ConsultaPrecoGrupo::with('itens')
                    ->where('orcamento_id', $orcamento->id)
                    ->first();

                if ($grupo) {
                    foreach ($grupo->itens as $item) {
                        \App\Models\PickingItem::create([
                            'picking_batch_id'    => $batch->id,
                            'orcamento_item_id'   => null,
                            'produto_id'          => null,
                            'consulta_preco_id'   => $item->id,
                            'is_encomenda'        => true,
                            'descricao_encomenda' => $item->descricao,
                            'qty_solicitada'      => $item->quantidade,
                            'qty_separada'        => 0,
                            'status'              => 'pendente',
                        ]);
                    }
                }

                // ── Reservas de estoque (só produtos físicos) ────────────
                if ($itensProduto->isNotEmpty()) {
                    app(\App\Services\EstoqueService::class)->reservarParaOrcamento($orcamento);
                }

                $orcamento->update(['workflow_status' => 'em_separacao']);

                Log::info("PickingBatch criado para encomenda #{$orcamento->id} após pagamento confirmado", [
                    'picking_batch_id' => $batch->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar separação da encomenda #{$orcamento->id}: " . $e->getMessage());
            // Não relança a exceção — o pagamento já foi aprovado, separação pode ser iniciada manualmente
        }
    }

    /**
     * Atualiza o orçamento após rejeição da solicitação
     */
    private function atualizarOrcamentoAposRejeicao($orcamentoId)
    {
        $orcamento = Orcamento::find($orcamentoId);

        if (!$orcamento) {
            return;
        }

        // Limpa o campo outros_meios_pagamento
        $orcamento->update([
            'outros_meios_pagamento' => null,
            'condicao_id' => null, // Volta para null para o vendedor escolher outra condição
            'status' => 'Pendente', // Ou outro status que faça sentido
        ]);

        Log::info("Solicitação de pagamento rejeitada para orçamento #{$orcamentoId} - meio de pagamento resetado");
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
    public function store(StoreSolicitacaoPagamentoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SolicitacaoPagamento $solicitacaoPagamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SolicitacaoPagamento $solicitacaoPagamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSolicitacaoPagamentoRequest $request, SolicitacaoPagamento $solicitacaoPagamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SolicitacaoPagamento $solicitacaoPagamento)
    {
        //
    }
}
