<?php

namespace App\Services;

use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use Illuminate\Support\Facades\DB;

class CreditoService
{
    /**
     * Obtém o saldo total de créditos disponíveis do cliente
     * 
     * @param int $clienteId
     * @return float
     */
    public function getSaldoDisponivel($clienteId)
    {
        return ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(function($query) {
                $query->whereNull('data_validade')
                      ->orWhere('data_validade', '>=', now());
            })
            ->sum('valor_disponivel');
    }

    /**
     * Obtém todos os créditos ativos do cliente
     * 
     * @param int $clienteId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCreditosAtivos($clienteId)
    {
        return ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(function($query) {
                $query->whereNull('data_validade')
                      ->orWhere('data_validade', '>=', now());
            })
            ->orderBy('data_validade', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Utiliza créditos do cliente em uma venda/orçamento (FIFO)
     * 
     * @param int $clienteId
     * @param float $valorUtilizar
     * @param int $referenciaId
     * @param string $referenciaTipo (ex: 'venda', 'orcamento')
     * @param int $usuarioId
     * @param string $motivo
     * @return array
     */
    public function utilizarCreditos($clienteId, $valorUtilizar, $referenciaId, $referenciaTipo, $usuarioId, $motivo)
    {
        return DB::transaction(function () use ($clienteId, $valorUtilizar, $referenciaId, $referenciaTipo, $usuarioId, $motivo) {
            $valorRestante = $valorUtilizar;
            $creditosUtilizados = [];

            // Busca créditos disponíveis (FIFO - primeiro que vence primeiro)
            $creditos = $this->getCreditosAtivos($clienteId);

            if ($creditos->isEmpty()) {
                throw new \Exception('Cliente não possui créditos disponíveis');
            }

            $saldoTotal = $creditos->sum('valor_disponivel');
            if ($saldoTotal < $valorUtilizar) {
                throw new \Exception('Créditos insuficientes. Disponível: R$ ' . number_format($saldoTotal, 2, ',', '.'));
            }

            foreach ($creditos as $credito) {
                if ($valorRestante <= 0) break;

                $valorUsar = min($credito->valor_disponivel, $valorRestante);
                $saldoAnterior = $credito->valor_disponivel;
                $saldoPosterior = $saldoAnterior - $valorUsar;

                // Registra a movimentação
                $movimentacao = ClienteCreditoMovimentacoes::create([
                    'credito_id' => $credito->id,
                    'cliente_id' => $clienteId,
                    'tipo_movimentacao' => 'utilizacao',
                    'valor_movimentado' => $valorUsar,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => $saldoPosterior,
                    'motivo' => $motivo,
                    'referencia_tipo' => $referenciaTipo,
                    'referencia_id' => $referenciaId,
                    'usuario_id' => $usuarioId,
                ]);

                // Atualiza o crédito
                $credito->valor_disponivel = $saldoPosterior;
                if ($saldoPosterior == 0) {
                    $credito->status = 'utilizado';
                }
                $credito->save();

                $creditosUtilizados[] = [
                    'credito_id' => $credito->id,
                    'valor_usado' => $valorUsar,
                    'movimentacao_id' => $movimentacao->id,
                ];

                $valorRestante -= $valorUsar;
            }

            return [
                'sucesso' => $valorRestante == 0,
                'valor_utilizado' => $valorUtilizar - $valorRestante,
                'valor_restante' => $valorRestante,
                'creditos_utilizados' => $creditosUtilizados,
            ];
        });
    }

    /**
     * Gera crédito de troco para o cliente
     * 
     * @param int $clienteId
     * @param float $valorTroco
     * @param int $referenciaId
     * @param string $referenciaTipo
     * @param int $usuarioId
     * @param string $motivoOrigem
     * @return ClienteCredito
     */
    public function gerarCreditoTroco($clienteId, $valorTroco, $referenciaId, $referenciaTipo, $usuarioId, $motivoOrigem)
    {
        return DB::transaction(function () use ($clienteId, $valorTroco, $referenciaId, $referenciaTipo, $usuarioId, $motivoOrigem) {
            // Cria o novo crédito de troco
            $credito = ClienteCreditos::create([
                'cliente_id' => $clienteId,
                'valor_original' => $valorTroco,
                'valor_disponivel' => $valorTroco,
                'tipo' => 'troco',
                'motivo_origem' => $motivoOrigem,
                'origem_tipo' => $referenciaTipo,
                'origem_id' => $referenciaId,
                'usuario_criacao_id' => $usuarioId,
                'status' => 'ativo',
                'data_validade' => now()->addYear(), // 1 ano de validade
            ]);

            // Registra a movimentação de criação
            ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $clienteId,
                'tipo_movimentacao' => 'geracao_troco',
                'valor_movimentado' => $valorTroco,
                'saldo_anterior' => 0,
                'saldo_posterior' => $valorTroco,
                'motivo' => $motivoOrigem,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
                'usuario_id' => $usuarioId,
            ]);

            return $credito;
        });
    }

    /**
     * Gera crédito de devolução
     * 
     * @param int $clienteId
     * @param float $valorDevolucao
     * @param int $referenciaId
     * @param string $referenciaTipo
     * @param int $usuarioId
     * @param string $motivo
     * @return ClienteCredito
     */
    public function gerarCreditoDevolucao($clienteId, $valorDevolucao, $referenciaId, $referenciaTipo, $usuarioId, $motivo)
    {
        return DB::transaction(function () use ($clienteId, $valorDevolucao, $referenciaId, $referenciaTipo, $usuarioId, $motivo) {
            $credito = ClienteCreditos::create([
                'cliente_id' => $clienteId,
                'valor_original' => $valorDevolucao,
                'valor_disponivel' => $valorDevolucao,
                'tipo' => 'devolucao',
                'motivo_origem' => $motivo,
                'origem_tipo' => $referenciaTipo,
                'origem_id' => $referenciaId,
                'usuario_criacao_id' => $usuarioId,
                'status' => 'ativo',
                'data_validade' => now()->addYear(),
            ]);

            ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $clienteId,
                'tipo_movimentacao' => 'geracao_troco',
                'valor_movimentado' => $valorDevolucao,
                'saldo_anterior' => 0,
                'saldo_posterior' => $valorDevolucao,
                'motivo' => $motivo,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
                'usuario_id' => $usuarioId,
            ]);

            return $credito;
        });
    }

    /**
     * Gera crédito de bonificação
     * 
     * @param int $clienteId
     * @param float $valorBonificacao
     * @param int $usuarioId
     * @param string $motivo
     * @return ClienteCredito
     */
    public function gerarCreditoBonificacao($clienteId, $valorBonificacao, $usuarioId, $motivo)
    {
        return DB::transaction(function () use ($clienteId, $valorBonificacao, $usuarioId, $motivo) {
            $credito = ClienteCreditos::create([
                'cliente_id' => $clienteId,
                'valor_original' => $valorBonificacao,
                'valor_disponivel' => $valorBonificacao,
                'tipo' => 'bonificacao',
                'motivo_origem' => $motivo,
                'origem_tipo' => 'manual',
                'origem_id' => null,
                'usuario_criacao_id' => $usuarioId,
                'status' => 'ativo',
                'data_validade' => now()->addYear(),
            ]);

            ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $clienteId,
                'tipo_movimentacao' => 'geracao_troco',
                'valor_movimentado' => $valorBonificacao,
                'saldo_anterior' => 0,
                'saldo_posterior' => $valorBonificacao,
                'motivo' => $motivo,
                'referencia_tipo' => 'manual',
                'referencia_id' => null,
                'usuario_id' => $usuarioId,
            ]);

            return $credito;
        });
    }

    /**
     * Cancela/estorna um crédito
     * 
     * @param int $creditoId
     * @param int $usuarioId
     * @param string $motivo
     * @return ClienteCredito
     */
    public function cancelarCredito($creditoId, $usuarioId, $motivo)
    {
        return DB::transaction(function () use ($creditoId, $usuarioId, $motivo) {
            $credito = ClienteCreditos::findOrFail($creditoId);
            
            if ($credito->status !== 'ativo') {
                throw new \Exception('Apenas créditos ativos podem ser cancelados');
            }

            $saldoAnterior = $credito->valor_disponivel;

            if ($saldoAnterior > 0) {
                ClienteCreditoMovimentacoes::create([
                    'credito_id' => $credito->id,
                    'cliente_id' => $credito->cliente_id,
                    'tipo_movimentacao' => 'cancelamento',
                    'valor_movimentado' => $saldoAnterior,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => 0,
                    'motivo' => $motivo,
                    'usuario_id' => $usuarioId,
                ]);
            }

            $credito->valor_disponivel = 0;
            $credito->status = 'cancelado';
            $credito->save();

            return $credito;
        });
    }

    /**
     * Estorna uma utilização de crédito
     * 
     * @param int $movimentacaoId
     * @param int $usuarioId
     * @param string $motivo
     * @return array
     */
    public function estornarUtilizacao($movimentacaoId, $usuarioId, $motivo)
    {
        return DB::transaction(function () use ($movimentacaoId, $usuarioId, $motivo) {
            $movimentacaoOriginal = ClienteCreditoMovimentacoes::findOrFail($movimentacaoId);

            if ($movimentacaoOriginal->tipo_movimentacao !== 'utilizacao') {
                throw new \Exception('Apenas movimentações de utilização podem ser estornadas');
            }

            $credito = ClienteCreditos::findOrFail($movimentacaoOriginal->credito_id);
            $valorEstornar = $movimentacaoOriginal->valor_movimentado;
            $saldoAnterior = $credito->valor_disponivel;
            $saldoPosterior = $saldoAnterior + $valorEstornar;

            // Cria movimentação de estorno
            $movimentacaoEstorno = ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $credito->cliente_id,
                'tipo_movimentacao' => 'estorno',
                'valor_movimentado' => $valorEstornar,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'motivo' => $motivo,
                'referencia_tipo' => $movimentacaoOriginal->referencia_tipo,
                'referencia_id' => $movimentacaoOriginal->referencia_id,
                'usuario_id' => $usuarioId,
            ]);

            // Atualiza o crédito
            $credito->valor_disponivel = $saldoPosterior;
            if ($credito->status === 'utilizado' && $saldoPosterior > 0) {
                $credito->status = 'ativo';
            }
            $credito->save();

            return [
                'credito' => $credito,
                'movimentacao_estorno' => $movimentacaoEstorno,
                'valor_estornado' => $valorEstornar,
            ];
        });
    }

    /**
     * Obtém o histórico de movimentações de um cliente
     * 
     * @param int $clienteId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistoricoMovimentacoes($clienteId, $limit = 50)
    {
        return ClienteCreditoMovimentacoes::where('cliente_id', $clienteId)
            ->with(['credito', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Expira créditos vencidos
     * 
     * @return int Quantidade de créditos expirados
     */
    public function expirarCreditosVencidos()
    {
        return DB::transaction(function () {
            $creditosVencidos = ClienteCreditos::where('status', 'ativo')
                ->where('valor_disponivel', '>', 0)
                ->whereNotNull('data_validade')
                ->where('data_validade', '<', now())
                ->get();

            $quantidadeExpirada = 0;

            foreach ($creditosVencidos as $credito) {
                $saldoAnterior = $credito->valor_disponivel;

                    ClienteCreditoMovimentacoes::create([
                    'credito_id' => $credito->id,
                    'cliente_id' => $credito->cliente_id,
                    'tipo_movimentacao' => 'expiracao',
                    'valor_movimentado' => $saldoAnterior,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => 0,
                    'motivo' => 'Crédito expirado automaticamente por vencimento da data de validade',
                    'usuario_id' => 1, // Sistema
                ]);

                $credito->valor_disponivel = 0;
                $credito->status = 'expirado';
                $credito->save();

                $quantidadeExpirada++;
            }

            return $quantidadeExpirada;
        });
    }
}