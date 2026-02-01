<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\SolicitacaoPagamento;
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
                return;
            }

            if (!$solicitacao->isPendente()) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Esta solicitação já foi avaliada!'
                ]);
                return;
            }

            $solicitacao->update([
                'aprovado_em' => now(),
                'aprovado_por' => Auth::id(),
                'justificativa_aprovacao' => $this->justificativas[$solicitacaoId] ?? null,
                'status' => 'Aprovado',
            ]);

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Solicitação aprovada com sucesso!'
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
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
                return;
            }

            if (!$solicitacao->isPendente()) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Esta solicitação já foi avaliada!'
                ]);
                return;
            }

            $solicitacao->update([
                'rejeitado_em' => now(),
                'rejeitado_por' => Auth::id(),
                'justificativa_rejeicao' => $this->justificativas[$solicitacaoId],
                'status' => 'Rejeitado',
            ]);

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Solicitação rejeitada!'
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao rejeitar solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.confirmar-solicitacao-pagamento');
    }
}