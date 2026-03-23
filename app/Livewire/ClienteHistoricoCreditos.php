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
        $creditos = ClienteCreditos::with(['usuarioCriacao', 'movimentacoes.user'])
            ->where('cliente_id', $this->clienteId)
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.cliente-historico-creditos', [
            'creditos' => $creditos
        ]);
    }
}
