<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClienteCreditos;
use Livewire\WithPagination;

class ClienteHistoricoCreditos extends Component
{
    use WithPagination;

    protected $listeners = ['creditoAdicionado' => '$refresh'];

    public $clienteId;

    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
    }

    public function render()
    {
        $creditos = ClienteCreditos::with(['usuarioCriacao', 'movimentacoes.usuario', 'movimentacoes.pagamento'])
            ->where('cliente_id', $this->clienteId)
            ->latest('created_at')
            ->paginate(10);

        $saldoTotal = app(\App\Services\CreditoService::class)->getSaldoDisponivel($this->clienteId);

        return view('livewire.cliente-historico-creditos', [
            'creditos' => $creditos,
            'saldoTotal' => $saldoTotal
        ]);
    }
}
