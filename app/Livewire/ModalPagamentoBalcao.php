<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orcamento;
use App\Models\CondicoesPagamento;
use App\Models\Desconto;
use App\Models\Pagamento;
use Illuminate\Support\Facades\DB;

class ModalPagamentoBalcao extends Component
{
    public $orcamento;
    public $orcamentoId;
    public bool $showModal = false;
    
    // Formas de pagamento
    public $condicoesPagamento = [];
    public array $formasPagamento = [];
    
    // Nota Fiscal
    public bool $precisaNotaFiscal = false;
    public bool $notaOutroCnpjCpf = false;
    public string $cnpjCpfNota = '';
    
    // Desconto
    public float $descontoAplicado = 0;
    public float $descontoBalcao = 0;
    public bool $podeAplicarDesconto = false;
    public bool $temDescontoOriginal = false;
    
    // Totais
    public float $valorTotal = 0;
    public float $valorComDesconto = 0;
    public float $valorPago = 0;
    public float $troco = 0;
    
    protected $rules = [
        'formasPagamento.*.condicao_id' => 'required|exists:condicoes_pagamento,id',
        'formasPagamento.*.valor' => 'required|numeric|min:0.01',
        'cnpjCpfNota' => 'nullable|string',
    ];

    public function mount($orcamentoId)
    {
        $this->orcamentoId = $orcamentoId;
        $this->carregarOrcamento();
    }

    public function carregarOrcamento()
    {
        $this->orcamento = Orcamento::with(['condicaoPagamento', 'descontos'])
            ->findOrFail($this->orcamentoId);
        
        $this->condicoesPagamento = CondicoesPagamento::all();
        $this->valorTotal = $this->orcamento->valor_total_itens;
        
        // Verificar se já tem desconto no orçamento
        $this->temDescontoOriginal = $this->orcamento->descontos()->exists();
        $descontosExistentes = $this->orcamento->descontos->sum('valor');
        $this->descontoAplicado = $descontosExistentes;
        
        // Inicializar com a condição do orçamento
        if ($this->orcamento->condicao_id) {
            $this->formasPagamento[] = [
                'condicao_id' => $this->orcamento->condicao_id,
                'valor' => $this->valorTotal - $descontosExistentes,
            ];
        } else {
            $this->adicionarFormaPagamento();
        }
        
        $this->calcularTotais();
    }

    public function adicionarFormaPagamento()
    {
        $this->formasPagamento[] = [
            'condicao_id' => null,
            'valor' => 0,
        ];
    }

    public function removerFormaPagamento($index)
    {
        unset($this->formasPagamento[$index]);
        $this->formasPagamento = array_values($this->formasPagamento);
        $this->calcularTotais();
    }

    public function updatedFormasPagamento()
    {
        $this->calcularTotais();
        $this->verificarDescontoBalcao();
    }

    public function updatedDescontoBalcao()
    {
        // Validar desconto máximo de 3%
        if ($this->descontoBalcao > ($this->valorTotal * 0.03)) {
            $this->descontoBalcao = $this->valorTotal * 0.03;
        }
        
        $this->calcularTotais();
    }

    public function verificarDescontoBalcao()
    {
        // Verifica se pode aplicar desconto de 3% no balcão
        // Somente PIX ou Dinheiro e sem desconto prévio
        if ($this->temDescontoOriginal) {
            $this->podeAplicarDesconto = false;
            $this->descontoBalcao = 0;
            return;
        }

        $condicoesPermitidas = ['PIX', 'Dinheiro'];
        $todasCondicoesPermitidas = true;

        foreach ($this->formasPagamento as $forma) {
            if ($forma['condicao_id']) {
                $condicao = $this->condicoesPagamento->find($forma['condicao_id']);
                if ($condicao && !in_array($condicao->nome, $condicoesPermitidas)) {
                    $todasCondicoesPermitidas = false;
                    break;
                }
            }
        }

        $this->podeAplicarDesconto = $todasCondicoesPermitidas;
        
        if (!$this->podeAplicarDesconto) {
            $this->descontoBalcao = 0;
        }
    }

    public function verificarRemocaoDesconto()
    {
        // Se a condição original era PIX ou Dinheiro e mudou para Cartão
        $condicaoOriginal = $this->orcamento->condicaoPagamento;
        
        if (!$condicaoOriginal) {
            return false;
        }

        $condicoesOriginaisComDesconto = ['PIX', 'Dinheiro'];
        $temCondicaoCartao = false;

        foreach ($this->formasPagamento as $forma) {
            if ($forma['condicao_id']) {
                $condicao = $this->condicoesPagamento->find($forma['condicao_id']);
                if ($condicao && str_contains(strtolower($condicao->nome), 'cartão')) {
                    $temCondicaoCartao = true;
                    break;
                }
            }
        }

        return in_array($condicaoOriginal->nome, $condicoesOriginaisComDesconto) 
               && $temCondicaoCartao 
               && $this->temDescontoOriginal;
    }

    public function removerDescontoOriginal()
    {
        if ($this->verificarRemocaoDesconto()) {
            $this->descontoAplicado = 0;
            $this->calcularTotais();
        }
    }

    public function calcularTotais()
    {
        $this->valorPago = collect($this->formasPagamento)
            ->sum('valor');
        
        $this->valorComDesconto = $this->valorTotal - $this->descontoAplicado - $this->descontoBalcao;
        
        $this->troco = max(0, $this->valorPago - $this->valorComDesconto);
    }

    public function finalizarPagamento()
    {
        $this->validate();

        // Validar se o valor pago é suficiente
        if ($this->valorPago < $this->valorComDesconto) {
            $this->addError('valorPago', 'O valor pago é insuficiente!');
            return;
        }

        DB::beginTransaction();
        
        try {
            // Salvar descontos do balcão se houver
            if ($this->descontoBalcao > 0) {
                Desconto::create([
                    'motivo' => 'Desconto no Balcão',
                    'valor' => $this->descontoBalcao,
                    'porcentagem' => ($this->descontoBalcao / $this->valorTotal) * 100,
                    'tipo' => 'percentual',
                    'orcamento_id' => $this->orcamento->id,
                    'cliente_id' => $this->orcamento->cliente_id,
                    'user_id' => auth()->id(),
                ]);
            }

            // Remover descontos originais se necessário
            if ($this->verificarRemocaoDesconto() && $this->descontoAplicado == 0) {
                $this->orcamento->descontos()->delete();
            }

            // Salvar pagamentos
            foreach ($this->formasPagamento as $forma) {
                if ($forma['valor'] > 0) {
                    Pagamento::create([
                        'orcamento_id' => $this->orcamento->id,
                        'condicao_pagamento_id' => $forma['condicao_id'],
                        'valor' => $forma['valor'],
                        'data_pagamento' => now(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            // Atualizar status do orçamento
            $this->orcamento->update([
                'status' => 'Finalizado',
            ]);

            DB::commit();

            // Gerar cupom/nota fiscal
            $this->gerarDocumentoFiscal();

            // Imprimir via do cliente
            $this->imprimirViaCliente();

            $this->dispatch('pagamentoFinalizado');
            $this->showModal = false;
            
            session()->flash('message', 'Pagamento finalizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('geral', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    protected function gerarDocumentoFiscal()
    {
        // Implementar lógica de geração de nota fiscal ou cupom
        // Aqui você integraria com seu sistema fiscal
        if ($this->precisaNotaFiscal) {
            // Gerar nota fiscal
            // Considerar o CNPJ/CPF diferente se informado
        } else {
            // Gerar cupom fiscal
        }
    }

    protected function imprimirViaCliente()
    {
        // Implementar lógica de impressão da via do cliente
        // Incluir: itens, formas de pagamento, textos específicos
    }

    public function render()
    {
        return view('livewire.modal-pagamento-balcao');
    }
}