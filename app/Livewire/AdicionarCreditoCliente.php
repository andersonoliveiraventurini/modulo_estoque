<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Services\CreditoService;
use Illuminate\Support\Facades\Log;

class AdicionarCreditoCliente extends Component
{
    public $clienteId;
    public $valor;
    public $motivo;
    public $tipo = 'ajuste';

    protected $rules = [
        'valor'  => 'required|numeric|min:0.01',
        'motivo' => 'required|string|min:5',
        'tipo'   => 'required|in:ajuste,bonificacao,outro',
    ];

    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
    }

    public function salvar(CreditoService $creditoService)
    {
        $this->validate();

        $cliente = Cliente::findOrFail($this->clienteId);

        $this->authorize('gerenciarCredito', $cliente);

        $creditoService->adicionarCredito(
            $cliente,
            $this->valor,
            $this->motivo,
            null,
            $this->tipo
        );

        Log::info('Crédito adicionado manualmente', [
            'cliente_id' => $this->clienteId,
            'valor'      => $this->valor,
            'tipo'       => $this->tipo,
            'user_id'    => auth()->id(),
        ]);

        $this->dispatch('creditoAdicionado');
        
        session()->flash('success', 'Crédito adicionado com sucesso!');

        $this->reset(['valor', 'motivo', 'tipo']);
        $this->tipo = 'ajuste';
    }

    public function render()
    {
        return view('livewire.adicionar-credito-cliente');
    }
}
