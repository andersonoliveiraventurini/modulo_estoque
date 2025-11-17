<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\PagamentoMetodo;
use App\Models\Orcamento;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagamentoService
{
    protected $creditoService;

    public function __construct(CreditoService $creditoService)
    {
        $this->creditoService = $creditoService;
    }

    /**
     * Processa o pagamento completo de um orçamento ou pedido
     * 
     * @param array $dados [
     *   'orcamento_id' => int|null,
     *   'pedido_id' => int|null,
     *   'condicao_pagamento_id' => int,
     *   'metodos_pagamento' => [
     *     ['metodo_id' => int, 'valor' => float],
     *     ['metodo_id' => int, 'valor' => float, 'usa_credito' => true],
     *   ],
     *   'desconto_balcao' => float|null,
     *   'valor_pago' => float,
     *   'tipo_documento' => string,
     *   'cnpj_cpf_nota' => string|null,
     *   'observacoes' => string|null,
     *   'gerar_troco_como_credito' => bool (default: false),
     * ]
     * @return array
     */
    public function processarPagamento(array $dados)
    {
        return DB::transaction(function () use ($dados) {
            // Valida os dados
            $this->validarDadosPagamento($dados);

            // Busca o registro (orçamento ou pedido)
            $registro = $this->buscarRegistro($dados);
            $clienteId = $registro->cliente_id;

            // Calcula valores
            $valorTotal = $registro->valor_total;
            $descontoAplicado = $registro->desconto ?? 0;
            $descontoBalcao = $dados['desconto_balcao'] ?? 0;
            $valorFinal = $valorTotal - $descontoAplicado - $descontoBalcao;
            $valorPago = $dados['valor_pago'];
            $troco = max(0, $valorPago - $valorFinal);

            // Cria o registro do pagamento
            $pagamento = Pagamento::create([
                'orcamento_id' => $dados['orcamento_id'] ?? null,
                'pedido_id' => $dados['pedido_id'] ?? null,
                'condicao_pagamento_id' => $dados['condicao_pagamento_id'],
                'desconto_aplicado' => $descontoAplicado,
                'desconto_balcao' => $descontoBalcao,
                'valor_final' => $valorFinal,
                'valor_pago' => $valorPago,
                'troco' => $troco,
                'data_pagamento' => now(),
                'tipo_documento' => $dados['tipo_documento'] ?? 'cupom_fiscal',
                'numero_documento' => $dados['numero_documento'] ?? null,
                'cnpj_cpf_nota' => $dados['cnpj_cpf_nota'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            $creditosUtilizados = [];
            $creditoTrocoGerado = null;

            // Processa os métodos de pagamento
            foreach ($dados['metodos_pagamento'] as $metodoPagamento) {
                $usaCredito = $metodoPagamento['usa_credito'] ?? false;

                if ($usaCredito) {
                    // Utiliza créditos do cliente
                    $resultadoCredito = $this->creditoService->utilizarCreditos(
                        $clienteId,
                        $metodoPagamento['valor'],
                        $pagamento->id,
                        $dados['orcamento_id'] ? 'orcamento' : 'pedido',
                        Auth::id(),
                        $this->gerarMotivoUtilizacaoCredito($dados)
                    );

                    $creditosUtilizados = array_merge($creditosUtilizados, $resultadoCredito['creditos_utilizados']);
                }

                // Registra o método de pagamento usado
                PagamentoMetodo::create([
                    'pagamento_id' => $pagamento->id,
                    'metodo_pagamento_id' => $metodoPagamento['metodo_id'],
                    'valor' => $metodoPagamento['valor'],
                    'usa_credito' => $usaCredito,
                ]);
            }

            // Gera crédito de troco se solicitado
            if ($troco > 0 && ($dados['gerar_troco_como_credito'] ?? false)) {
                $creditoTrocoGerado = $this->creditoService->gerarCreditoTroco(
                    $clienteId,
                    $troco,
                    $pagamento->id,
                    'pagamento',
                    Auth::id(),
                    $this->gerarMotivoTroco($dados, $troco)
                );
            }

            // Atualiza o status do orçamento/pedido
            $this->atualizarStatusRegistro($registro, $dados);

            return [
                'sucesso' => true,
                'pagamento' => $pagamento->fresh(['metodos']),
                'creditos_utilizados' => $creditosUtilizados,
                'credito_troco_gerado' => $creditoTrocoGerado,
                'troco' => $troco,
                'valor_final' => $valorFinal,
            ];
        });
    }

    /**
     * Estorna um pagamento completo
     * 
     * @param int $pagamentoId
     * @param string $motivo
     * @return array
     */
    public function estornarPagamento($pagamentoId, $motivo)
    {
        return DB::transaction(function () use ($pagamentoId, $motivo) {
            $pagamento = Pagamento::with(['metodos', 'orcamento', 'pedido'])->findOrFail($pagamentoId);

            // Verifica se já foi estornado
            if ($pagamento->estornado) {
                throw new \Exception('Este pagamento já foi estornado');
            }

            $estornosCredito = [];
            $creditoDevolucao = null;

            // Estorna os créditos utilizados
            $metodosComCredito = $pagamento->metodos()->where('usa_credito', true)->get();
            
            foreach ($metodosComCredito as $metodo) {
                // Busca a movimentação de utilização de crédito relacionada
                $movimentacao = \App\Models\ClienteCreditoMovimentacoes::where('referencia_tipo', 'pagamento')
                    ->where('referencia_id', $pagamento->id)
                    ->where('tipo_movimentacao', 'utilizacao')
                    ->where('valor_movimentado', $metodo->valor)
                    ->first();

                if ($movimentacao) {
                    $resultadoEstorno = $this->creditoService->estornarUtilizacao(
                        $movimentacao->id,
                        Auth::id(),
                        $motivo
                    );

                    $estornosCredito[] = $resultadoEstorno;
                }
            }

            // Se houve pagamento em dinheiro/outros métodos, gera crédito de devolução
            $valorDevolucao = $pagamento->metodos()
                ->where('usa_credito', false)
                ->sum('valor');

            if ($valorDevolucao > 0) {
                $registro = $pagamento->orcamento ?? $pagamento->pedido;
                
                $creditoDevolucao = $this->creditoService->gerarCreditoDevolucao(
                    $registro->cliente_id,
                    $valorDevolucao,
                    $pagamento->id,
                    'pagamento',
                    Auth::id(),
                    "Estorno de pagamento: {$motivo}"
                );
            }

            // Marca o pagamento como estornado
            $pagamento->update([
                'estornado' => true,
                'data_estorno' => now(),
                'motivo_estorno' => $motivo,
                'usuario_estorno_id' => Auth::id(),
            ]);

            // Atualiza o status do orçamento/pedido
            $registro = $pagamento->orcamento ?? $pagamento->pedido;
            if ($registro) {
                $registro->update(['status' => 'pendente']);
            }

            return [
                'sucesso' => true,
                'pagamento' => $pagamento->fresh(),
                'estornos_credito' => $estornosCredito,
                'credito_devolucao' => $creditoDevolucao,
                'valor_devolvido' => $valorDevolucao,
            ];
        });
    }

    /**
     * Valida os dados do pagamento
     */
    protected function validarDadosPagamento(array $dados)
    {
        if (empty($dados['orcamento_id']) && empty($dados['pedido_id'])) {
            throw new \Exception('É necessário informar um orçamento ou pedido');
        }

        if (!empty($dados['orcamento_id']) && !empty($dados['pedido_id'])) {
            throw new \Exception('Não é possível processar orçamento e pedido simultaneamente');
        }

        if (empty($dados['metodos_pagamento'])) {
            throw new \Exception('É necessário informar pelo menos um método de pagamento');
        }

        if (empty($dados['condicao_pagamento_id'])) {
            throw new \Exception('É necessário informar a condição de pagamento');
        }
    }

    /**
     * Busca o registro (orçamento ou pedido)
     */
    protected function buscarRegistro(array $dados)
    {
        if (!empty($dados['orcamento_id'])) {
            return Orcamento::findOrFail($dados['orcamento_id']);
        }

        return Pedido::findOrFail($dados['pedido_id']);
    }

    /**
     * Gera o motivo da utilização de crédito
     */
    protected function gerarMotivoUtilizacaoCredito(array $dados)
    {
        if (!empty($dados['orcamento_id'])) {
            return "Utilização de crédito no orçamento #{$dados['orcamento_id']}";
        }

        return "Utilização de crédito no pedido #{$dados['pedido_id']}";
    }

    /**
     * Gera o motivo do troco
     */
    protected function gerarMotivoTroco(array $dados, $valorTroco)
    {
        $tipo = !empty($dados['orcamento_id']) ? 'orçamento' : 'pedido';
        $id = $dados['orcamento_id'] ?? $dados['pedido_id'];
        
        return "Troco de R$ " . number_format($valorTroco, 2, ',', '.') . " convertido em crédito - {$tipo} #{$id}";
    }

    /**
     * Atualiza o status do orçamento/pedido
     */
    protected function atualizarStatusRegistro($registro, array $dados)
    {
        if ($registro instanceof Orcamento) {
            $registro->update([
                'status' => 'pago',
                'data_pagamento' => now(),
            ]);
        } elseif ($registro instanceof Pedido) {
            $registro->update([
                'status' => 'pago',
                'data_pagamento' => now(),
            ]);
        }
    }

    /**
     * Obtém o histórico de pagamentos de um cliente
     */
    public function getHistoricoPagamentos($clienteId, $limit = 50)
    {
        return Pagamento::whereHas('orcamento', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->orWhereHas('pedido', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->with(['metodos.metodoPagamento', 'condicaoPagamento', 'user'])
            ->orderBy('data_pagamento', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calcula o total pago por um cliente em um período
     */
    public function getTotalPagoNoPeriodo($clienteId, $dataInicio, $dataFim)
    {
        return Pagamento::whereHas('orcamento', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->orWhereHas('pedido', function ($query) use ($clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->where('estornado', false)
            ->sum('valor_final');
    }
}