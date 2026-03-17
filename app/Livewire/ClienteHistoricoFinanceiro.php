<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Fatura;
use Livewire\WithPagination;

class ClienteHistoricoFinanceiro extends Component
{
    use WithPagination;

    public $clienteId;

    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
    }

    public function render()
    {
        $cliente = Cliente::with('analisesCredito')->find($this->clienteId);

        // Pagamentos concluídos
        $pagamentos = Pagamento::with(['orcamento', 'pedido', 'formas.condicaoPagamento', 'metodos'])
            ->whereHas('orcamento', function($q){
                $q->where('cliente_id', $this->clienteId);
            })
            ->orWhereHas('pedido', function($q) {
                $q->where('cliente_id', $this->clienteId);
            })
            ->where('estornado', false)
            ->latest('data_pagamento')
            ->paginate(10, ['*'], 'pagamentosPage');

        // Boletos em aberto (Faturas)
        $faturasAberto = Fatura::where('cliente_id', $this->clienteId)
            ->where('status', '!=', 'pago')
            ->orderBy('data_vencimento', 'asc')
            ->paginate(10, ['*'], 'faturasPage');

        return view('livewire.cliente-historico-financeiro', [
            'cliente' => $cliente,
            'pagamentos' => $pagamentos,
            'faturasAberto' => $faturasAberto
        ]);
    }
}
