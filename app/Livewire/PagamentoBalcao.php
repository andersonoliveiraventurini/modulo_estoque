<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\CondicoesPagamento;
use App\Models\MetodoPagamento;
use App\Services\PagamentoService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PagamentoBalcao extends Component
{
    public $showModal = false;
    public $orcamentoId;
    public $orcamento;
    
    public $formasPagamento = [];
    public $condicoesPagamento = [];
    
    public $descontoBalcao = 0;
    public $descontoOriginal = 0;
    public $descontoAplicado = 0;
    
    public $precisaNotaFiscal = false;
    public $notaOutroCnpjCpf = false;
    public $cnpjCpfNota = '';
    
    public $valorPago = 0;
    public $valorComDesconto = 0;
    public $troco = 0;

    protected $pagamentoService;

    public function boot(PagamentoService $pagamentoService)
    {
        $this->pagamentoService = $pagamentoService;
    }

    public function mount($orcamentoId)
    {
        $this->orcamentoId = $orcamentoId;
        $this->carregarOrcamento();
        $this->carregarCondicoesPagamento();
        $this->inicializarFormaPagamento();
        $this->calcularValores();
    }

    public function carregarOrcamento()
    {
        $this->orcamento = Orcamento::with(['cliente', 'vendedor', 'condicaoPagamento', 'itens'])
            ->findOrFail($this->orcamentoId);
        
        $this->descontoOriginal = $this->orcamento->desconto ?? 0;
        $this->descontoAplicado = $this->descontoOriginal;
    }

    public function carregarCondicoesPagamento()
    {
        $this->condicoesPagamento = MetodoPagamento::ativos()
            ->ordenado()
            ->get();
    }

    public function inicializarFormaPagamento()
    {
        $this->formasPagamento = [
            [
                'condicao_id' => '',
                'valor' => 0,
                'usa_credito' => false,
                'parcelas' => 1,
            ]
        ];
    }

    public function adicionarFormaPagamento()
    {
        $this->formasPagamento[] = [
            'condicao_id' => '',
            'valor' => 0,
            'usa_credito' => false,
            'parcelas' => 1,
        ];
    }

    public function removerFormaPagamento($index)
    {
        if (count($this->formasPagamento) > 1) {
            unset($this->formasPagamento[$index]);
            $this->formasPagamento = array_values($this->formasPagamento);
            $this->calcularValores();
        }
    }

    public function preencherRestante()
    {
        $valorRestante = $this->valorComDesconto - $this->valorPago;
        
        if ($valorRestante > 0) {
            $ultimoIndex = count($this->formasPagamento) - 1;
            $this->formasPagamento[$ultimoIndex]['valor'] = number_format($valorRestante, 2, '.', '');
            $this->calcularValores();
        }
    }

    public function removerDescontoOriginal()
    {
        $this->descontoAplicado = 0;
        $this->calcularValores();
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'formasPagamento') || 
            $propertyName === 'descontoBalcao' || 
            $propertyName === 'descontoAplicado') {
            $this->calcularValores();
        }

        if (str_starts_with($propertyName, 'formasPagamento')) {
            $this->verificarUsoCreditoCliente();
        }
    }

    protected function verificarUsoCreditoCliente()
    {
        foreach ($this->formasPagamento as $index => $forma) {
            if (!empty($forma['condicao_id'])) {
                $metodo = MetodoPagamento::find($forma['condicao_id']);
                if ($metodo && $metodo->isCreditoCliente()) {
                    $this->formasPagamento[$index]['usa_credito'] = true;
                }
            }
        }
    }

    public function calcularValores()
    {
        $valorTotal = $this->orcamento->valor_total_itens ?? 0;
        
        $isBlocked = $this->orcamento->cliente->bloqueado ?? false;
        $maxDescontoBalcao = $isBlocked ? 0 : ($valorTotal * 0.03);
        
        if ($this->descontoBalcao > $maxDescontoBalcao) {
            $this->descontoBalcao = $maxDescontoBalcao;
        }

        $descontoTotal = $this->descontoAplicado + $this->descontoBalcao;
        $this->valorComDesconto = max(0, $valorTotal - $descontoTotal);

        $this->valorPago = 0;
        foreach ($this->formasPagamento as $forma) {
            $this->valorPago += (float) ($forma['valor'] ?? 0);
        }

        $this->troco = max(0, $this->valorPago - $this->valorComDesconto);
    }

    public function usandoCartao()
    {
        foreach ($this->formasPagamento as $forma) {
            if (!empty($forma['condicao_id'])) {
                $metodo = MetodoPagamento::find($forma['condicao_id']);
                if ($metodo && in_array($metodo->tipo, ['cartao_credito', 'cartao_debito'])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function finalizarPagamento()
    {
        // 🔍 PONTO DE DEBUG 1: Verificar se o método foi chamado
        // dd('🎯 PONTO 1: Método finalizarPagamento foi chamado!', [
        //     'orcamento_id' => $this->orcamentoId,
        //     'valor_pago' => $this->valorPago,
        //     'valor_com_desconto' => $this->valorComDesconto,
        //     'formas_pagamento' => $this->formasPagamento,
        // ]);

        // Log para acompanhar execução
        logger('🟢 Iniciando processamento de pagamento', [
            'orcamento_id' => $this->orcamentoId,
            'valor_pago' => $this->valorPago,
        ]);

        try {
            // Valida os dados antes de enviar
            $this->validarDadosPagamento();
            
            logger('✅ Validação passou');

            // Prepara os dados para o service
            $dadosPagamento = $this->prepararDadosPagamento();

            // 🔍 PONTO DE DEBUG 2: Verificar dados preparados
            // dd('🎯 PONTO 2: Dados preparados para o Service', [
            //     'dados_pagamento' => $dadosPagamento,
            //     'metodos_pagamento' => $dadosPagamento['metodos_pagamento'],
            // ]);

            logger('📦 Dados preparados', ['dados' => $dadosPagamento]);

            // Processa o pagamento
            $resultado = $this->pagamentoService->salvarPagamentoVenda($dadosPagamento);

            // 🔍 PONTO DE DEBUG 3: Verificar resultado
            // dd('🎯 PONTO 3: Pagamento processado com sucesso!', [
            //     'pagamento_id' => $resultado['pagamento']->id,
            //     'resultado_completo' => $resultado,
            // ]);

            logger('💾 Pagamento salvo com sucesso', [
                'pagamento_id' => $resultado['pagamento']->id,
            ]);

            // Sucesso! Exibe mensagem
            session()->flash('success', $resultado['mensagem']);
            
            // Emite evento de sucesso
            $this->dispatch('pagamento-finalizado', [
                'pagamentoId' => $resultado['pagamento']->id,
                'numeroDocumento' => $resultado['pagamento']->numero_documento,
            ]);

            // Fecha o modal
            $this->showModal = false;

            // Redireciona
            return redirect()->route('orcamentos.index')
                ->with('success', 'Pagamento realizado com sucesso!');

        } catch (ValidationException $e) {
            // Erros de validação
            logger('❌ Erro de validação', ['erros' => $e->errors()]);
            
            $this->addError('geral', 'Por favor, corrija os erros abaixo:');
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        } catch (\Exception $e) {
            // Outros erros
            logger('❌ ERRO no processamento', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // 🔍 PONTO DE DEBUG 4: Ver detalhes do erro
            // dd('🎯 PONTO 4: Erro capturado!', [
            //     'mensagem' => $e->getMessage(),
            //     'arquivo' => $e->getFile(),
            //     'linha' => $e->getLine(),
            //     'dados_enviados' => $dadosPagamento ?? 'não preparados',
            // ]);

            $this->addError('geral', $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }

    protected function validarDadosPagamento()
    {
        $formasValidas = array_filter($this->formasPagamento, function ($forma) {
            return !empty($forma['condicao_id']) && !empty($forma['valor']) && $forma['valor'] > 0;
        });

        if (empty($formasValidas)) {
            throw new \Exception('É necessário adicionar pelo menos uma forma de pagamento válida.');
        }

        if ($this->valorPago < $this->valorComDesconto) {
            $faltando = $this->valorComDesconto - $this->valorPago;
            throw new \Exception(
                'Valor pago insuficiente! Falta: R$ ' . number_format($faltando, 2, ',', '.')
            );
        }

        $isBlocked = $this->orcamento->cliente->bloqueado ?? false;
        $maxDesconto = $isBlocked ? 0 : ($this->orcamento->valor_total_itens * 0.03);
        
        if ($this->descontoBalcao > $maxDesconto) {
            if ($isBlocked) {
                throw new \Exception('Cliente bloqueado: desconto de balcão não é permitido.');
            }
            throw new \Exception(
                'Desconto de balcão não pode ser maior que 3% do valor total.'
            );
        }

        if ($this->precisaNotaFiscal && $this->notaOutroCnpjCpf && empty($this->cnpjCpfNota)) {
            throw new \Exception('CNPJ/CPF da nota fiscal é obrigatório quando selecionado.');
        }
    }

    protected function prepararDadosPagamento()
    {
        $formasValidas = array_filter($this->formasPagamento, function ($forma) {
            return !empty($forma['condicao_id']) && !empty($forma['valor']) && $forma['valor'] > 0;
        });

        $metodosPagamento = [];
        foreach ($formasValidas as $forma) {
            $metodo = MetodoPagamento::find($forma['condicao_id']);
            
            $metodosPagamento[] = [
                'metodo_id' => $forma['condicao_id'],
                'valor' => (float) $forma['valor'],
                'usa_credito' => $metodo && $metodo->isCreditoCliente(),
                'parcelas' => (int) ($forma['parcelas'] ?? 1),
            ];
        }

        return [
            'orcamento_id' => $this->orcamentoId,
            'condicao_pagamento_id' => $this->orcamento->condicao_pagamento_id ?? 1,
            'metodos_pagamento' => $metodosPagamento,
            'desconto_balcao' => (float) $this->descontoBalcao,
            'tipo_documento' => $this->precisaNotaFiscal ? 'nota_fiscal' : 'cupom_fiscal',
            'cnpj_cpf_nota' => $this->notaOutroCnpjCpf ? $this->cnpjCpfNota : null,
            'observacoes' => $this->gerarObservacoes(),
            'gerar_troco_como_credito' => false,
        ];
    }

    protected function gerarObservacoes()
    {
        $observacoes = [];

        if ($this->descontoAplicado != $this->descontoOriginal) {
            $observacoes[] = 'Desconto original removido (estava usando cartão)';
        }

        if ($this->descontoBalcao > 0) {
            $observacoes[] = 'Desconto de balcão aplicado: R$ ' . number_format($this->descontoBalcao, 2, ',', '.');
        }

        if ($this->troco > 0) {
            $observacoes[] = 'Troco: R$ ' . number_format($this->troco, 2, ',', '.');
        }

        return implode(' | ', $observacoes);
    }

    public function render()
    {
        return view('livewire.pagamento-balcao');
    }
}