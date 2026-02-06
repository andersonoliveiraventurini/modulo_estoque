<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\SolicitacaoPagamento;
use App\Models\Desconto;
use App\Services\OrcamentoPdfService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfirmarSolicitacaoPagamento extends Component
{
    public $orcamentoId;
    public $orcamento;
    public $solicitacoes = [];
    public $justificativas = [];
    public $showModal = false;

    protected $listeners = ['abrirModalSolicitacoes' => 'abrir'];

    public function mount($orcamentoId = null)
    {
        if ($orcamentoId) {
            $this->orcamentoId = $orcamentoId;
            $this->carregarDados();
        } else {
            Log::warning('orcamentoId não foi passado para o componente');
        }
    }

    public function abrir($orcamentoId)
    {
        $this->orcamentoId = $orcamentoId;
        $this->carregarDados();
        $this->showModal = true;
    }

    public function carregarDados()
    {
        try {
            Log::info('Carregando orçamento para solicitação de pagamento', [
                'orcamento_id' => $this->orcamentoId
            ]);

            if (!$this->orcamentoId) {
                Log::warning('orcamentoId está vazio');
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'ID do orçamento não foi fornecido!'
                ]);
                return;
            }

            // Carrega o orçamento
            $this->orcamento = Orcamento::with([
                'cliente',
                'vendedor',
                'condicaoPagamento'
            ])->find($this->orcamentoId);

            if (!$this->orcamento) {
                Log::error('Orçamento não encontrado', [
                    'orcamento_id' => $this->orcamentoId
                ]);
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Orçamento #' . $this->orcamentoId . ' não encontrado!'
                ]);
                return;
            }

            Log::info('Orçamento carregado com sucesso', [
                'orcamento_id' => $this->orcamento->id,
                'cliente' => $this->orcamento->cliente->nome ?? 'N/A'
            ]);

            // Carrega as solicitações pendentes
            $this->solicitacoes = SolicitacaoPagamento::where('orcamento_id', $this->orcamentoId)
                ->pendentes()
                ->with('solicitante')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Solicitações carregadas', [
                'total' => $this->solicitacoes->count()
            ]);

            $this->justificativas = [];
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados', [
                'orcamento_id' => $this->orcamentoId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    public function aprovarSolicitacao($solicitacaoId)
    {
        if (!$this->orcamento) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Orçamento não encontrado!'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            $solicitacao = SolicitacaoPagamento::findOrFail($solicitacaoId);

            if ($solicitacao->orcamento_id != $this->orcamentoId) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Solicitação não pertence a este orçamento!'
                ]);
                DB::rollBack();
                return;
            }

            if (!$solicitacao->isPendente()) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Esta solicitação já foi avaliada!'
                ]);
                DB::rollBack();
                return;
            }

            // Atualiza a solicitação
            $solicitacao->update([
                'aprovado_em' => now(),
                'aprovado_por' => Auth::id(),
                'justificativa_aprovacao' => $this->justificativas[$solicitacaoId] ?? null,
                'status' => 'Aprovado',
            ]);

            Log::info("Solicitação de pagamento #{$solicitacaoId} aprovada", [
                'orcamento_id' => $this->orcamentoId,
                'aprovado_por' => Auth::id()
            ]);

            // Atualiza o orçamento
            $this->atualizarOrcamentoAposAprovacao();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Solicitação de pagamento aprovada com sucesso!'
            ]);

            // Aguarda 2 segundos e redireciona
            $this->dispatch('redirect', ['url' => route('orcamentos.show', $this->orcamentoId), 'delay' => 2000]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao aprovar solicitação', [
                'solicitacao_id' => $solicitacaoId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function rejeitarSolicitacao($solicitacaoId)
    {
        if (!$this->orcamento) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Orçamento não encontrado!'
            ]);
            return;
        }

        if (empty($this->justificativas[$solicitacaoId])) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'A justificativa é obrigatória para rejeitar!'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            $solicitacao = SolicitacaoPagamento::findOrFail($solicitacaoId);

            if ($solicitacao->orcamento_id != $this->orcamentoId) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Solicitação não pertence a este orçamento!'
                ]);
                DB::rollBack();
                return;
            }

            if (!$solicitacao->isPendente()) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Esta solicitação já foi avaliada!'
                ]);
                DB::rollBack();
                return;
            }

            // Atualiza a solicitação
            $solicitacao->update([
                'rejeitado_em' => now(),
                'rejeitado_por' => Auth::id(),
                'justificativa_rejeicao' => $this->justificativas[$solicitacaoId],
                'status' => 'Rejeitado',
            ]);

            Log::info("Solicitação de pagamento #{$solicitacaoId} rejeitada", [
                'orcamento_id' => $this->orcamentoId,
                'rejeitado_por' => Auth::id()
            ]);

            // Atualiza o orçamento
            $this->atualizarOrcamentoAposRejeicao();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Solicitação de pagamento rejeitada!'
            ]);

            // Aguarda 2 segundos e redireciona
            $this->dispatch('redirect', ['url' => route('orcamentos.show', $this->orcamentoId), 'delay' => 2000]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao rejeitar solicitação', [
                'solicitacao_id' => $solicitacaoId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao rejeitar solicitação: ' . $e->getMessage()
            ]);
        }
    }

    private function atualizarOrcamentoAposAprovacao()
    {
        if (!$this->orcamento) {
            Log::warning('Tentativa de atualizar orçamento nulo após aprovação');
            return;
        }

        // Recarrega o orçamento para ter dados atualizados
        $this->orcamento->refresh();

        // Verifica se ainda existem solicitações de pagamento pendentes
        $solicitacoesPendentes = SolicitacaoPagamento::where('orcamento_id', $this->orcamentoId)
            ->pendentes()
            ->count();

        Log::info('Verificando solicitações pendentes após aprovação', [
            'orcamento_id' => $this->orcamentoId,
            'solicitacoes_pendentes' => $solicitacoesPendentes
        ]);

        // Se ainda houver solicitações pendentes, não faz nada
        if ($solicitacoesPendentes > 0) {
            Log::info("Ainda há {$solicitacoesPendentes} solicitação(ões) de pagamento pendente(s)");
            return;
        }

        // Verifica se há descontos pendentes
        $descontosPendentes = Desconto::where('orcamento_id', $this->orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->count();

        Log::info('Verificando descontos pendentes após aprovação de pagamento', [
            'orcamento_id' => $this->orcamentoId,
            'descontos_pendentes' => $descontosPendentes
        ]);

        if ($descontosPendentes > 0) {
            // Ainda tem descontos pendentes - muda status para aprovar desconto
            $this->orcamento->update([
                'status' => 'Aprovar desconto',
            ]);

            Log::info("Orçamento #{$this->orcamentoId} aguardando aprovação de {$descontosPendentes} desconto(s)", [
                'novo_status' => 'Aprovar desconto'
            ]);
        } else {
            // Não tem mais nada pendente - muda para Pendente e gera PDF
            $this->orcamento->update([
                'status' => 'Pendente',
            ]);

            Log::info("Orçamento #{$this->orcamentoId} aprovado - gerando PDF", [
                'novo_status' => 'Pendente'
            ]);

            // Gera o PDF
            try {
                $pdfService = new OrcamentoPdfService();
                $pdfGerado = $pdfService->gerarOrcamentoPdf($this->orcamento);

                if ($pdfGerado) {
                    Log::info("PDF gerado com sucesso para orçamento #{$this->orcamentoId}");
                } else {
                    Log::warning("Falha ao gerar PDF para orçamento #{$this->orcamentoId}");
                }
            } catch (\Exception $e) {
                Log::error("Erro ao gerar PDF para orçamento #{$this->orcamentoId}", [
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    private function atualizarOrcamentoAposRejeicao()
    {
        if (!$this->orcamento) {
            Log::warning('Tentativa de atualizar orçamento nulo após rejeição');
            return;
        }

        // Recarrega o orçamento
        $this->orcamento->refresh();

        // Limpa os campos de meio de pagamento e volta status para Rejeitado
        $this->orcamento->update([
            'outros_meios_pagamento' => null,
            'condicao_id' => null,
            'status' => 'Rejeitado',
        ]);

        Log::info("Orçamento #{$this->orcamentoId} rejeitado - meio de pagamento removido", [
            'novo_status' => 'Rejeitado',
            'condicao_id' => 'null',
            'outros_meios_pagamento' => 'null'
        ]);
    }

    public function render()
    {
        return view('livewire.confirmar-solicitacao-pagamento');
    }
}