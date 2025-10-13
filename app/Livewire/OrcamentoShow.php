<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orcamento;

class OrcamentoShow extends Component
{
    public $orcamento;
    public $status;
    public $desconto_aprovado;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount($id)
    {
        $this->orcamento = Orcamento::with(['cliente', 'itens', 'vidros', 'transporte'])->findOrFail($id);
        $this->status = $this->orcamento->status;
        $this->desconto_aprovado = $this->orcamento->desconto_aprovado;
    }

    public function atualizarStatus()
    {
        $this->orcamento->update(['status' => $this->status]);
        $this->dispatch('notify', 'Status atualizado com sucesso!');
    }

    public function aprovarDesconto()
    {
        $this->orcamento->update(['desconto_aprovado' => $this->desconto_aprovado]);
        $this->dispatch('notify', 'Desconto aprovado com sucesso!');
    }

    public function render()
    {
        // Atualiza o relacionamento ao recarregar
        $this->orcamento->load(['cliente', 'itens', 'vidros', 'transporte']);
        return view('livewire.orcamento-show');
    }
}
