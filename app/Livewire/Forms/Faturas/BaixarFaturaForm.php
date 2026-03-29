<?php

namespace App\Livewire\Forms\Faturas;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Fatura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\PagamentoMetodo;
use App\Models\MetodoPagamento;
use App\Models\Pagamento;

class BaixarFaturaForm extends Form
{
    public ?Fatura $fatura = null;

    #[Validate('required|numeric|min:0.01')]
    public $valor_pago = '';

    #[Validate('required|date')]
    public $data_pagamento = '';

    #[Validate('required|exists:metodos_pagamento,id')]
    public $metodo_pagamento_id = '';

    #[Validate('required|integer|min:1')]
    public $parcelas = 1;

    #[Validate('nullable|string')]
    public $observacoes = '';

    public function setFatura(Fatura $fatura)
    {
        $this->fatura = $fatura;
        $this->valor_pago = $fatura->valor_total - $fatura->valor_pago; // Default for full remaining balance
        $this->data_pagamento = now()->format('Y-m-d');
        $this->observacoes = '';
        $this->metodo_pagamento_id = '';
    }

    public function salvarBaixa()
    {
        $this->validate();

        // Extra logic to prevent overpayment
        $restante = $this->fatura->valor_total - $this->fatura->valor_pago;
        if ((float) $this->valor_pago > $restante + 0.01) {
            throw ValidationException::withMessages([
                'valor_pago' => 'O valor pago não pode ser maior que o valor restante (R$ ' . number_format($restante, 2, ',', '.') . ').'
            ]);
        }

        try {
            DB::transaction(function () {
                $novoValorPago = $this->fatura->valor_pago + (float) $this->valor_pago;
                $status = ($novoValorPago >= $this->fatura->valor_total - 0.01) ? 'pago' : 'parcial';

                // We create a standalone Pagamento record to register the entry
                $pagamento = Pagamento::create([
                    'orcamento_id'          => $this->fatura->orcamento_id,
                    'pedido_id'             => $this->fatura->pedido_id,
                    'condicao_pagamento_id' => 1, // À Vista padrão — ajustar se houver múltiplas condições
                    'valor_final'           => $this->valor_pago,
                    'valor_pago'            => $this->valor_pago,
                    'troco'                 => 0,
                    'data_pagamento'        => $this->data_pagamento,
                    'tipo_documento'        => 'recibo',
                    'observacoes'           => 'Baixa de Fatura #' . $this->fatura->id . ' - ' . $this->observacoes,
                    'user_id'               => auth()->id(),
                ]);

                PagamentoMetodo::create([
                    'pagamento_id' => $pagamento->id,
                    'metodo_pagamento_id' => $this->metodo_pagamento_id,
                    'valor' => $this->valor_pago,
                    'usa_credito' => false,
                    'parcelas' => $this->parcelas,
                    'valor_parcela' => (float) $this->valor_pago / $this->parcelas,
                ]);

                $this->fatura->update([
                    'valor_pago' => $novoValorPago,
                    'data_pagamento' => $status === 'pago' ? $this->data_pagamento : $this->fatura->data_pagamento,
                    'status' => $status,
                ]);

                // Etapa 2: Baixa definitiva para Rota/Transportadora ao concluir pagamento (exceto Encomendas)
                if ($this->fatura->orcamento_id) {
                    $orcamento = $this->fatura->orcamento;
                    if ($orcamento->pagamentoFinalizado()) {
                        if (!$orcamento->isEncomenda() && $orcamento->isEntregaAgendada()) {
                            Log::info("Pagamento total atingido para Orçamento de Rota/Transporte #{$orcamento->id}. Baixando estoque definitivo.");
                            app(\App\Services\EstoqueService::class)->baixarEstoqueDefinitivo($orcamento);
                            $orcamento->update(['status' => 'Pago']);
                        } elseif ($orcamento->isEncomenda()) {
                            // Etapa 3: Garantir status 'Pago' para encomendas para que apareçam na tela de retirada
                            Log::info("Pagamento total atingido para Encomenda #{$orcamento->id}. Definindo status como 'Pago'.");
                            $orcamento->update(['status' => 'Pago']);
                        }
                    }
                }

                Log::info('Fatura baixada com sucesso', [
                    'fatura_id' => $this->fatura->id,
                    'valor_pago' => $this->valor_pago,
                    'novo_status' => $status,
                    'user_id' => auth()->id()
                ]);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao baixar fatura', [
                'fatura_id' => $this->fatura->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
