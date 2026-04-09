<?php

namespace App\Services;

use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use App\Models\OrderReturn;
use App\Models\Orcamento;
use App\Models\Pagamento;
use App\Models\PagamentoForma;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    /**
     * Realiza a cobrança financeira automática após aprovação de encomenda.
     * 
     * @param Orcamento $orcamento
     * @return array
     * @throws \Exception
     */
    public function processarCobrancaEncomenda(Orcamento $orcamento): array
    {
        return DB::transaction(function () use ($orcamento) {
            $valorOriginal = (float) $orcamento->valor_com_desconto;
            
            Log::info("Iniciando cobrança automática para encomenda #{$orcamento->id}", [
                'cliente_id' => $orcamento->cliente_id,
                'valor' => $valorOriginal
            ]);

            // 1. Simula chamada para Gateway de Pagamento / Módulo Financeiro
            // Em um cenário real, aqui seria feita a integração via API.
            $transactionId = 'AUTO-' . strtoupper(bin2hex(random_bytes(6)));
            $timestamp = now();

            // 2. Registra o Pagamento no sistema
            $pagamento = Pagamento::create([
                'orcamento_id' => $orcamento->id,
                'cliente_id' => $orcamento->cliente_id,
                'condicao_pagamento_id' => $orcamento->condicao_id,
                'valor_total' => $valorOriginal,
                'status' => 'Pago',
                'tipo_documento' => 'nota_fiscal', // Padrão para encomendas
                'observacoes' => "Cobrança automática pós-aprovação de encomenda. Transação: {$transactionId}",
                'user_id' => auth()->id() ?? 1,
            ]);

            // 3. Registra a Forma de Pagamento (Assume-se a principal da condição ou uma padrão)
            PagamentoForma::create([
                'pagamento_id' => $pagamento->id,
                'metodo_pagamento_id' => 1, // Ex: Boleto ou Cartão padrão
                'valor' => $valorOriginal,
                'transaction_id' => $transactionId, // Armazena ID da transação
                'processed_at' => $timestamp,
            ]);

            // 4. Verificação rigorosa de preços
            $verificacao = $this->verificarDiscrepanciasPreco($orcamento, $valorOriginal);

            // 5. Auditoria e Logs
            Log::info("Cobrança de encomenda #{$orcamento->id} finalizada", [
                'pagamento_id' => $pagamento->id,
                'transaction_id' => $transactionId,
                'valor_cobrado' => $valorOriginal,
                'discrepancias' => $verificacao['tem_discrepancia']
            ]);

            return [
                'pagamento' => $pagamento,
                'transaction_id' => $transactionId,
                'verificacao' => $verificacao
            ];
        });
    }

    /**
     * Compara o valor aprovado com o valor cobrado e detecta discrepâncias por item.
     */
    protected function verificarDiscrepanciasPreco(Orcamento $orcamento, float $valorCobrado): array
    {
        $orcamento->load('itens');
        $totalItens = 0;
        $detalhes = [];
        $temDiscrepancia = false;

        foreach ($orcamento->itens as $item) {
            $valorItem = (float) ($item->valor_com_desconto > 0 ? $item->valor_com_desconto : ($item->valor_unitario * $item->quantidade));
            $totalItens += $valorItem;

            // Aqui poderíamos comparar com um histórico de preços se houvesse, 
            // mas o requisito pede para comparar o aprovado com o cobrado.
            // Como estamos cobrando exatamente o valor_com_desconto do orçamento, 
            // a discrepância ocorreria se o valor_com_desconto fosse alterado entre a aprovação e a cobrança.
        }

        // Se houver desconto global adicional
        if ($orcamento->desconto_especifico > 0) {
            $totalItens = max(0, $totalItens - $orcamento->desconto_especifico);
        }

        $diferenca = round($valorCobrado - $totalItens, 2);
        
        if ($diferenca != 0) {
            $temDiscrepancia = true;
            $percentual = ($totalItens > 0) ? ($diferenca / $totalItens) * 100 : 100;

            Log::warning("DISCREPÂNCIA DE PREÇO DETECTADA na encomenda #{$orcamento->id}", [
                'valor_aprovado' => $totalItens,
                'valor_cobrado' => $valorCobrado,
                'diferenca' => $diferenca,
                'percentual' => $percentual . '%'
            ]);

            // Dispara Alerta de Auditoria
            \App\Models\AcaoAtualizar::create([
                'descricao' => "ALERTA: Discrepância de preço na Encomenda #{$orcamento->id}. Aprovado: R$ {$totalItens}, Cobrado: R$ {$valorCobrado}. Dif: R$ {$diferenca} ({$percentual}%)",
                'user_id' => auth()->id() ?? 1,
            ]);
        }

        return [
            'tem_discrepancia' => $temDiscrepancia,
            'valor_aprovado' => $totalItens,
            'valor_cobrado' => $valorCobrado,
            'diferenca' => $diferenca,
        ];
    }

    /**
     * Gera crédito para o cliente baseado em uma devolução aprovada.
     */
    public function generateCreditFromReturn(OrderReturn $return)
    {
        return DB::transaction(function () use ($return) {
            $totalValue = $return->items->sum(function($item) {
                return $item->quantity_approved * $item->unit_price;
            });

            if ($totalValue <= 0) return null;

            $credit = ClienteCreditos::create([
                'cliente_id' => $return->customer_id,
                'valor_original' => $totalValue,
                'valor_disponivel' => $totalValue,
                'status' => 'ativo',
                'tipo' => 'devolucao',
                'origem_tipo' => 'devolucao',
                'origem_id' => $return->id,
                'data_validade' => now()->addYear(),
                'usuario_criacao_id' => auth()->id() ?? 1,
            ]);

            ClienteCreditoMovimentacoes::create([
                'cliente_credito_id' => $credit->id,
                'tipo_movimentacao' => 'entrada',
                'valor_movimentado' => $totalValue,
                'saldo_momento' => $totalValue,
                'descricao' => "Crédito gerado via devolução do pedido #{$return->order_id}",
                'user_id' => auth()->id() ?? 1,
            ]);

            return $credit;
        });
    }

    /**
     * Abate crédito disponível do cliente em um pagamento.
     */
    public function applyCreditAbatement($clienteId, $amount, $referenceType, $referenceId)
    {
        return DB::transaction(function () use ($clienteId, $amount, $referenceType, $referenceId) {
            $availableCredits = ClienteCreditos::where('cliente_id', $clienteId)
                ->where('status', 'ativo')
                ->where('valor_disponivel', '>', 0)
                ->orderBy('data_validade', 'asc')
                ->get();

            $remainingToAbate = $amount;

            foreach ($availableCredits as $credit) {
                if ($remainingToAbate <= 0) break;

                $abatement = min($credit->valor_disponivel, $remainingToAbate);
                
                $credit->decrement('valor_disponivel', $abatement);
                if ($credit->valor_disponivel <= 0) {
                    $credit->update(['status' => 'utilizado']);
                }

                ClienteCreditoMovimentacoes::create([
                    'cliente_credito_id' => $credit->id,
                    'tipo_movimentacao' => 'utilizacao',
                    'valor_movimentado' => $abatement,
                    'saldo_momento' => $credit->valor_disponivel,
                    'descricao' => "Abatimento de crédito em {$referenceType} #{$referenceId}",
                    'user_id' => auth()->id() ?? 1,
                ]);

                $remainingToAbate -= $abatement;
            }

            return $amount - $remainingToAbate; // Return total abated
        });
    }
}
