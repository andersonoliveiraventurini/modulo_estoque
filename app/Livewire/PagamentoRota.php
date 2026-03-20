<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\MetodoPagamento;
use App\Models\RouteBillingApproval;
use App\Models\User;
use App\Notifications\RouteBillingDeniedNotification;
use App\Services\PagamentoService;
use App\Services\CreditoService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PagamentoRota extends Component
{
    // ─── Orçamento ──────────────────────────────────────────────────────────
    public $orcamentoId;
    public $orcamento;

    // ─── Pagamento ──────────────────────────────────────────────────────────
    public $formasPagamento      = [];
    public $condicoesPagamento   = [];
    public $valorPago            = 0;
    public $valorComDesconto     = 0;
    public $saldoDisponivel      = 0;
    public $abaterCredito        = false;
    public $valorCreditoAbatido  = 0;
    public $troco                = 0;
    public $isBlocked            = false;

    // ─── Faturamento de Rota ────────────────────────────────────────────────
    /** approved | restrictions | rejected */
    public $billingStatus = 'approved';
    public $billingComments = '';

    // ─── Documento fiscal ───────────────────────────────────────────────────
    public $precisaNotaFiscal   = false;
    public $notaOutroCnpjCpf    = false;
    public $cnpjCpfNota         = '';

    // ─── Serviços ────────────────────────────────────────────────────────────
    protected $pagamentoService;
    protected $creditoService;

    public function boot(PagamentoService $pagamentoService, CreditoService $creditoService)
    {
        $this->pagamentoService = $pagamentoService;
        $this->creditoService   = $creditoService;
    }

    public function mount($orcamentoId)
    {
        $this->orcamentoId = $orcamentoId;
        $this->carregarOrcamento();
        $this->carregarCondicoesPagamento();
        $this->carregarSaldoCredito();
        $this->inicializarFormaPagamento();
        $this->calcularValores();

        // Apenas Financeiro pode acessar esta tela
        $this->authorize('viewBilling', $this->orcamento);

        Log::info("PagamentoRota: tela de faturamento acessada para orçamento #{$this->orcamentoId}", [
            'user_id' => Auth::id(),
        ]);
    }

    // ─── Carregamento ────────────────────────────────────────────────────────

    public function carregarOrcamento(): void
    {
        $this->orcamento = Orcamento::with([
            'cliente',
            'vendedor',
            'condicaoPagamento',
            'itens',
            'transportes',
            'routeBillingApprovals.user',
            'routeBillingAttachments',
        ])->findOrFail($this->orcamentoId);

        $this->isBlocked = $this->orcamento->cliente->bloqueado ?? false;
    }

    public function carregarCondicoesPagamento(): void
    {
        $query = MetodoPagamento::ativos()->ordenado();

        if ($this->isBlocked) {
            $query->whereIn('tipo', ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']);
        }

        $this->condicoesPagamento = $query->get();
    }

    public function carregarSaldoCredito(): void
    {
        if ($this->orcamento && $this->orcamento->cliente_id) {
            $this->saldoDisponivel = $this->creditoService->getSaldoDisponivel($this->orcamento->cliente_id);
        }
    }

    public function inicializarFormaPagamento(): void
    {
        $this->formasPagamento = [[
            'condicao_id' => '',
            'valor'       => 0,
            'parcelas'    => 1,
        ]];
    }

    // ─── Formas de pagamento ─────────────────────────────────────────────────

    public function adicionarFormaPagamento(): void
    {
        $this->formasPagamento[] = [
            'condicao_id' => '',
            'valor'       => 0,
            'parcelas'    => 1,
        ];
    }

    public function removerFormaPagamento(int $index): void
    {
        if (count($this->formasPagamento) > 1) {
            unset($this->formasPagamento[$index]);
            $this->formasPagamento = array_values($this->formasPagamento);
            $this->calcularValores();
        }
    }

    public function preencherRestante(): void
    {
        $restante = $this->valorComDesconto - $this->valorPago;
        if ($restante > 0) {
            $ultimoIndex = count($this->formasPagamento) - 1;
            $this->formasPagamento[$ultimoIndex]['valor'] = number_format($restante, 2, '.', '');
            $this->calcularValores();
        }
    }

    // ─── Cálculo de valores ───────────────────────────────────────────────────

    public function updated($propertyName): void
    {
        if (
            str_starts_with($propertyName, 'formasPagamento') ||
            $propertyName === 'abaterCredito'
        ) {
            $this->calcularValores();
        }
    }

    public function calcularValores(): void
    {
        $valorTotal = $this->orcamento->valor_total_itens ?? 0;
        $this->valorComDesconto = max(0, $valorTotal - $this->orcamento->totalDescontosAprovados());

        $this->valorCreditoAbatido = 0;
        if ($this->abaterCredito && $this->saldoDisponivel > 0) {
            $this->valorCreditoAbatido = min($this->saldoDisponivel, $this->valorComDesconto);
        }

        $this->valorPago = 0;
        foreach ($this->formasPagamento as $forma) {
            $this->valorPago += (float) ($forma['valor'] ?? 0);
        }

        $totalGeral = $this->valorPago + $this->valorCreditoAbatido;
        $this->troco = max(0, $totalGeral - $this->valorComDesconto);
    }

    // ─── Finalizar pagamento + aprovação Financeiro ───────────────────────────

    /**
     * Registra o pagamento E grava a decisão de faturamento do Financeiro.
     * Para aprovação com restrição ou rejeição, o pagamento não é finalizado —
     * apenas a decisão de faturamento é persistida.
     */
    public function finalizarPagamento(): void
    {
        $this->authorize('approve', $this->orcamento);

        Log::info("PagamentoRota: iniciando finalização de pagamento", [
            'orcamento_id'   => $this->orcamentoId,
            'billing_status' => $this->billingStatus,
            'user_id'        => Auth::id(),
        ]);

        try {
            $this->validate([
                'billingStatus'   => 'required|in:approved,restrictions,rejected',
                'billingComments' => 'nullable|string|max:2000',
            ]);

            DB::transaction(function () {
                // 1. Registra a decisão de faturamento
                RouteBillingApproval::create([
                    'orcamento_id' => $this->orcamentoId,
                    'user_id'      => Auth::id(),
                    'status'       => $this->billingStatus,
                    'comments'     => $this->billingComments,
                ]);

                Log::info("PagamentoRota: decisão de faturamento registrada", [
                    'orcamento_id' => $this->orcamentoId,
                    'status'       => $this->billingStatus,
                ]);

                // 2. Para negação, dispara notificações e NÃO salva pagamento
                if ($this->billingStatus === 'rejected') {
                    $this->dispararNotificacoesNegacao();
                    return; // Sai sem salvar pagamento
                }

                // 3. Salva o pagamento (approved ou restrictions)
                $this->validarDadosPagamento();
                $dadosPagamento = $this->prepararDadosPagamento();
                $resultado = $this->pagamentoService->salvarPagamentoVenda($dadosPagamento);

                Log::info("PagamentoRota: pagamento salvo com sucesso", [
                    'pagamento_id' => $resultado['pagamento']->id,
                ]);
            });

            $label = match ($this->billingStatus) {
                'approved'     => 'Faturamento aprovado e pagamento registrado!',
                'restrictions' => 'Aprovado com restrição. Pagamento registrado para entrega.',
                'rejected'     => 'Faturamento negado. Todos os envolvidos foram notificados.',
            };

            session()->flash('success', $label);
            $this->dispatch('pagamento-rota-finalizado');
            $this->redirect(route('orcamentos.rota_pagamento_lista'));

        } catch (ValidationException $e) {
            Log::warning("PagamentoRota: validação falhou", ['erros' => $e->errors(), 'orcamento_id' => $this->orcamentoId]);
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        } catch (\Exception $e) {
            Log::error("PagamentoRota: erro ao finalizar pagamento", [
                'orcamento_id' => $this->orcamentoId,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);
            $this->addError('geral', 'Erro ao processar: ' . $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    protected function validarDadosPagamento(): void
    {
        $formasValidas = array_filter(
            $this->formasPagamento,
            fn ($f) => !empty($f['condicao_id']) && (float)($f['valor'] ?? 0) > 0
        );

        if (empty($formasValidas)) {
            throw new \Exception('Adicione pelo menos uma forma de pagamento válida.');
        }

        // Proteção para cliente bloqueado
        if ($this->isBlocked) {
            foreach ($formasValidas as $forma) {
                $metodo = MetodoPagamento::find($forma['condicao_id']);
                if ($metodo && !in_array($metodo->tipo, ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito'])) {
                    throw new \Exception('Cliente bloqueado: Pagamento restrito a PIX, Dinheiro ou Cartão de Crédito/Débito.');
                }
            }
        }

        $totalPago = $this->valorPago + $this->valorCreditoAbatido;
        if ($totalPago < $this->valorComDesconto - 0.01) {
            $faltando = $this->valorComDesconto - $totalPago;
            throw new \Exception('Valor pago insuficiente. Falta: R$ ' . number_format($faltando, 2, ',', '.'));
        }
    }

    protected function prepararDadosPagamento(): array
    {
        $formasValidas = array_filter(
            $this->formasPagamento,
            fn ($f) => !empty($f['condicao_id']) && (float)($f['valor'] ?? 0) > 0
        );

        $metodosPagamento = [];

        if ($this->abaterCredito && $this->valorCreditoAbatido > 0) {
            $metodoCredito = MetodoPagamento::where('tipo', 'credito_cliente')->first();
            if ($metodoCredito) {
                $metodosPagamento[] = [
                    'metodo_id'  => $metodoCredito->id,
                    'valor'      => (float) $this->valorCreditoAbatido,
                    'usa_credito' => true,
                    'parcelas'   => 1,
                ];
            }
        }

        foreach ($formasValidas as $forma) {
            $metodosPagamento[] = [
                'metodo_id'  => $forma['condicao_id'],
                'valor'      => (float) $forma['valor'],
                'usa_credito' => false,
                'parcelas'   => (int) ($forma['parcelas'] ?? 1),
            ];
        }

        return [
            'orcamento_id'         => $this->orcamentoId,
            'condicao_pagamento_id' => $this->orcamento->condicao_id ?? 1,
            'metodos_pagamento'    => $metodosPagamento,
            'desconto_balcao'      => 0,
            'tipo_documento'       => $this->precisaNotaFiscal ? 'nota_fiscal' : 'cupom_fiscal',
            'cnpj_cpf_nota'        => $this->notaOutroCnpjCpf ? $this->cnpjCpfNota : null,
            'observacoes'          => $this->billingStatus === 'restrictions'
                ? 'Aprovado com restrição — RECEBER PAGAMENTO NA ENTREGA'
                : null,
            'gerar_troco_como_credito' => false,
        ];
    }

    protected function dispararNotificacoesNegacao(): void
    {
        $motivo     = $this->billingComments ?: 'Sem motivo informado';
        $deniedBy   = Auth::user()->name;
        $orcamento  = $this->orcamento;
        $notificacao = new RouteBillingDeniedNotification($orcamento, $motivo, $deniedBy);

        // Notificar: vendedor do pedido
        $orcamento->vendedor?->notify($notificacao);

        // Notificar: supervisores de vendas, separação, conferência
        $roles = ['Supervisor de Vendas', 'Separação', 'Conferência'];
        User::role($roles)->each(fn ($u) => $u->notify($notificacao));

        Log::info("PagamentoRota: notificações de negação enviadas", [
            'orcamento_id' => $this->orcamentoId,
            'roles'        => $roles,
        ]);
    }

    public function render()
    {
        return view('livewire.pagamento-rota');
    }
}
