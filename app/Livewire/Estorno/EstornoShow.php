<?php

namespace App\Livewire\Estorno;

use App\Models\Estorno;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EstornoShow extends Component
{
    use AuthorizesRequests;

    public Estorno $estorno;

    public function mount(Estorno $estorno)
    {
        $this->authorize('view', $estorno);
        $this->estorno = $estorno->load(['pagamento', 'solicitante', 'aprovador']);
    }

    public function render()
    {
        return view('livewire.estorno.estorno-show')
             ->layout('components.layouts.app', ['title' => 'Detalhes do Estorno']);
    }
}
