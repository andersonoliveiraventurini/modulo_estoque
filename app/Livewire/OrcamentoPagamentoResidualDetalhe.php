<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\Pagamento;
use App\Models\PagamentoForma;
use App\Models\PagamentoComprovante;
use App\Models\CondicoesPagamento;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrcamentoPagamentoResidualDetalhe extends Component
{
    use WithFileUploads;

    public Orcamento $orcamento;
    
    // Form 1: Solicitação de Pagamento
    public $solicitacao = [
        'valor' => '',
        'data_vencimento' => '',
        'descricao' => '',
        'condicao_id' => '',
    ];

    public $condicoes;

    public function mount($id)
    {
        $this->orcamento = Orcamento::with(['cliente', 'pagamentos.condicaoPagamento'])->findOrFail($id);
        $this->condicoes = CondicoesPagamento::where('ativo', true)->orderBy('nome')->get();
        
        $this->solicitacao['data_vencimento'] = now()->addDays(2)->format('Y-m-d');
    }

    public function salvarSolicitacao()
    {
        $this->validate([
            'solicitacao.valor' => 'required|numeric|min:0.01',
            'solicitacao.descricao' => 'required|string|min:3|max:500',
            'solicitacao.data_vencimento' => 'required|date|after_or_equal:today',
            'solicitacao.condicao_id' => 'required|exists:condicoes_pagamento,id',
        ], [
            'solicitacao.valor.required' => 'O valor é obrigatório.',
            'solicitacao.descricao.required' => 'O motivo/descrição é obrigatório.',
            'solicitacao.condicao_id.required' => 'Selecione um meio de pagamento sugerido.',
        ]);

        try {
            DB::transaction(function () {
                Pagamento::create([
                    'orcamento_id' => $this->orcamento->id,
                    'condicao_pagamento_id' => $this->solicitacao['condicao_id'],
                    'data_pagamento' => $this->solicitacao['data_vencimento'],
                    'tipo_documento' => 'nota_fiscal',
                    'user_id' => auth()->id(),
                    'valor_final' => (float) $this->solicitacao['valor'],
                    'valor_pago' => 0,
                    'observacoes' => $this->solicitacao['descricao'],
                    'tipo' => Pagamento::TIPO_RESIDUAL,
                    'estornado' => false,
                ]);
            });

            $this->reset('solicitacao');
            $this->solicitacao['data_vencimento'] = now()->addDays(2)->format('Y-m-d');

            return redirect()->route('orcamentos.show', $this->orcamento->id)
                ->with('success', 'Solicitação de cobrança residual registrada com sucesso!');

        } catch (\Exception $e) {
            Log::error("Erro ao solicitar residual: " . $e->getMessage());
            $this->dispatch('notify-swal', [
                'icon' => 'error',
                'title' => 'Erro!',
                'text' => 'Falha ao processar solicitação.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.orcamento-pagamento-residual-detalhe')
            ->title('Gestão de Residuais - Orçamento #' . $this->orcamento->id);
    }
}
