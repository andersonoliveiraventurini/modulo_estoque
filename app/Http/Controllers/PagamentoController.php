<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\PagamentoForma;
use App\Models\Orcamento;
use App\Models\Pedido;
use App\Models\MetodoPagamento;
use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagamentoController extends Controller
{
    /**
     * Exibe o formulário de pagamento de um orçamento
     */
    public function formPagamentoOrcamento($orcamentoId)
    {
        $orcamento = Orcamento::with(['cliente', 'vendedor', 'condicaoPagamento', 'itens'])
            ->findOrFail($orcamentoId);

        $metodosPagamento = MetodoPagamento::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();
        
        return view('paginas.pagamentos.form-pagamento-balcao', compact('orcamento', 'metodosPagamento'));
    }

    /**
     * Salva o pagamento de um orçamento
     */
    public function salvarPagamentoOrcamento(Request $request, $orcamentoId)
    {
        // Validação dos dados
        try {
            $validated = $request->validate([
                'formas_pagamento' => 'required|array|min:1',
                'formas_pagamento.*.metodo_id' => 'required|exists:metodos_pagamento,id',
                'formas_pagamento.*.valor' => 'required|numeric|min:0.01',
                'desconto_balcao' => 'nullable|numeric|min:0',
                'precisa_nota_fiscal' => 'nullable|boolean',
                'cnpj_cpf_nota' => 'nullable|string|max:20',
                'observacoes' => 'nullable|string|max:1000',
            ], [
                'formas_pagamento.required' => 'É necessário informar pelo menos uma forma de pagamento',
                'formas_pagamento.*.metodo_id.required' => 'Método de pagamento é obrigatório',
                'formas_pagamento.*.metodo_id.exists' => 'Método de pagamento inválido',
                'formas_pagamento.*.valor.required' => 'Valor é obrigatório',
                'formas_pagamento.*.valor.numeric' => 'Valor deve ser numérico',
                'formas_pagamento.*.valor.min' => 'Valor deve ser maior que zero',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        try {
            return DB::transaction(function () use ($request, $orcamentoId, $validated) {
                // Busca o orçamento
                $orcamento = Orcamento::with(['cliente'])->findOrFail($orcamentoId);

                // Verifica se já foi pago
                $pagamentoExistente = Pagamento::where('orcamento_id', $orcamentoId)
                    ->where('estornado', false)
                    ->exists();

                if ($pagamentoExistente) {
                    return back()
                        ->withErrors(['erro' => 'Este orçamento já possui um pagamento registrado.'])
                        ->withInput();
                }

                // Calcula valores
                $valorTotal = $orcamento->valor_total_itens;
                $descontoOriginal = $orcamento->desconto ?? 0;
                $descontoBalcao = (float) ($request->desconto_balcao ?? 0);
                
                // Valida desconto de balcão (máximo 3%)
                $maxDescontoBalcao = $valorTotal * 0.03;
                if ($descontoBalcao > $maxDescontoBalcao) {
                    return back()
                        ->withErrors(['desconto_balcao' => 'Desconto de balcão não pode ser maior que 3% do valor total.'])
                        ->withInput();
                }

                $descontoTotal = $descontoOriginal + $descontoBalcao;
                $valorFinal = $valorTotal - $descontoTotal;

                // Calcula valor pago total
                $valorPago = 0;
                foreach ($validated['formas_pagamento'] as $forma) {
                    $valorPago += (float) $forma['valor'];
                }

                // Valida se o valor pago é suficiente
                if ($valorPago < $valorFinal) {
                    $faltando = $valorFinal - $valorPago;
                    return back()
                        ->withErrors(['erro' => 'Valor pago insuficiente! Falta: R$ ' . number_format($faltando, 2, ',', '.')])
                        ->withInput();
                }

                $troco = max(0, $valorPago - $valorFinal);

                // Cria o registro de pagamento
                $pagamento = Pagamento::create([
                    'orcamento_id' => $orcamentoId,
                    'condicao_pagamento_id' => $orcamento->condicao_pagamento_id ?? 1,
                    'desconto_aplicado' => $descontoOriginal,
                    'desconto_balcao' => $descontoBalcao,
                    'valor_final' => $valorFinal,
                    'valor_pago' => $valorPago,
                    'troco' => $troco,
                    'data_pagamento' => now(),
                    'tipo_documento' => $request->boolean('precisa_nota_fiscal') ? 'nota_fiscal' : 'cupom_fiscal',
                    'cnpj_cpf_nota' => $request->cnpj_cpf_nota,
                    'observacoes' => $request->observacoes,
                    'user_id' => Auth::id(),
                ]);

                // Salva as formas de pagamento
                $creditosUtilizados = [];
                foreach ($validated['formas_pagamento'] as $forma) {
                    $metodo = MetodoPagamento::find($forma['metodo_id']);
                    $usaCredito = $metodo && $metodo->tipo === 'credito_cliente';

                    // Se usa crédito, processa a utilização
                    if ($usaCredito) {
                        $resultadoCredito = $this->utilizarCreditos(
                            $orcamento->cliente_id,
                            $forma['valor'],
                            $pagamento->id,
                            'orcamento'
                        );
                        $creditosUtilizados[] = $resultadoCredito;
                    }

                    // Registra a forma de pagamento
                    PagamentoForma::create([
                        'pagamento_id' => $pagamento->id,
                        'metodo_pagamento_id' => $forma['metodo_id'],
                        'valor' => $forma['valor'],
                        'usa_credito' => $usaCredito,
                        'parcelas' => 1,
                        'observacoes' => null,
                    ]);
                }

                // Atualiza o status do orçamento
                // Ajuste o status conforme os valores aceitos na sua tabela
                // Valores comuns: 'aprovado', 'finalizado', 'concluido', 'pago'
                $orcamento->update([
                    'status' => 'Pago', // Altere para o status correto da sua aplicação
                    'data_pagamento' => now(),
                ]);

                return redirect()->route('orcamentos.index')
                    ->with('success', 'Pagamento realizado com sucesso! Número do pagamento: #' . $pagamento->id);
            });

        } catch (\Exception $e) {
            return back()
                ->withErrors(['erro' => 'Erro ao processar pagamento: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Utiliza créditos do cliente (FIFO)
     */
    protected function utilizarCreditos($clienteId, $valorUtilizar, $pagamentoId, $referenciaTipo)
    {
        $valorRestante = $valorUtilizar;
        $creditosUtilizados = [];

        // Busca créditos disponíveis (FIFO)
        $creditos = ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(function($query) {
                $query->whereNull('data_validade')
                      ->orWhere('data_validade', '>=', now());
            })
            ->orderBy('data_validade', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

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
            ClienteCreditoMovimentacoes::create([
                'credito_id' => $credito->id,
                'cliente_id' => $clienteId,
                'tipo_movimentacao' => 'utilizacao',
                'valor_movimentado' => $valorUsar,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'motivo' => "Utilização de crédito no pagamento #{$pagamentoId}",
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $pagamentoId,
                'usuario_id' => Auth::id(),
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
            ];

            $valorRestante -= $valorUsar;
        }

        return $creditosUtilizados;
    }

    /**
     * Exibe os detalhes de um pagamento
     */
    public function show($id)
    {
        $pagamento = Pagamento::with([
            'orcamento.cliente',
            'formas.metodoPagamento',
            'user'
        ])->findOrFail($id);

        return view('paginas.pagamentos.show', compact('pagamento'));
    }

    /**
     * Estorna um pagamento
     */
    public function estornar(Request $request, $id)
    {
        $request->validate([
            'motivo_estorno' => 'required|string|min:10',
        ], [
            'motivo_estorno.required' => 'O motivo do estorno é obrigatório',
            'motivo_estorno.min' => 'O motivo deve ter pelo menos 10 caracteres',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $pagamento = Pagamento::with(['formas', 'orcamento'])->findOrFail($id);

                if ($pagamento->estornado) {
                    return back()->withErrors(['erro' => 'Este pagamento já foi estornado anteriormente.']);
                }

                // Estorna os créditos utilizados
                $formasComCredito = $pagamento->formas()->where('usa_credito', true)->get();
                
                foreach ($formasComCredito as $forma) {
                    $movimentacoes = ClienteCreditoMovimentacoes::where('referencia_tipo', 'orcamento')
                        ->where('referencia_id', $pagamento->id)
                        ->where('tipo_movimentacao', 'utilizacao')
                        ->get();

                    foreach ($movimentacoes as $movimentacao) {
                        $credito = ClienteCreditos::find($movimentacao->credito_id);
                        $valorEstornar = $movimentacao->valor_movimentado;
                        $saldoAnterior = $credito->valor_disponivel;
                        $saldoPosterior = $saldoAnterior + $valorEstornar;

                        // Cria movimentação de estorno
                        ClienteCreditoMovimentacoes::create([
                            'credito_id' => $credito->id,
                            'cliente_id' => $credito->cliente_id,
                            'tipo_movimentacao' => 'estorno',
                            'valor_movimentado' => $valorEstornar,
                            'saldo_anterior' => $saldoAnterior,
                            'saldo_posterior' => $saldoPosterior,
                            'motivo' => "Estorno de pagamento: {$request->motivo_estorno}",
                            'referencia_tipo' => 'orcamento',
                            'referencia_id' => $pagamento->id,
                            'usuario_id' => Auth::id(),
                        ]);

                        // Atualiza o crédito
                        $credito->valor_disponivel = $saldoPosterior;
                        if ($credito->status === 'utilizado' && $saldoPosterior > 0) {
                            $credito->status = 'ativo';
                        }
                        $credito->save();
                    }
                }

                // Marca o pagamento como estornado
                $pagamento->update([
                    'estornado' => true,
                    'data_estorno' => now(),
                    'motivo_estorno' => $request->motivo_estorno,
                    'usuario_estorno_id' => Auth::id(),
                ]);

                // Atualiza o status do orçamento
                if ($pagamento->orcamento) {
                    $pagamento->orcamento->update(['status' => 'pendente']); // ou 'em_andamento', conforme sua aplicação
                }

                return redirect()->route('pagamentos.show', $pagamento->id)
                    ->with('success', 'Pagamento estornado com sucesso!');
            });

        } catch (\Exception $e) {
            return back()->withErrors(['erro' => 'Erro ao estornar pagamento: ' . $e->getMessage()]);
        }
    }
}