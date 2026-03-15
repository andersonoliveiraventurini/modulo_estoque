<?php

namespace App\Livewire\Faturas;

use App\Models\Fatura;
use App\Models\MetodoPagamento;
use App\Livewire\Forms\Faturas\BaixarFaturaForm;
use Livewire\Component;
use Livewire\Attributes\On;

class BaixarFaturaModal extends Component
{
    public BaixarFaturaForm $form;
    
    public bool $show = false;
    public $metodosDisponiveis = [];

    public function mount()
    {
        $this->metodosDisponiveis = MetodoPagamento::where('ativo', true)->get();
    }

    #[On('abrir-baixa-fatura')]
    public function openModal($faturaId)
    {
        $fatura = Fatura::findOrFail($faturaId);
        $this->form->setFatura($fatura);
        $this->resetValidation();
        $this->show = true;
    }

    public function salvar()
    {
        $this->form->salvarBaixa();
        
        $this->show = false;
        
        // Dispara evento global notificando a tabela/componente pai de que rolou sucesso
        $this->dispatch('fatura-baixada');
        
        // Se houver um modulo de Toast notification como no flux-pro, você injeta aqui.
        // ex: \Flux::toast('Fatura baixada com sucesso!');
    }

    public function render()
    {
        return view('livewire.faturas.baixar-fatura-modal');
    }
}
