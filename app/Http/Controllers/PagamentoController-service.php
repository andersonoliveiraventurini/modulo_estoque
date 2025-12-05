<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagamentoRequest;
use App\Http\Requests\UpdatePagamentoRequest;
use App\Models\CondicoesPagamento;
use App\Models\Orcamento;
use App\Models\Pagamento;

use App\Services\PagamentoService;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PagamentoController extends Controller
{
    protected $pagamentoService;
    protected $creditoService;

    public function __construct(
        PagamentoService $pagamentoService,
        CreditoService $creditoService
    ) {
        $this->pagamentoService = $pagamentoService;
        $this->creditoService = $creditoService;
    }

    /**
     * Processa o pagamento de um orçamento ou pedido
     */
    public function processar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orcamento_id' => 'nullable|exists:orcamentos,id',
            'pedido_id' => 'nullable|exists:pedidos,id',
            'condicao_pagamento_id' => 'required|exists:condicoes_pagamento,id',
            'metodos_pagamento' => 'required|array|min:1',
            'metodos_pagamento.*.metodo_id' => 'required|exists:metodos_pagamento,id',
            'metodos_pagamento.*.valor' => 'required|numeric|min:0.01',
            'metodos_pagamento.*.usa_credito' => 'nullable|boolean',
            'desconto_balcao' => 'nullable|numeric|min:0',
            'valor_pago' => 'required|numeric|min:0',
            'tipo_documento' => 'required|in:cupom_fiscal,nota_fiscal',
            'numero_documento' => 'nullable|string|max:255',
            'cnpj_cpf_nota' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string',
            'gerar_troco_como_credito' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'erros' => $validator->errors()
            ], 422);
        }

        try {
            $resultado = $this->pagamentoService->processarPagamento($request->all());

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Pagamento processado com sucesso',
                'dados' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Estorna um pagamento
     */
    public function estornar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'erros' => $validator->errors()
            ], 422);
        }

        try {
            $resultado = $this->pagamentoService->estornarPagamento($id, $request->motivo);

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Pagamento estornado com sucesso',
                'dados' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Consulta o saldo de créditos de um cliente
     */
    public function consultarCreditos($clienteId)
    {
        try {
            $saldoDisponivel = $this->creditoService->getSaldoDisponivel($clienteId);
            $creditosAtivos = $this->creditoService->getCreditosAtivos($clienteId);

            return response()->json([
                'sucesso' => true,
                'saldo_disponivel' => $saldoDisponivel,
                'saldo_formatado' => 'R$ ' . number_format($saldoDisponivel, 2, ',', '.'),
                'creditos_ativos' => $creditosAtivos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtém o histórico de pagamentos de um cliente
     */
    public function historicoPagamentos($clienteId)
    {
        try {
            $historico = $this->pagamentoService->getHistoricoPagamentos($clienteId);

            return response()->json([
                'sucesso' => true,
                'historico' => $historico,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtém o histórico de movimentações de créditos de um cliente
     */
    public function historicoCreditos($clienteId)
    {
        try {
            $historico = $this->creditoService->getHistoricoMovimentacoes($clienteId);

            return response()->json([
                'sucesso' => true,
                'historico' => $historico,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Gera crédito de bonificação para um cliente
     */
    public function gerarBonificacao(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'valor' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'erros' => $validator->errors()
            ], 422);
        }

        try {
            $credito = $this->creditoService->gerarCreditoBonificacao(
                $request->cliente_id,
                $request->valor,
                auth()->id(),
                $request->motivo
            );

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Bonificação gerada com sucesso',
                'credito' => $credito
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancela um crédito
     */
    public function cancelarCredito(Request $request, $creditoId)
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'erros' => $validator->errors()
            ], 422);
        }

        try {
            $credito = $this->creditoService->cancelarCredito(
                $creditoId,
                auth()->id(),
                $request->motivo
            );

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Crédito cancelado com sucesso',
                'credito' => $credito
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function create() {}

    /**
     * Show the form for creating a new resource.
     */
    public function realizar_pagamento($orcamento_id)
    {
        $orcamento = Orcamento::findOrFail($orcamento_id);
        $condicoesPagamento = CondicoesPagamento::all();
        return view('paginas.pagamentos.create', compact('orcamento', 'condicoesPagamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePagamentoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pagamento $pagamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pagamento $pagamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePagamentoRequest $request, Pagamento $pagamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pagamento $pagamento)
    {
        //
    }
}
