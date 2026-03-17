<?php

namespace App\Services;

use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use App\Models\OrderReturn;
use Illuminate\Support\Facades\DB;

class FinancialService
{
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
