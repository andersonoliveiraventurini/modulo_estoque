<?php

namespace App\Livewire;

use App\Models\Pagamento;
use App\Models\PagamentoForma;
use App\Models\PagamentoComprovante;
use App\Models\CondicoesPagamento;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrcamentoPagamentoResidualBaixa extends Component
{
    use WithFileUploads;

    public Pagamento $pagamento;
    
    public $registro = [
        'valor_pago' => '',
        'data_pagamento' => '',
        'condicao_id' => '',
        'comprovante' => null,
    ];

    public $condicoes;

    public function mount($pagamento_id)
    {
        $this->pagamento = Pagamento::with(['orcamento.cliente'])->findOrFail($pagamento_id);
        
        // Se já estiver pago, redireciona de volta
        if ($this->pagamento->valor_pago > 0) {
            return redirect()->route('orcamentos.show', $this->pagamento->orcamento_id);
        }

        $this->condicoes = CondicoesPagamento::where('ativo', true)->orderBy('nome')->get();
        
        // Valores padrão
        $this->registro['valor_pago'] = $this->pagamento->valor_final;
        $this->registro['data_pagamento'] = now()->format('Y-m-d');
        $this->registro['condicao_id'] = $this->pagamento->condicao_pagamento_id;
    }

    public function registrarPagamento()
    {
        $this->validate([
            'registro.valor_pago' => 'required|numeric|min:0.01',
            'registro.data_pagamento' => 'required|date|before_or_equal:today',
            'registro.condicao_id' => 'required|exists:condicoes_pagamento,id',
            'registro.comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'registro.valor_pago.required' => 'Informe o valor efetivamente pago.',
            'registro.condicao_id.required' => 'Selecione a forma de pagamento utilizada.',
        ]);

        try {
            DB::transaction(function () {
                // Atualiza o pagamento principal
                $this->pagamento->update([
                    'valor_pago' => (float) $this->registro['valor_pago'],
                    'data_pagamento' => $this->registro['data_pagamento'],
                    'condicao_pagamento_id' => $this->registro['condicao_id'],
                ]);

                // Cria a forma de pagamento detalhada
                $forma = PagamentoForma::create([
                    'pagamento_id' => $this->pagamento->id,
                    'condicao_pagamento_id' => $this->registro['condicao_id'],
                    'valor' => (float) $this->registro['valor_pago'],
                    'parcelas' => 1,
                    'valor_parcela' => (float) $this->registro['valor_pago'],
                ]);

                // Upload do comprovante se existir
                if ($this->registro['comprovante']) {
                    $path = $this->registro['comprovante']->store('comprovantes', 'public');
                    PagamentoComprovante::create([
                        'pagamento_id' => $this->pagamento->id,
                        'pagamento_forma_id' => $forma->id,
                        'nome_original' => $this->registro['comprovante']->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $this->registro['comprovante']->getMimeType(),
                        'tamanho' => $this->registro['comprovante']->getSize(),
                        'user_id' => auth()->id(),
                    ]);
                }
            });

            return redirect()->route('orcamentos.show', $this->pagamento->orcamento_id)
                ->with('success', 'Pagamento residual baixado com sucesso!');

        } catch (\Exception $e) {
            Log::error("Erro ao registrar pagamento residual: " . $e->getMessage());
            $this->dispatch('notify-swal', [
                'icon' => 'error',
                'title' => 'Erro!',
                'text' => 'Falha ao registrar o pagamento.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.orcamento-pagamento-residual-baixa')
            ->title('Baixar Pagamento Residual - Orçamento #' . $this->pagamento->orcamento_id);
    }
}
