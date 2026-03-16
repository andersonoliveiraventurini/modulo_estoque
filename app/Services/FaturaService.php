<?php

namespace App\Services;

use App\Models\Fatura;
use App\Models\Orcamento;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FaturaService
{
    /**
     * Gera faturas para um orçamento ou pedido com base na condição de pagamento
     */
    public function gerarFaturasVenda($registro, array $dadosPagamento): void
    {
        Log::info("Gerando faturas para venda", [
            'tipo' => $registro instanceof Orcamento ? 'orcamento' : 'pedido',
            'id' => $registro->id,
            'condicao_id' => $dadosPagamento['condicao_pagamento_id']
        ]);

        $metodos = $dadosPagamento['metodos_pagamento'];
        $clienteId = $registro->cliente_id;

        foreach ($metodos as $metodoPag) {
            $valorTotalMetodo = (float) $metodoPag['valor'];
            $parcelas = (int) ($metodoPag['parcelas'] ?? 1);
            $valorParcela = round($valorTotalMetodo / $parcelas, 2);
            $diferenca = $valorTotalMetodo - ($valorParcela * $parcelas);
            
            for ($i = 1; $i <= $parcelas; $i++) {
                $valorFinalParcela = ($i === $parcelas) ? ($valorParcela + $diferenca) : $valorParcela;
                
                // Calcula vencimento (assumindo 30 dias entre parcelas por padrão se não especificado)
                $vencimento = Carbon::now()->addDays(($i - 1) * 30);
                
                // Se o vencimento é hoje, assumimos que esta parcela foi paga no ato (pagamento à vista ou entrada)
                $isPago = $vencimento->isToday();

                Fatura::create([
                    'cliente_id' => $clienteId,
                    'orcamento_id' => $registro instanceof Orcamento ? $registro->id : null,
                    'pedido_id' => $registro instanceof Pedido ? $registro->id : null,
                    'valor_total' => $valorFinalParcela,
                    'valor_pago' => $isPago ? $valorFinalParcela : 0,
                    'data_pagamento' => $isPago ? Carbon::now() : null,
                    'numero_parcela' => $i,
                    'total_parcelas' => $parcelas,
                    'data_vencimento' => $vencimento,
                    'status' => $isPago ? 'pago' : 'pendente',
                ]);
            }
        }
    }

    /**
     * Atualiza o status das faturas com base no vencimento
     */
    public function verificarInadimplencia(): int
    {
        $faturasAtrasadas = Fatura::where('status', 'pendente')
            ->where('data_vencimento', '<', Carbon::today())
            ->get();

        /** @var Fatura $fatura */
        foreach ($faturasAtrasadas as $fatura) {
            $fatura->update(['status' => 'vencido']);
            Log::warning("Fatura marcada como vencida", ['fatura_id' => $fatura->id]);
        }

        return $faturasAtrasadas->count();
    }

    /**
     * Gera uma Fatura simples para um Orçamento finalizado (sem condição de pagamento parcelada).
     * Caso já exista uma Fatura vinculada ao orçamento, não cria duplicata.
     */
    public function gerarFaturaPorOrcamento(Orcamento $orcamento): ?Fatura
    {
        if (Fatura::where('orcamento_id', $orcamento->id)->exists()) {
            Log::info("Fatura já existe para Orçamento #{$orcamento->id}. Pulando geração.");
            return null;
        }

        $vencimento = now()->addDays(30);

        $fatura = Fatura::create([
            'cliente_id'      => $orcamento->cliente_id,
            'orcamento_id'    => $orcamento->id,
            'valor_total'     => $orcamento->valor_com_desconto > 0 ? $orcamento->valor_com_desconto : $orcamento->valor_total_itens,
            'valor_pago'      => 0,
            'numero_parcela'  => 1,
            'total_parcelas'  => 1,
            'data_vencimento' => $vencimento,
            'status'          => 'pendente',
        ]);

        Log::info("Fatura #{$fatura->id} gerada automaticamente para Orçamento #{$orcamento->id}");

        return $fatura;
    }
}
