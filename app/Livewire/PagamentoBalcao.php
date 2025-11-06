<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orcamento;
use App\Models\CondicoesPagamento;

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
        'formasPagamento.*.condicao_id' => 'required|exists:condicoes_pagamento,id',
        'formasPagamento.*.valor' => 'required|numeric|min:0',
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
            ['condicao_id' => '', 'valor' => 0]
        ];
        
        $this->calcularValores();
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'formasPagamento') || 
            $propertyName === 'descontoBalcao') {
            $this->calcularValores();
        }
    }

    public function calcularValores()
    {
        // Calcular valor pago
        $this->valorPago = collect($this->formasPagamento)
            ->sum(function($forma) {
                return floatval($forma['valor'] ?? 0);
            });
        
        // Limitar desconto de balcão a 3%
        $maxDesconto = $this->orcamento->valor_total_itens * 0.03;
        if ($this->descontoBalcao > $maxDesconto) {
            $this->descontoBalcao = $maxDesconto;
        }
        
        // Calcular valor com desconto
        $this->valorComDesconto = $this->orcamento->valor_total_itens 
            - $this->descontoAplicado 
            - $this->descontoBalcao;
        
        // Calcular troco
        $this->troco = max(0, $this->valorPago - $this->valorComDesconto);
    }

    public function adicionarFormaPagamento()
    {
        $this->formasPagamento[] = [
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

    public function preencherRestante()
    {
        $valorRestante = $this->valorComDesconto - $this->valorPago;
        
        if ($valorRestante <= 0) {
            return;
        }
        
        // Adicionar nova forma de pagamento com o valor restante
        $this->formasPagamento[] = [
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
        $this->validate([
            'formasPagamento.*.condicao_id' => 'required|exists:condicoes_pagamento,id',
            'formasPagamento.*.valor' => 'required|numeric|min:0.01',
            'descontoBalcao' => 'nullable|numeric|min:0',
            'cnpjCpfNota' => 'required_if:notaOutroCnpjCpf,true',
        ]);
        
        if ($this->valorPago < $this->valorComDesconto) {
            session()->flash('error', 'Valor pago insuficiente!');
            return;
        }
        
        try {
            // Sua lógica de pagamento aqui
            
            session()->flash('success', 'Pagamento realizado com sucesso!');
            return redirect()->route('orcamentos.index');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pagamento-balcao');
    }
}