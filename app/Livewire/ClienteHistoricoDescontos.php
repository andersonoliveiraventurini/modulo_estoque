<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Desconto;
use Livewire\WithPagination;

class ClienteHistoricoDescontos extends Component
{
    use WithPagination;

    public $clienteId;

    public function mount($clienteId)
    {
        $this->clienteId = $clienteId;
    }

    public function render()
    {
        $descontos = Desconto::with(['user', 'aprovadoPor', 'rejeitadoPor'])
            ->where('cliente_id', $this->clienteId)
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.cliente-historico-descontos', [
            'descontos' => $descontos
        ]);
    }
}
