<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\Pagamento;
use App\Models\MetodoPagamento;
use App\Models\RouteBillingApproval;
use App\Models\RouteBillingAttachment;
use App\Models\User;
use App\Notifications\RouteBillingDeniedNotification;
use App\Services\PagamentoService;
use App\Services\CreditoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class PagamentoRota extends Component
{
    use WithFileUploads;

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
    public $valorJaPago          = 0;
    public $pagamentosExistentes = [];
    public $troco                = 0;
    public $isBlocked            = false;

    // ─── Faturamento de Rota ────────────────────────────────────────────────
    /** approved | restrictions | rejected */
    public $billingStatus = '';
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
            'pagamentos.metodos.metodoPagamento',
        ])->findOrFail($this->orcamentoId);

        $this->isBlocked = $this->orcamento->cliente->bloqueado ?? false;

        // Popula as formas de pagamento com os dados reais do banco para edição
        $this->formasPagamento = [];
        $pagamentos = $this->orcamento->pagamentos()->ativos()->get();
        
        foreach ($pagamentos as $pag) {
            $pag->load('metodos', 'routeBillingAttachments');
            foreach ($pag->metodos as $metodo) {
                $attachment = $pag->routeBillingAttachments->first();
                $this->formasPagamento[] = [
                    'pagamento_id' => $pag->id,
                    'condicao_id'  => $metodo->condicao_pagamento_id,
                    'valor'        => (float) $metodo->valor,
                    'parcelas'     => 1,
                    'comprovante'  => null,
                    'comprovante_url'  => $attachment ? Storage::url($attachment->file_path) : null,
                    'comprovante_nome' => $attachment ? basename($attachment->file_path) : null,
                ];
            }
        }

        if (empty($this->formasPagamento)) {
            $this->adicionarFormaPagamento();
        }
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

    // ─── Formas de pagamento ─────────────────────────────────────────────────

    public function adicionarFormaPagamento(): void
    {
        $this->formasPagamento[] = [
            'pagamento_id' => null,
            'condicao_id'  => '',
            'valor'        => 0,
            'parcelas'     => 1,
            'comprovante'  => null,
        ];
    }

    public function removerFormaPagamento(int $index): void
    {
        $forma = $this->formasPagamento[$index];
        
        // Se tinha ID de pagamento, estorna do sistema
        if (!empty($forma['pagamento_id'])) {
            try {
                $pagamento = Pagamento::find($forma['pagamento_id']);
                if ($pagamento) {
                    $this->pagamentoService->estornarPagamento($pagamento, 'Removido via edição no faturamento de rota');
                }
            } catch (\Exception $e) {
                Log::error("PagamentoRota: erro ao estornar pagamento removido", ['error' => $e->getMessage()]);
            }
        }

        unset($this->formasPagamento[$index]);
        $this->formasPagamento = array_values($this->formasPagamento);

        if (empty($this->formasPagamento)) {
            $this->adicionarFormaPagamento();
        }

        $this->calcularValores();
    }

    public function preencherRestante(): void
    {
        $restante = $this->valorComDesconto - $this->valorPago - $this->valorJaPago;
        if ($restante > 0) {
            $ultimoIndex = count($this->formasPagamento) - 1;
            $this->formasPagamento[$ultimoIndex]['valor'] = number_format($restante, 2, '.', '');
            $this->calcularValores();
        }
    }

    // ─── Cálculo de valores ───────────────────────────────────────────────────

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'formasPagamento')) {
            // Se mudou condicao_id ou valor, reseta o comprovante daquela linha para forçar novo anexo se necessário
            if (str_ends_with($propertyName, '.condicao_id') || str_ends_with($propertyName, '.valor')) {
                $parts = explode('.', $propertyName);
                if (isset($parts[1])) {
                    $index = $parts[1];
                    $this->formasPagamento[$index]['comprovante'] = null;
                }
            }
            $this->calcularValores();
        }

        if ($propertyName === 'abaterCredito') {
            $this->calcularValores();
        }
    }

    public function calcularValores(): void
    {
        $this->valorComDesconto = (float) $this->orcamento->valor_total;
        
        // Agora todos os pagamentos (novos e já salvos) estão no grid formasPagamento
        $this->valorPago = collect($this->formasPagamento)->sum('valor');
        
        // valorJaPago deve ser zero pois eles já estão no grid e somando em valorPago
        $this->valorJaPago = 0; 

        $this->valorCreditoAbatido = $this->abaterCredito ? min($this->saldoDisponivel, $this->valorComDesconto - $this->valorPago) : 0;
        
        $totalSessao = $this->valorPago + $this->valorCreditoAbatido;
        $this->troco = max(0, $totalSessao - $this->valorComDesconto);
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
            ], [
                'billingStatus.required' => 'Selecione um resultado para a decisão do financeiro.',
                'billingStatus.in'       => 'Resultado de decisão inválido.',
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
                $this->validarDadosPagamento(false);
                $dadosPagamento = $this->prepararDadosPagamento();
                $resultado = $this->pagamentoService->salvarPagamentoVenda($dadosPagamento);

                Log::info("PagamentoRota: pagamento salvo com sucesso", [
                    'pagamento_id' => $resultado['pagamento']->id,
                ]);

                // 4. Salva os comprovantes enviados por forma de pagamento
                $this->processarUploadComprovantes();
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

    protected function validarDadosPagamento($permitirParcial = false): void
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
        if (!$permitirParcial && $totalPago < $this->valorComDesconto - 0.01) {
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

    public function registrarPagamentoAvulso(): void
    {
        Log::info("PagamentoRota: iniciando registro de pagamento avulso", [
            'orcamento_id' => $this->orcamentoId,
            'user_id'      => Auth::id(),
        ]);

        try {
            // 1. Validação básica
            if ($this->valorPago <= 0) {
                $this->addError('formasPagamento', 'Informe pelo menos um valor de pagamento.');
                return;
            }

            $this->validarDadosPagamento(true);

            DB::transaction(function () {
                foreach ($this->formasPagamento as $index => $forma) {
                    // 2. Se for um pagamento existente e mudou, estorna o antigo antes de criar novo
                    if (!empty($forma['pagamento_id'])) {
                        $pagamentoExistente = Pagamento::find($forma['pagamento_id']);
                        
                        // Verifica se houve mudança significativa ou novo arquivo
                        $metodoBase = $pagamentoExistente->metodos->first();
                        $mudou = ($metodoBase->metodo_pagamento_id != $forma['condicao_id']) || ($metodoBase->valor != $forma['valor']) || !empty($forma['comprovante']);
                        
                        if ($mudou) {
                            $this->pagamentoService->estornarPagamento($pagamentoExistente->id, 'Edição via faturamento de rota');
                            // Após estornar, tratamos como novo para o service criar o registro atualizado
                        } else {
                            // Não mudou nada, pula para o próximo
                            continue;
                        }
                    }

                    // 3. Prepara os dados para o NOVO registro (ou atualização via re-criação)
                    $dadosItem = [
                        'orcamento_id'          => $this->orcamentoId,
                        'condicao_pagamento_id' => $this->orcamento->condicao_id,
                        'tipo_documento'        => $this->orcamento->precisa_nota_fiscal ? 'nota_fiscal' : 'cupom_fiscal',
                        'metodos_pagamento'     => [
                            [
                                'metodo_id' => $forma['condicao_id'],
                                'valor'     => $forma['valor'],
                                'parcelas'  => 1,
                            ]
                        ],
                        'permitir_parcial'   => true,
                        'finalizar_registro' => false,
                    ];

                    $resultado = $this->pagamentoService->salvarPagamentoVenda($dadosItem);
                    $novoPagamento = $resultado['pagamento'];

                    // 4. Se houver novo comprovante (ou se já existia um que precisamos re-vincular)
                    $this->processarUploadComprovanteItem($index, $novoPagamento);
                }
            });

            session()->flash('success', 'Recebimento registrado com sucesso!');
            
            // 5. Recarrega do banco para garantir que o grid reflita o estado atual
            $this->carregarOrcamento();
            $this->calcularValores();

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) $this->addError($field, $message);
            }
        } catch (\Exception $e) {
            Log::error("PagamentoRota: erro no pagamento avulso", ['error' => $e->getMessage()]);
            session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
        }
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

    public function salvarApenasComprovantes(): void
    {
        try {
            $this->processarUploadComprovantes();
            
            session()->flash('success', 'Comprovantes salvos com sucesso!');
            $this->carregarOrcamento(); // Recarrega para mostrar os anexos na lista
        } catch (\Exception $e) {
            Log::error("PagamentoRota: erro ao salvar comprovantes", ['error' => $e->getMessage()]);
            session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
        }
    }

    protected function processarUploadComprovanteItem(int $index, $pagamento): void
    {
        $forma = $this->formasPagamento[$index];
        $comprovante = $forma['comprovante'] ?? null;
        
        // 1. Se houver um novo arquivo fazendo upload
        if ($comprovante instanceof UploadedFile) {
            $metodo = MetodoPagamento::find($forma['condicao_id']);
            $nomeMetodo = $metodo ? $metodo->nome : 'Método não identificado';
            
            $path = $comprovante->store('comprovantes-rota', 'public');
            
            RouteBillingAttachment::create([
                'orcamento_id' => $this->orcamentoId,
                'pagamento_id' => $pagamento->id,
                'user_id'      => Auth::id(),
                'file_path'    => $path,
                'file_type'    => $comprovante->getClientOriginalExtension(),
                'notes'        => "Comprovante de pagamento ({$nomeMetodo}): R$ " . number_format($forma['valor'], 2, ',', '.'),
            ]);

            // Limpa o campo temporário
            $this->formasPagamento[$index]['comprovante'] = null;
        } 
        // 2. Se não houver novo arquivo, mas havia um comprovante_url (re-vincular o anexo anterior ao novo pagamento)
        elseif (!empty($forma['comprovante_url']) && !empty($forma['pagamento_id'])) {
             // O anexo anterior estava em route_billing_attachments vinculado ao pagamento_id antigo
             // Como estornamos o antigo e criamos um novo, precisamos atualizar o pagamento_id no anexo
             RouteBillingAttachment::where('pagamento_id', $forma['pagamento_id'])
                ->update(['pagamento_id' => $pagamento->id]);
        }
    }

    public function render()
    {
        return view('livewire.pagamento-rota');
    }
}
