<?php

namespace App\Services;

use App\Models\Fatura;
use App\Models\PedidoCompra;
use App\Models\Pagamento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FluxoCaixaService
{
    /**
     * Consolida os dados de Previsto vs Realizado no período.
     */
    public function obterDadosFluxo($dataInicio, $dataFim)
    {
        $entradasPrevistas = $this->getEntradasPrevistas($dataInicio, $dataFim);
        $entradasRealizadas = $this->getEntradasRealizadas($dataInicio, $dataFim);
        $saidasPrevistas = $this->getSaidasPrevistas($dataInicio, $dataFim);
        
        // Como o sistema não tem Contas a Pagar "Realizado" formal, 
        // assumiremos saídas reais como pedidos recebidos.
        $saidasRealizadas = $this->getSaidasRealizadas($dataInicio, $dataFim);

        return [
            'periodo' => [
                'inicio' => $dataInicio->format('d/m/Y'),
                'fim' => $dataFim->format('d/m/Y'),
            ],
            'resumo' => [
                'total_entradas_previstas' => $entradasPrevistas->sum('valor'),
                'total_entradas_realizadas' => $entradasRealizadas->sum('valor'),
                'total_saidas_previstas' => $saidasPrevistas->sum('valor'),
                'total_saidas_realizadas' => $saidasRealizadas->sum('valor'),
            ],
            'detalhes' => [
                'entradas_previstas' => $entradasPrevistas,
                'entradas_realizadas' => $entradasRealizadas,
                'saidas_previstas' => $saidasPrevistas,
                'saidas_realizadas' => $saidasRealizadas,
            ]
        ];
    }

    private function getEntradasPrevistas($inicio, $fim)
    {
        return Fatura::whereBetween('data_vencimento', [$inicio, $fim])
            ->where('status', '!=', 'pago')
            ->select(
                DB::raw('DATE(data_vencimento) as data'),
                DB::raw('SUM(valor_total) as valor')
            )
            ->groupBy('data')
            ->get();
    }

    private function getEntradasRealizadas($inicio, $fim)
    {
        // Pega faturas pagas e pagamentos avulsos
        return Fatura::whereBetween('data_pagamento', [$inicio, $fim])
            ->where('status', 'pago')
            ->select(
                DB::raw('DATE(data_pagamento) as data'),
                DB::raw('SUM(valor_pago) as valor')
            )
            ->groupBy('data')
            ->get();
    }

    private function getSaidasPrevistas($inicio, $fim)
    {
        // Baseado em PedidoCompra (Previsão de Saída)
        return PedidoCompra::whereBetween('data_pedido', [$inicio, $fim])
            ->whereIn('status', ['aguardando', 'parcialmente_recebido'])
            ->select(
                DB::raw('DATE(data_pedido) as data'),
                DB::raw('SUM(valor_total) as valor')
            )
            ->groupBy('data')
            ->get();
    }

    private function getSaidasRealizadas($inicio, $fim)
    {
        // Consideramos saídas reais quando o pedido de compra é recebido
        return PedidoCompra::whereBetween('updated_at', [$inicio, $fim])
            ->where('status', 'recebido')
            ->select(
                DB::raw('DATE(updated_at) as data'),
                DB::raw('SUM(valor_total) as valor')
            )
            ->groupBy('data')
            ->get();
    }
}
