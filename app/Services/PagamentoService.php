<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\PagamentoForma;
use App\Models\Orcamento;
use App\Models\Pedido;
use App\Models\MetodoPagamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PagamentoService
{
    protected CreditoService $creditoService;
    protected PagamentoPdfService $pdfService;
    protected FaturaService $faturaService;

    public function __construct(
        CreditoService $creditoService,
        PagamentoPdfService $pdfService,
        FaturaService $faturaService
    ) {
        $this->creditoService = $creditoService;
        $this->pdfService     = $pdfService;
        $this->faturaService  = $faturaService;
    }

    /**
     * Salva o pagamento de uma venda (orçamento ou pedido) com validações completas
     * 
     * @param array $dados
     * @return array
     * @throws ValidationException
     * @throws \Exception
     */
   public function salvarPagamentoVenda(array $dados): array
    {
        \Illuminate\Support\Facades\Log::info("Iniciando processo de salvamento de pagamento para Venda", [
            'orcamento_id' => $dados['orcamento_id'] ?? null,
            'pedido_id' => $dados['pedido_id'] ?? null,
            'user_id' => \Illuminate\Support\Facades\Auth::id()
        ]);

        try {
            $dadosValidados = $this->validarDadosPagamento($dados);
     
            // A transaction retorna o array de resultado com o pagamento já salvo.
            $resultado = \Illuminate\Support\Facades\DB::transaction(
                function () use ($dadosValidados, $dados) {
                    $registro  = $this->buscarRegistro($dadosValidados);
                    $clienteId = $registro->cliente_id;
     
                    $this->validarRegistroNaoPago($registro);
     
                    $valoresVenda = $this->calcularValoresVenda($registro, $dadosValidados);
     
                    $this->validarMetodosPagamento(
                        $dadosValidados['metodos_pagamento'],
                        $valoresVenda,
                        $clienteId
                    );
     
                    // Se não for permitido parcial, valida se o valor total foi atingido
                    $finalizarRegistro = filter_var($dadosValidados['finalizar_registro'] ?? ($dados['finalizar_registro'] ?? true), FILTER_VALIDATE_BOOLEAN);
                    $permitirParcial   = filter_var($dadosValidados['permitir_parcial'] ?? ($dados['permitir_parcial'] ?? false), FILTER_VALIDATE_BOOLEAN);

                    \Illuminate\Support\Facades\Log::info("DEBUG: Checagem de Pagamento Parcial", [
                        'finalizar_registro_raw' => $dadosValidados['finalizar_registro'] ?? ($dados['finalizar_registro'] ?? 'N/A'),
                        'finalizar_registro_bool' => $finalizarRegistro,
                        'permitir_parcial_raw' => $dadosValidados['permitir_parcial'] ?? ($dados['permitir_parcial'] ?? 'N/A'),
                        'permitir_parcial_bool' => $permitirParcial,
                    ]);

                    // Se não vai finalizar o registro (caso de faturamento de rota parcial), sempre permite parcial
                    if (!$finalizarRegistro) {
                        $permitirParcial = true;
                    }

                    if (!$permitirParcial) {
                        $this->validarValorPago($dadosValidados['metodos_pagamento'], $valoresVenda);
                    }
     
                    $pagamento = $this->criarPagamento($registro, $dadosValidados, $valoresVenda);
     
                    $resultadoProcessamento = $this->processarMetodosPagamento(
                        $pagamento,
                        $dadosValidados['metodos_pagamento'],
                        $clienteId,
                        $dadosValidados
                    );

                    // Geração automática de faturas para controle financeiro
                    $this->faturaService->gerarFaturasVenda($registro, $dadosValidados);
     
                    $creditoTroco = null;
                    if ($valoresVenda['troco'] > 0 && ($dadosValidados['gerar_troco_como_credito'] ?? false)) {
                        $creditoTroco = $this->processarTrocoComoCredito(
                            $clienteId,
                            $valoresVenda['troco'],
                            $pagamento,
                            $dadosValidados
                        );
                    }
     
                    if ($dadosValidados['finalizar_registro'] ?? true) {
                        $this->atualizarStatusRegistro($registro, 'Pago');
                    }
     
                    return [
                        'sucesso'              => true,
                        'mensagem'             => 'Pagamento salvo com sucesso!',
                        'pagamento'            => $pagamento->fresh(['metodos.metodoPagamento', 'condicaoPagamento']),
                        'creditos_utilizados'  => $resultadoProcessamento['creditos_utilizados'],
                        'credito_troco_gerado' => $creditoTroco,
                        'valores'              => [
                            'valor_total'    => $valoresVenda['valor_total'],
                            'desconto_total' => $valoresVenda['desconto_total'],
                            'valor_final'    => $valoresVenda['valor_final'],
                            'valor_pago'     => $valoresVenda['valor_pago'],
                            'troco'          => $valoresVenda['troco'],
                        ],
                    ];
                }
            );
     
            // ── PDF gerado FORA da transaction ───────────────────────────────────
            // Falha de I/O no PDF não reverte o pagamento já commitado.
            // O caminho fica em $resultado['pagamento']->pdf_path após o gerar().
            $this->pdfService->gerar($resultado['pagamento']);
     
            \Illuminate\Support\Facades\Log::info("Pagamento finalizado com sucesso", [
                'pagamento_id' => $resultado['pagamento']->id,
                'valor' => $resultado['valores']['valor_pago']
            ]);

            return $resultado;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro crítico no salvamento do pagamento", [
                'erro' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'dados' => $dados
            ]);
            throw $e;
        }
    }

    /**
     * Valida todos os dados do pagamento
     */
    protected function validarDadosPagamento(array $dados)
    {
        $regras = [
            'orcamento_id' => 'nullable|required_without:pedido_id|exists:orcamentos,id',
            'pedido_id' => 'nullable|required_without:orcamento_id|exists:pedidos,id',
            'condicao_pagamento_id' => 'required|exists:condicoes_pagamento,id',
            'metodos_pagamento' => 'required|array|min:1',
            'metodos_pagamento.*.metodo_id' => 'required|exists:metodos_pagamento,id',
            'metodos_pagamento.*.valor' => 'required|numeric|min:0.01',
            'metodos_pagamento.*.usa_credito' => 'nullable|boolean',
            'metodos_pagamento.*.parcelas' => 'nullable|integer|min:1|max:12',
            'desconto_balcao' => 'nullable|numeric|min:0',
            'tipo_documento' => 'required|in:cupom_fiscal,nota_fiscal',
            'numero_documento' => 'nullable|string|max:255',
            'cnpj_cpf_nota' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string|max:1000',
            'gerar_troco_como_credito' => 'nullable|boolean',
            'permitir_parcial' => 'nullable|boolean',
            'finalizar_registro' => 'nullable|boolean',
        ];

        $mensagens = [
            'orcamento_id.required_without' => 'É necessário informar um orçamento ou pedido',
            'pedido_id.required_without' => 'É necessário informar um orçamento ou pedido',
            'orcamento_id.exists' => 'Orçamento não encontrado',
            'pedido_id.exists' => 'Pedido não encontrado',
            'condicao_pagamento_id.required' => 'Condição de pagamento é obrigatória',
            'condicao_pagamento_id.exists' => 'Condição de pagamento inválida',
            'metodos_pagamento.required' => 'É necessário informar pelo menos um método de pagamento',
            'metodos_pagamento.min' => 'É necessário informar pelo menos um método de pagamento',
            'metodos_pagamento.*.metodo_id.required' => 'Método de pagamento é obrigatório',
            'metodos_pagamento.*.metodo_id.exists' => 'Método de pagamento inválido',
            'metodos_pagamento.*.valor.required' => 'Valor do método é obrigatório',
            'metodos_pagamento.*.valor.numeric' => 'Valor do método deve ser numérico',
            'metodos_pagamento.*.valor.min' => 'Valor do método deve ser maior que zero',
            'desconto_balcao.numeric' => 'Desconto de balcão deve ser numérico',
            'desconto_balcao.min' => 'Desconto de balcão não pode ser negativo',
            'tipo_documento.required' => 'Tipo de documento é obrigatório',
            'tipo_documento.in' => 'Tipo de documento inválido',
        ];

        $validator = Validator::make($dados, $regras, $mensagens);

        // Validação customizada: não pode ter orçamento E pedido ao mesmo tempo
        $validator->after(function ($validator) use ($dados) {
            if (!empty($dados['orcamento_id']) && !empty($dados['pedido_id'])) {
                $validator->errors()->add(
                    'orcamento_id',
                    'Não é possível processar orçamento e pedido simultaneamente'
                );
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Busca o registro (orçamento ou pedido)
     */
    protected function buscarRegistro(array $dados)
    {
        if (!empty($dados['orcamento_id'])) {
            $registro = Orcamento::with(['cliente', 'itens'])->findOrFail($dados['orcamento_id']);
        } else {
            $registro = Pedido::with(['cliente', 'itens'])->findOrFail($dados['pedido_id']);
        }

        if (!$registro->cliente_id) {
            throw new \Exception('Registro não possui cliente vinculado');
        }

        return $registro;
    }

    /**
     * Valida se o registro ainda não foi pago
     */
    protected function validarRegistroNaoPago($registro)
    {
        $statusPagos = ['pago', 'finalizado', 'concluido'];
        
        if (in_array(strtolower($registro->status), $statusPagos)) {
            throw new \Exception('Este registro já foi pago anteriormente');
        }

        // Verifica se já existe pagamento não estornado para este registro
        $pagamentoExistente = Pagamento::where(function ($query) use ($registro) {
            if ($registro instanceof Orcamento) {
                $query->where('orcamento_id', $registro->id);
            } else {
                $query->where('pedido_id', $registro->id);
            }
        })
        ->where('estornado', false)
        ->exists();

        /* 
        // No faturamento de rota, permitimos múltiplos pagamentos parciais
        if ($pagamentoExistente) {
            throw new \Exception('Já existe um pagamento registrado para este registro');
        }
        */
    }

    /**
     * Calcula todos os valores da venda
     */
    protected function calcularValoresVenda($registro, array $dados)
    {
        // O valorTotal deve ser o bruto (valor_total_itens)
        $valorTotal = (float) ($registro instanceof \App\Models\Orcamento ? $registro->valor_total_itens : $registro->valor_total);
        
        // O desconto_aplicado deve ser o total de descontos aprovados
        $descontoAplicado = (float) ($registro instanceof \App\Models\Orcamento ? $registro->totalDescontosAprovados() : ($registro->desconto ?? 0));
        
        $descontoBalcao = (float) ($dados['desconto_balcao'] ?? 0);
        $descontoTotal = $descontoAplicado + $descontoBalcao;

        // O valor final é Bruto - Desconto Total
        $valorFinal = $valorTotal - $descontoTotal;

        // Valida valor final mínimo
        if ($valorFinal <= 0) {
            throw new \Exception('Valor final da venda deve ser maior que zero');
        }

        // Calcula o valor pago (soma de todos os métodos)
        $valorPago = array_reduce(
            $dados['metodos_pagamento'],
            fn($total, $metodo) => $total + (float) $metodo['valor'],
            0
        );

        // Calcula o troco
        $troco = max(0, $valorPago - $valorFinal);

        return [
            'valor_total' => $valorTotal,
            'desconto_aplicado' => $descontoAplicado,
            'desconto_balcao' => $descontoBalcao,
            'desconto_total' => $descontoTotal,
            'valor_final' => $valorFinal,
            'valor_pago' => $valorPago,
            'troco' => $troco,
        ];
    }

    /**
     * Valida os métodos de pagamento
     */
    protected function validarMetodosPagamento(array $metodosPagamento, array $valoresVenda, $clienteId)
    {
        $valorTotalMetodos = 0;
        $valorCreditosUtilizados = 0;

        foreach ($metodosPagamento as $index => $metodoPag) {
            $metodo = MetodoPagamento::find($metodoPag['metodo_id']);
            
            if (!$metodo) {
                throw new \Exception("Método de pagamento #{$metodoPag['metodo_id']} não encontrado");
            }

            if (!$metodo->ativo) {
                throw new \Exception("Método de pagamento '{$metodo->nome}' está inativo");
            }

            $valorMetodo = (float) $metodoPag['valor'];
            $valorTotalMetodos += $valorMetodo;

            // Valida se está usando crédito
            $usaCredito = $metodoPag['usa_credito'] ?? false;
            
            if ($usaCredito) {
                if (!$metodo->isCreditoCliente()) {
                    throw new \Exception(
                        "Método '{$metodo->nome}' não pode ser usado com crédito do cliente. " .
                        "Use o método 'Crédito do Cliente'."
                    );
                }

                $valorCreditosUtilizados += $valorMetodo;
            }

            // Valida parcelamento
            $parcelas = (int) ($metodoPag['parcelas'] ?? 1);
            
            // Nova Regra de Negócio: Sempre que a forma de pagamento selecionada for 'Cartão de Crédito',
            // o sistema deve exigir que o operador registre a 'quantidade de parcelas'.
            if (stripos($metodo->nome, 'Cartão de Crédito') !== false || $metodo->tipo === 'cartao_credito') {
                if ($parcelas < 1) {
                    throw new \Exception(
                        "Para o método '{$metodo->nome}', a quantidade de parcelas deve ser pelo menos 1."
                    );
                }
            }

            if ($parcelas > 1) {
                if (!$metodo->permite_parcelamento) {
                    throw new \Exception("Método '{$metodo->nome}' não permite parcelamento");
                }

                if ($metodo->max_parcelas && $parcelas > $metodo->max_parcelas) {
                    throw new \Exception(
                        "Método '{$metodo->nome}' permite no máximo {$metodo->max_parcelas} parcelas"
                    );
                }
            }
        }

        // Valida se o cliente tem créditos suficientes
        if ($valorCreditosUtilizados > 0) {
            $saldoDisponivel = $this->creditoService->getSaldoDisponivel($clienteId);
            
            if ($saldoDisponivel < $valorCreditosUtilizados) {
                throw new \Exception(
                    'Créditos insuficientes. ' .
                    'Necessário: R$ ' . number_format($valorCreditosUtilizados, 2, ',', '.') . ' | ' .
                    'Disponível: R$ ' . number_format($saldoDisponivel, 2, ',', '.')
                );
            }
        }
    }

    /**
     * Valida se o valor pago é suficiente
     */
    protected function validarValorPago(array $metodosPagamento, array $valoresVenda)
    {
        $valorTotalPago = array_reduce(
            $metodosPagamento,
            fn($total, $metodo) => $total + (float) $metodo['valor'],
            0
        );

        $valorFinal = $valoresVenda['valor_final'];

        // Permite uma margem de erro de R$ 0.01 por questões de arredondamento
        $margemErro = 0.01;

        if ($valorTotalPago < ($valorFinal - $margemErro)) {
            $faltando = $valorFinal - $valorTotalPago;
            
            throw new \Exception(
                'Valor pago insuficiente! ' .
                'Valor da venda: R$ ' . number_format($valorFinal, 2, ',', '.') . ' | ' .
                'Valor pago: R$ ' . number_format($valorTotalPago, 2, ',', '.') . ' | ' .
                'Faltando: R$ ' . number_format($faltando, 2, ',', '.')
            );
        }

        // Opcional: pode validar se o valor pago não é excessivamente maior
        $diferencaMaxima = 1000.00; // R$ 1.000 de diferença máxima
        
        if ($valorTotalPago > ($valorFinal + $diferencaMaxima)) {
            throw new \Exception(
                'Valor pago muito maior que o valor da venda. ' .
                'Verifique os valores informados.'
            );
        }
    }

    /**
     * Cria o registro do pagamento
     */
    protected function criarPagamento($registro, array $dados, array $valores)
    {
        return Pagamento::create([
            'orcamento_id' => $dados['orcamento_id'] ?? null,
            'pedido_id' => $dados['pedido_id'] ?? null,
            'condicao_pagamento_id' => $dados['condicao_pagamento_id'],
            'desconto_aplicado' => $valores['desconto_aplicado'],
            'desconto_balcao' => $valores['desconto_balcao'],
            'valor_final' => $valores['valor_final'],
            'valor_pago' => $valores['valor_pago'],
            'troco' => $valores['troco'],
            'data_pagamento' => now(),
            'tipo_documento' => $dados['tipo_documento'],
            'numero_documento' => $dados['numero_documento'] ?? null,
            'cnpj_cpf_nota' => $dados['cnpj_cpf_nota'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Processa os métodos de pagamento e utilização de créditos
     */
    protected function processarMetodosPagamento($pagamento, array $metodosPagamento, $clienteId, array $dados)
    {
        $creditosUtilizados = [];

        foreach ($metodosPagamento as $metodoPag) {
            $usaCredito = $metodoPag['usa_credito'] ?? false;

            // Se usa crédito, processa a utilização
            if ($usaCredito) {
                $resultadoCredito = $this->creditoService->utilizarCreditos(
                    $clienteId,
                    $metodoPag['valor'],
                    $pagamento->id,
                    $dados['orcamento_id'] ? 'orcamento' : 'pedido',
                    Auth::id(),
                    $this->gerarMotivoUtilizacaoCredito($dados, $metodoPag['valor'])
                );

                $creditosUtilizados = array_merge(
                    $creditosUtilizados,
                    $resultadoCredito['creditos_utilizados'] ?? []
                );
            }

            // Registra o método de pagamento usado
            $parcelas = (int) ($metodoPag['parcelas'] ?? 1);
            $valorParcela = $parcelas > 1 ? round($metodoPag['valor'] / $parcelas, 2) : null;

            PagamentoForma::create([
                'pagamento_id' => $pagamento->id,
                'condicao_pagamento_id' => $metodoPag['metodo_id'],
                'valor' => $metodoPag['valor'],
                'usa_credito' => $usaCredito,
                'parcelas' => $parcelas,
                'valor_parcela' => $valorParcela,
                'observacoes' => $metodoPag['observacoes'] ?? null,
            ]);
        }

        return [
            'creditos_utilizados' => $creditosUtilizados,
        ];
    }

    /**
     * Processa o troco como crédito
     */
    protected function processarTrocoComoCredito($clienteId, $valorTroco, $pagamento, array $dados)
    {
        return $this->creditoService->gerarCreditoTroco(
            $clienteId,
            $valorTroco,
            $pagamento->id,
            'pagamento',
            Auth::id(),
            $this->gerarMotivoTroco($dados, $valorTroco)
        );
    }

    /**
     * Gera o motivo da utilização de crédito
     */
    protected function gerarMotivoUtilizacaoCredito(array $dados, $valor)
    {
        $tipo = !empty($dados['orcamento_id']) ? 'orçamento' : 'pedido';
        $id = $dados['orcamento_id'] ?? $dados['pedido_id'];
        
        return "Utilização de R$ " . number_format($valor, 2, ',', '.') . 
               " em crédito - {$tipo} #{$id}";
    }

    /**
     * Gera o motivo do troco
     */
    protected function gerarMotivoTroco(array $dados, $valorTroco)
    {
        $tipo = !empty($dados['orcamento_id']) ? 'orçamento' : 'pedido';
        $id = $dados['orcamento_id'] ?? $dados['pedido_id'];
        
        return "Troco de R$ " . number_format($valorTroco, 2, ',', '.') . 
               " convertido em crédito - {$tipo} #{$id}";
    }

    /**
     * Atualiza o status do orçamento/pedido
     */
    protected function atualizarStatusRegistro($registro, $novoStatus)
    {
        $registro->update([
            'status' => $novoStatus,
            'data_pagamento' => now(),
        ]);

        if ($registro instanceof Orcamento && in_array(strtolower($novoStatus), ['pago', 'finalizado'])) {
            \App\Events\OrcamentoPago::dispatch($registro);
        }
    }

    public function estornarPagamento($pagamentoId, $motivo, bool $gerarDevolucao = true)
    {
        // Se for um objeto de modelo, extrai o ID
        if (is_object($pagamentoId) && method_exists($pagamentoId, 'getKey')) {
            $pagamentoId = $pagamentoId->getKey();
        }

        \Illuminate\Support\Facades\Log::info("Iniciando estorno de pagamento", [
            'pagamento_id' => $pagamentoId,
            'motivo' => (string) $motivo,
            'user_id' => \Illuminate\Support\Facades\Auth::id()
        ]);

        if (strlen($motivo) < 10) {
            throw new \Exception('O motivo do estorno deve ter pelo menos 10 caracteres');
        }

        try {
            return DB::transaction(function () use ($pagamentoId, $motivo, $gerarDevolucao) {
                $pagamento = Pagamento::with(['metodos', 'orcamento', 'pedido'])->findOrFail($pagamentoId);

                if ($pagamento->estornado) {
                    throw new \Exception('Este pagamento já foi estornado anteriormente');
                }

                $estornosCredito = [];
                $creditoDevolucao = null;

                // Estorna os créditos utilizados
                $metodosComCredito = $pagamento->metodos()->where('usa_credito', true)->get();
                
                foreach ($metodosComCredito as $metodo) {
                    $movimentacoes = \App\Models\ClienteCreditoMovimentacoes::where('referencia_tipo', 'pagamento')
                        ->where('referencia_id', $pagamento->id)
                        ->where('tipo_movimentacao', 'utilizacao')
                        ->get();

                    foreach ($movimentacoes as $movimentacao) {
                        $resultadoEstorno = $this->creditoService->estornarUtilizacao(
                            $movimentacao->id,
                            Auth::id(),
                            "Estorno de pagamento: {$motivo}"
                        );

                        $estornosCredito[] = $resultadoEstorno;
                    }
                }

                // Gera crédito de devolução para outros métodos
                $valorDevolucao = $pagamento->metodos()
                    ->where('usa_credito', false)
                    ->sum('valor');

                if ($gerarDevolucao && $valorDevolucao > 0) {
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

                // Atualiza o status do registro
                $registro = $pagamento->orcamento ?? $pagamento->pedido;
                if ($registro) {
                    $this->atualizarStatusRegistro($registro, 'pendente');
                }

                \Illuminate\Support\Facades\Log::info("Estorno de pagamento realizado com sucesso", [
                    'pagamento_id' => $pagamento->id,
                    'status_venda' => 'pendente'
                ]);

                return [
                    'sucesso' => true,
                    'mensagem' => 'Pagamento estornado com sucesso!',
                    'pagamento' => $pagamento->fresh(),
                    'estornos_credito' => $estornosCredito,
                    'credito_devolucao' => $creditoDevolucao,
                    'valor_devolvido' => $valorDevolucao,
                ];
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro crítico no estorno de pagamento", [
                'pagamento_id' => $pagamentoId,
                'erro' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}