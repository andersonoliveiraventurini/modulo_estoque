<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orcamento;
use App\Models\CondicoesPagamento;
use App\Services\CreditoService;
use Illuminate\Support\Facades\DB;

class PagamentoBalcao extends Component
{
    // ID do orçamento
    public $orcamentoId;
    public $orcamento;

    // Formas de pagamento
    public $condicoesPagamento = [];
    public $formasPagamento = []; // Array para as formas selecionadas

    // Descontos
    public $descontoBalcao = 0;
    public $descontoAplicado = 0;
    public $descontoOriginal = 0;
    public $creditos = 0;
    public $creditosUtilizados = 0; // Total de créditos sendo usados neste pagamento

    // Nota Fiscal
    public $precisaNotaFiscal = false;
    public $notaOutroCnpjCpf = false;
    public $cnpjCpfNota = '';

    // Controle
    public $showModal = false;
    public $podeAplicarDesconto = true;

    // Valores calculados
    public $valorPago = 0;
    public $valorComDesconto = 0;
    public $troco = 0;

    protected $rules = [
        'formasPagamento.*.valor' => 'required|numeric|min:0.01',
        'descontoBalcao' => 'nullable|numeric|min:0',
        'cnpjCpfNota' => 'required_if:notaOutroCnpjCpf,true',
    ];

    public function mount($orcamentoId)
    {
        $this->orcamentoId = $orcamentoId;
        $this->orcamento = Orcamento::with(['cliente', 'vendedor', 'condicaoPagamento'])->findOrFail($orcamentoId);

        // Inicializar valores
        $this->descontoAplicado = $this->orcamento->desconto ?? 0;
        $this->descontoOriginal = $this->descontoAplicado;
        $this->valorComDesconto = $this->orcamento->valor_total_itens - $this->descontoAplicado;

        // Carregar condições de pagamento disponíveis
        $this->condicoesPagamento = CondicoesPagamento::all();

        // Inicializar com uma forma de pagamento vazia
        $this->formasPagamento = [
            ['tipo' => 'normal', 'condicao_id' => '', 'valor' => 0]
        ];

        // Buscar créditos disponíveis do cliente
        $creditoService = app(CreditoService::class);
        $this->creditos = $creditoService->getSaldoDisponivel($this->orcamento->cliente_id);

        $this->calcularValores();
    }

    public function updated($propertyName)
    {
        if (
            str_starts_with($propertyName, 'formasPagamento') ||
            $propertyName === 'descontoBalcao'
        ) {
            $this->calcularValores();
        }
    }

    public function calcularValores()
    {
        // Calcular valor pago e créditos utilizados
        $this->valorPago = 0;
        $this->creditosUtilizados = 0;

        foreach ($this->formasPagamento as $forma) {
            $valor = floatval($forma['valor'] ?? 0);
            $this->valorPago += $valor;

            if (isset($forma['tipo']) && $forma['tipo'] === 'credito') {
                $this->creditosUtilizados += $valor;
            }
        }

        // Limitar desconto de balcão a 3%
        $maxDesconto = $this->orcamento->valor_total_itens * 0.03;
        if ($this->descontoBalcao > $maxDesconto) {
            $this->descontoBalcao = $maxDesconto;
        }

        // Verificar se pode aplicar desconto no balcão
        $this->podeAplicarDesconto = $this->validarDescontoBalcao();

        // Se não pode aplicar desconto, zerar
        if (!$this->podeAplicarDesconto) {
            $this->descontoBalcao = 0;
        }

        // Calcular valor com desconto
        $this->valorComDesconto = $this->orcamento->valor_total_itens
            - $this->descontoAplicado
            - $this->descontoBalcao;

        // Calcular troco
        $this->troco = max(0, $this->valorPago - $this->valorComDesconto);
    }

    public function validarDescontoBalcao()
    {
        // Desconto de balcão só pode ser aplicado se:
        // 1. Não houver desconto original
        // 2. Todas as formas de pagamento sejam PIX ou Dinheiro

        if ($this->descontoOriginal > 0 && $this->descontoAplicado > 0) {
            return false;
        }

        $condicoesPix = $this->condicoesPagamento
            ->whereIn('nome', ['PIX', 'Dinheiro', 'À Vista'])
            ->pluck('id')
            ->toArray();

        foreach ($this->formasPagamento as $forma) {
            // Créditos são permitidos
            if (isset($forma['tipo']) && $forma['tipo'] === 'credito') {
                continue;
            }

            // Se não for PIX/Dinheiro, não pode desconto
            if (!in_array($forma['condicao_id'] ?? null, $condicoesPix)) {
                return false;
            }
        }

        return true;
    }

    public function adicionarFormaPagamento()
    {
        $this->formasPagamento[] = [
            'tipo' => 'normal',
            'condicao_id' => '',
            'valor' => 0
        ];
    }

    public function removerFormaPagamento($index)
    {
        // Não permitir remover se for a única forma
        if (count($this->formasPagamento) <= 1) {
            return;
        }

        unset($this->formasPagamento[$index]);
        $this->formasPagamento = array_values($this->formasPagamento);
        $this->calcularValores();
    }

    // ========== MÉTODOS DE CRÉDITOS ==========

    /**
     * Adiciona uma nova forma de pagamento com créditos
     */
    public function adicionarCreditos()
    {
        if ($this->creditos <= 0) {
            session()->flash('error', 'Cliente não possui créditos disponíveis.');
            return;
        }

        // Verificar se já não está usando todos os créditos
        if ($this->creditosUtilizados >= $this->creditos) {
            session()->flash('error', 'Todos os créditos disponíveis já estão sendo utilizados.');
            return;
        }

        $this->formasPagamento[] = [
            'tipo' => 'credito',
            'condicao_id' => null,
            'valor' => 0
        ];
    }

    /**
     * Adiciona o valor máximo de créditos disponível ou o valor restante
     */
    public function usarTodosCreditos()
    {
        if ($this->creditos <= 0) {
            session()->flash('error', 'Cliente não possui créditos disponíveis.');
            return;
        }

        $valorRestante = $this->valorComDesconto - $this->valorPago;
        $creditosDisponiveis = $this->creditos - $this->creditosUtilizados;
        $valorUsar = min($creditosDisponiveis, $valorRestante);

        if ($valorUsar <= 0) {
            session()->flash('info', 'Não há valor restante para pagar ou todos os créditos já foram utilizados.');
            return;
        }

        $this->formasPagamento[] = [
            'tipo' => 'credito',
            'condicao_id' => null,
            'valor' => round($valorUsar, 2)
        ];

        $this->calcularValores();
    }

    /**
     * Paga o total da compra usando apenas créditos
     */
    public function usarCreditosExatos()
    {
        if ($this->creditos < $this->valorComDesconto) {
            session()->flash('error', 'Créditos insuficientes para pagar o valor total.');
            return;
        }

        $this->formasPagamento = [[
            'tipo' => 'credito',
            'condicao_id' => null,
            'valor' => round($this->valorComDesconto, 2)
        ]];

        $this->calcularValores();
    }

    // ========== FIM MÉTODOS DE CRÉDITOS ==========

    public function preencherRestante()
    {
        $valorRestante = $this->valorComDesconto - $this->valorPago;

        if ($valorRestante <= 0) {
            session()->flash('info', 'Não há valor restante para preencher.');
            return;
        }

        // Adicionar nova forma de pagamento com o valor restante
        $this->formasPagamento[] = [
            'tipo' => 'normal',
            'condicao_id' => '',
            'valor' => round($valorRestante, 2)
        ];

        $this->calcularValores();
    }

    public function usandoCartao()
    {
        // Verifica se alguma forma de pagamento é cartão
        $condicoesCartao = $this->condicoesPagamento
            ->whereIn('nome', ['Cartão de Crédito', 'Cartão de Débito', 'Crédito', 'Débito'])
            ->pluck('id')
            ->toArray();

        foreach ($this->formasPagamento as $forma) {
            if (isset($forma['tipo']) && $forma['tipo'] === 'credito') {
                continue;
            }
            
            if (in_array($forma['condicao_id'] ?? null, $condicoesCartao)) {
                return true;
            }
        }

        return false;
    }

    public function removerDescontoOriginal()
    {
        $this->descontoAplicado = 0;
        $this->calcularValores();

        session()->flash('success', 'Desconto original removido com sucesso!');
    }

    public function finalizarPagamento()
    {
        // Validação customizada para formas de pagamento
        $this->validate([
            'descontoBalcao' => 'nullable|numeric|min:0',
            'cnpjCpfNota' => 'required_if:notaOutroCnpjCpf,true',
        ]);

        // Validar cada forma de pagamento
        foreach ($this->formasPagamento as $index => $forma) {
            if (isset($forma['tipo']) && $forma['tipo'] === 'credito') {
                // Validar créditos
                if (empty($forma['valor']) || $forma['valor'] <= 0) {
                    session()->flash('error', "Forma de pagamento #" . ($index + 1) . ": valor do crédito deve ser maior que zero.");
                    return;
                }

                if ($forma['valor'] > $this->creditos) {
                    session()->flash('error', "Forma de pagamento #" . ($index + 1) . ": valor excede os créditos disponíveis.");
                    return;
                }
            } else {
                // Validar formas normais
                if (empty($forma['condicao_id'])) {
                    session()->flash('error', "Forma de pagamento #" . ($index + 1) . ": selecione uma condição de pagamento.");
                    return;
                }

                if (empty($forma['valor']) || $forma['valor'] <= 0) {
                    session()->flash('error', "Forma de pagamento #" . ($index + 1) . ": valor deve ser maior que zero.");
                    return;
                }
            }
        }

        // Validar se o valor total é suficiente
        if ($this->valorPago < $this->valorComDesconto) {
            session()->flash('error', 'Valor pago insuficiente! Falta: R$ ' . number_format($this->valorComDesconto - $this->valorPago, 2, ',', '.'));
            return;
        }

        // Validar se não está usando mais créditos do que possui
        if ($this->creditosUtilizados > $this->creditos) {
            session()->flash('error', 'O valor total de créditos utilizados excede o disponível!');
            return;
        }

        try {
            DB::transaction(function () {
                $creditoService = app(CreditoService::class);

                // Processar cada forma de pagamento
                foreach ($this->formasPagamento as $forma) {
                    if (isset($forma['tipo']) && $forma['tipo'] === 'credito') {
                        // Utilizar créditos
                        $resultado = $creditoService->utilizarCreditos(
                            $this->orcamento->cliente_id,
                            $forma['valor'],
                            $this->orcamento->id,
                            'orcamento',
                            auth()->id(),
                            "Pagamento do orçamento #{$this->orcamento->id}"
                        );

                        if (!$resultado['sucesso']) {
                            throw new \Exception('Erro ao utilizar créditos: créditos insuficientes');
                        }
                    } else {
                        // Processar pagamento normal
                        // TODO: Implementar lógica de pagamento normal
                        // Exemplo: registrar em uma tabela de pagamentos
                    }
                }

                // Se houver troco, gerar crédito para o cliente
                if ($this->troco > 0) {
                    $creditoService->gerarCreditoTroco(
                        $this->orcamento->cliente_id,
                        $this->troco,
                        $this->orcamento->id,
                        'orcamento',
                        auth()->id(),
                        "Troco gerado no pagamento do orçamento #{$this->orcamento->id}"
                    );
                }

                // Atualizar status do orçamento
                $this->orcamento->update([
                    'status' => 'pago',
                    'desconto_balcao' => $this->descontoBalcao,
                    'desconto_aplicado' => $this->descontoAplicado,
                    'valor_final' => $this->valorComDesconto,
                    'valor_pago' => $this->valorPago,
                    'troco' => $this->troco,
                    'precisa_nota_fiscal' => $this->precisaNotaFiscal,
                    'data_pagamento' => now(),
                ]);

                // TODO: Gerar nota fiscal ou cupom
                // TODO: Registrar os pagamentos em uma tabela específica

                session()->flash('success', 'Pagamento realizado com sucesso!' . 
                    ($this->troco > 0 ? ' Troco de R$ ' . number_format($this->troco, 2, ',', '.') . ' convertido em créditos.' : ''));
            });

            return redirect()->route('orcamentos.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao processar pagamento: ' . $e->getMessage());
            \Log::error('Erro no pagamento do orçamento #' . $this->orcamentoId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pagamento-balcao');
    }
}