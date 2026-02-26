<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\Desconto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfirmarDescontos extends Component
{
    public $orcamentoId;
    public $orcamento;
    public $descontos = [];
    public $justificativas = [];
    public $showModal = false;

    protected $listeners = ['abrirModalDescontos' => 'abrir'];

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
            // Log para debug
            Log::info('Carregando orçamento', ['orcamento_id' => $this->orcamentoId]);

            if (!$this->orcamentoId) {
                Log::warning('orcamentoId está vazio');
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'ID do orçamento não foi fornecido!'
                ]);
                return;
            }

            // Carrega o orçamento com relacionamentos
            $this->orcamento = Orcamento::with([
                'cliente',
                'vendedor',
                'condicaoPagamento'
            ])->find($this->orcamentoId);

            // Verificar se o orçamento foi encontrado
            if (!$this->orcamento) {
                Log::error('Orçamento não encontrado', ['orcamento_id' => $this->orcamentoId]);
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Orçamento #' . $this->orcamentoId . ' não encontrado no banco de dados!'
                ]);
                return;
            }

            Log::info('Orçamento carregado com sucesso', [
                'orcamento_id' => $this->orcamento->id,
                'cliente' => $this->orcamento->cliente->nome ?? 'N/A'
            ]);

            // Carrega os descontos pendentes de aprovação
            $this->descontos = Desconto::where('orcamento_id', $this->orcamentoId)
                ->whereNull('aprovado_em')
                ->whereNull('rejeitado_em')
                ->with('user', 'produto') 
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Descontos carregados', ['total' => $this->descontos->count()]);

            // Inicializa o array de justificativas
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

    public function aprovarDesconto($descontoId)
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

            $desconto = Desconto::findOrFail($descontoId);

            if ($desconto->orcamento_id != $this->orcamentoId) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Desconto não pertence a este orçamento!'
                ]);
                return;
            }

            if ($desconto->aprovado_em || $desconto->rejeitado_em) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Este desconto já foi avaliado!'
                ]);
                return;
            }

            $desconto->update([
                'aprovado_em' => now(),
                'aprovado_por' => Auth::id(),
                'justificativa_aprovacao' => $this->justificativas[$descontoId] ?? null,
            ]);

            $this->atualizarValorOrcamento();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Desconto aprovado com sucesso!'
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao aprovar desconto: ' . $e->getMessage()
            ]);
        }
    }

    public function rejeitarDesconto($descontoId)
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

            $desconto = Desconto::findOrFail($descontoId);

            if ($desconto->orcamento_id != $this->orcamentoId) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Desconto não pertence a este orçamento!'
                ]);
                return;
            }

            if ($desconto->aprovado_em || $desconto->rejeitado_em) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Este desconto já foi avaliado!'
                ]);
                return;
            }

            $desconto->update([
                'rejeitado_em' => now(),
                'rejeitado_por' => Auth::id(),
                'justificativa_rejeicao' => $this->justificativas[$descontoId] ?? null,
            ]);

            $this->atualizarValorOrcamento();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Desconto rejeitado!'
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao rejeitar desconto: ' . $e->getMessage()
            ]);
        }
    }

    public function aprovarTodos()
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

            $totalAprovados = 0;

            foreach ($this->descontos as $desconto) {
                if ($desconto->aprovado_em || $desconto->rejeitado_em) {
                    continue;
                }

                $desconto->update([
                    'aprovado_em' => now(),
                    'aprovado_por' => Auth::id(),
                    'justificativa_aprovacao' => 'Aprovação em lote',
                ]);

                $totalAprovados++;
            }

            $this->atualizarValorOrcamento();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => "{$totalAprovados} desconto(s) aprovado(s) com sucesso!"
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao aprovar descontos: ' . $e->getMessage()
            ]);
        }
    }

    public function rejeitarTodos()
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

            $totalRejeitados = 0;

            foreach ($this->descontos as $desconto) {
                if ($desconto->aprovado_em || $desconto->rejeitado_em) {
                    continue;
                }

                $desconto->update([
                    'rejeitado_em' => now(),
                    'rejeitado_por' => Auth::id(),
                    'justificativa_rejeicao' => 'Rejeição em lote',
                ]);

                $totalRejeitados++;
            }
            $this->atualizarValorOrcamento();

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => "{$totalRejeitados} desconto(s) rejeitado(s)!"
            ]);

            $this->carregarDados();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Erro ao rejeitar descontos: ' . $e->getMessage()
            ]);
        }
    }

    public function finalizarAnalise()
    {
        if (!$this->orcamento) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Orçamento não encontrado!'
            ]);
            return;
        }

        $pendentes = collect($this->descontos)->filter(function ($desconto) {
            return !$desconto->aprovado_em && !$desconto->rejeitado_em;
        });

        if ($pendentes->count() > 0) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Ainda existem ' . $pendentes->count() . ' desconto(s) pendente(s) de análise!'
            ]);
            return;
        }

        $this->showModal = false;

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Análise de descontos finalizada com sucesso!'
        ]);

        $this->dispatch('descontosAnalisados', ['orcamentoId' => $this->orcamentoId]);
    }

    private function atualizarValorOrcamento()
    {
        if (!$this->orcamento) {
            return;
        }

        $totalDescontosAprovados = Desconto::where('orcamento_id', $this->orcamentoId)
            ->whereNotNull('aprovado_em')
            ->sum('valor');

        $temDescontosPendentes = Desconto::where('orcamento_id', $this->orcamentoId)
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->exists();

        $dadosAtualizacao = [
            'desconto_total'     => $totalDescontosAprovados,
            'valor_com_desconto' => $this->orcamento->valor_total_itens - $totalDescontosAprovados,
        ];

        if (!$temDescontosPendentes) {
            $novoStatus = $this->orcamento->condicao_id == 20 ? 'Aprovar pagamento' : 'Pendente';
            
            $dadosAtualizacao['status'] = $novoStatus;

            DB::table('orcamentos')
                ->where('id', $this->orcamentoId)
                ->whereNull('deleted_at')
                ->update(array_merge($dadosAtualizacao, ['updated_at' => now()]));

            // Gera PDF apenas quando status vai para Pendente
            if ($novoStatus === 'Pendente') {
                try {
                    $orcamentoAtualizado = $this->orcamento->fresh();
                    $pdfService = new \App\Services\OrcamentoPdfService();
                    $pdfService->gerarOrcamentoPdf($orcamentoAtualizado);
                } catch (\Exception $e) {
                    Log::error("Erro ao gerar PDF após aprovar descontos (Livewire) orçamento #{$this->orcamentoId}: " . $e->getMessage());
                }
            }
        } else {
            DB::table('orcamentos')
                ->where('id', $this->orcamentoId)
                ->whereNull('deleted_at')
                ->update(array_merge($dadosAtualizacao, ['updated_at' => now()]));
        }

        $this->orcamento = $this->orcamento->fresh();
    }

    public function render()
    {        
        return view('livewire.confirmar-descontos');
    }
}