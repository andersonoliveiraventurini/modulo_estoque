<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use App\Models\ClientCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditoService
{
    /**
     * Obtém o saldo total de créditos disponíveis do cliente
     */
    public function getSaldoDisponivel($clienteId)
    {
        $saldo = (float) ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(function($query) {
                $query->whereNull('data_validade')
                      ->orWhere('data_validade', '>=', now());
            })
            ->sum('valor_disponivel');

        // Opcional: sincroniza se houver divergência
        $cliente = Cliente::find($clienteId);
        if ($cliente && isset($cliente->saldo_credito) && (float)$cliente->saldo_credito !== $saldo) {
            $cliente->update(['saldo_credito' => $saldo]);
        }

        return $saldo;
    }

    /**
     * Sincroniza o saldo persistido do cliente com o somatório dos créditos ativos
     */
    public function sincronizarSaldo($clienteId)
    {
        $saldo = (float) ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(function($query) {
                $query->whereNull('data_validade')
                      ->orWhere('data_validade', '>=', now());
            })
            ->sum('valor_disponivel');

        Cliente::where('id', $clienteId)->update(['saldo_credito' => $saldo]);
        return $saldo;
    }

    /**
     * Obtém todos os créditos ativos do cliente
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
     */
    public function utilizarCreditos($clienteId, $valorUtilizar, $referenciaId, $referenciaTipo, $usuarioId, $motivo)
    {
        Log::info("Iniciando utilização de créditos", [
            'cliente_id' => $clienteId,
            'valor' => $valorUtilizar,
            'referencia_id' => $referenciaId,
            'referencia_tipo' => $referenciaTipo,
        ]);

        try {
            return DB::transaction(function () use ($clienteId, $valorUtilizar, $referenciaId, $referenciaTipo, $usuarioId, $motivo) {
                $valorRestante = $valorUtilizar;
                $creditosUtilizados = [];

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

                    Log::info("Utilizando crédito individual #{$credito->id}", [
                        'valor_usar' => $valorUsar,
                        'saldo_anterior' => $saldoAnterior,
                        'saldo_posterior' => $saldoPosterior,
                    ]);

                    // Registra a movimentação antiga
                    ClienteCreditoMovimentacoes::create([
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

                    $credito->valor_disponivel = $saldoPosterior;
                    if ($saldoPosterior == 0) {
                        $credito->status = 'utilizado';
                    }
                    $credito->save();

                    $creditosUtilizados[] = [
                        'credito_id' => $credito->id,
                        'valor_utilizado' => $valorUsar,
                    ];

                    $valorRestante -= $valorUsar;
                }

                // Novo Registro de Débito (Transaction Log)
                $orcamentoId = null;
                if ($referenciaTipo === 'orcamento') {
                    $orcamentoId = $referenciaId;
                } elseif ($referenciaTipo === 'pagamento') {
                    $pagamento = \App\Models\Pagamento::find($referenciaId);
                    $orcamentoId = $pagamento?->orcamento_id;
                }

                ClientCredit::create([
                    'cliente_id' => $clienteId,
                    'orcamento_id' => $orcamentoId,
                    'tipo' => 'saida',
                    'valor' => $valorUtilizar,
                    'descricao' => $motivo,
                ]);

                $this->sincronizarSaldo($clienteId);

                Log::info("Utilização de créditos finalizada com sucesso", [
                    'cliente_id' => $clienteId,
                    'valor_total' => $valorUtilizar,
                ]);

                return [
                    'sucesso' => true,
                    'valor_utilizado' => $valorUtilizar,
                    'creditos_utilizados' => $creditosUtilizados,
                ];
            });
        } catch (\Exception $e) {
            Log::error("Erro na utilização de créditos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gera crédito (usado por Devoluções e Trocos)
     */
    public function adicionarCredito(Cliente $cliente, float $valor, string $descricao, ?int $returnId = null, $tipo = 'devolucao')
    {
        return DB::transaction(function () use ($cliente, $valor, $descricao, $returnId, $tipo) {
            // 1. Criar registro no sistema antigo (FIFO compatível)
            $credito = ClienteCreditos::create([
                'cliente_id' => $cliente->id,
                'valor_original' => $valor,
                'valor_disponivel' => $valor,
                'tipo' => $tipo,
                'motivo_origem' => $descricao,
                'origem_tipo' => 'return',
                'origem_id' => $returnId,
                'usuario_criacao_id' => auth()->id() ?? 1,
                'status' => 'ativo',
                'data_validade' => now()->addYear(),
            ]);

            // 2. Criar registro no novo sistema (Transaction Log)
            ClientCredit::create([
                'cliente_id' => $cliente->id,
                'return_id' => $returnId,
                'tipo' => 'entrada',
                'valor' => $valor,
                'descricao' => $descricao,
            ]);

            // 3. Sincronizar saldo persistido
            $this->sincronizarSaldo($cliente->id);

            return $credito;
        });
    }

    // Métodos legados mantidos para compatibilidade se chamados diretamente
    public function gerarCreditoTroco($clienteId, $valorTroco, $referenciaId, $referenciaTipo, $usuarioId, $motivoOrigem)
    {
        $cliente = Cliente::find($clienteId);
        return $this->adicionarCredito($cliente, $valorTroco, $motivoOrigem, $referenciaId, 'troco');
    }

    public function gerarCreditoDevolucao($clienteId, $valorDevolucao, $referenciaId, $referenciaTipo, $usuarioId, $motivo)
    {
        $cliente = Cliente::find($clienteId);
        return $this->adicionarCredito($cliente, $valorDevolucao, $motivo, $referenciaId, 'devolucao');
    }

    /**
     * Estorna uma utilização de crédito específica
     */
    public function estornarUtilizacao($movimentacaoId, $usuarioId, $motivo)
    {
        Log::info("Iniciando estorno de utilização de crédito", [
            'movimentacao_id' => $movimentacaoId,
            'user_id' => $usuarioId,
            'motivo' => $motivo,
        ]);

        return DB::transaction(function () use ($movimentacaoId, $usuarioId, $motivo) {
            $movimentacao = ClienteCreditoMovimentacoes::findOrFail($movimentacaoId);
            
            if ($movimentacao->tipo_movimentacao !== 'utilizacao') {
                throw new \Exception('Esta movimentação não é uma utilização de crédito');
            }

            $credito = $movimentacao->credito;
            $valorEstorno = $movimentacao->valor_movimentado;

            // 1. Devolve o valor ao crédito original
            $saldoAnterior = $credito->valor_disponivel;
            $saldoPosterior = $saldoAnterior + $valorEstorno;

            Log::info("Devolvendo valor ao crédito original #{$credito->id}", [
                'valor_estorno' => $valorEstorno,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
            ]);

            $credito->valor_disponivel = $saldoPosterior;
            $credito->status = 'ativo'; // Garante que volta a ser ativo se estava utilizado
            $credito->save();

            // 2. Registra a nova movimentação de estorno
            $novaMovimentacao = ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $credito->cliente_id,
                'tipo_movimentacao' => 'estorno',
                'valor_movimentado' => $valorEstorno,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'motivo' => $motivo,
                'referencia_tipo' => $movimentacao->referencia_tipo,
                'referencia_id' => $movimentacao->referencia_id,
                'usuario_id' => $usuarioId,
            ]);

            // 3. Registra no novo sistema (Transaction Log)
            $orcamentoId = null;
            if ($movimentacao->referencia_tipo === 'orcamento') {
                $orcamentoId = $movimentacao->referencia_id;
            } elseif ($movimentacao->referencia_tipo === 'pagamento') {
                $pagamento = \App\Models\Pagamento::find($movimentacao->referencia_id);
                $orcamentoId = $pagamento?->orcamento_id;
            }

            ClientCredit::create([
                'cliente_id' => $credito->cliente_id,
                'orcamento_id' => $orcamentoId,
                'tipo' => 'entrada',
                'valor' => $valorEstorno,
                'descricao' => "ESTORNO: " . $motivo,
            ]);

            // 4. Sincronizar saldo persistido
            $this->sincronizarSaldo($credito->cliente_id);

            Log::info("Estorno de utilização de crédito finalizado com sucesso", [
                'credito_id' => $credito->id,
                'valor_estornado' => $valorEstorno,
            ]);

            return $novaMovimentacao;
        });
    }
}