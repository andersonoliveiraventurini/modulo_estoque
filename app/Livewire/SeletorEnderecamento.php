<?php

namespace App\Livewire;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\Posicao;
use Livewire\Component;

class SeletorEnderecamento extends Component
{
    public $armazem_id;
    public $corredor_id;
    public $posicao_id;

    public $armazens = [];
    public $corredores = [];
    public $posicoes = [];

    public function mount($armazemId = null, $corredorId = null, $posicaoId = null)
    {
        $this->armazens = Armazem::all();
        $this->armazem_id = $armazemId;
        $this->corredor_id = $corredorId;
        $this->posicao_id = $posicaoId;

        if ($this->armazem_id) {
            $this->updatedArmazemId($this->armazem_id);
        }
        if ($this->corredor_id) {
            $this->updatedCorredorId($this->corredor_id);
        }
    }

    public function updatedArmazemId($value)
    {
        $this->corredores = $value ? Corredor::where('armazem_id', $value)->get() : [];
        $this->corredor_id = null;
        $this->posicoes = [];
        $this->posicao_id = null;
        
        $this->dispatch('enderecamento-atualizado', [
            'armazem_id' => $this->armazem_id,
            'corredor_id' => null,
            'posicao_id' => null
        ]);
    }

    public function updatedCorredorId($value)
    {
        $this->posicoes = $value ? Posicao::where('corredor_id', $value)->get() : [];
        $this->posicao_id = null;

        $this->dispatch('enderecamento-atualizado', [
            'armazem_id' => $this->armazem_id,
            'corredor_id' => $this->corredor_id,
            'posicao_id' => null
        ]);
    }

    public function updatedPosicaoId($value)
    {
        $this->dispatch('enderecamento-atualizado', [
            'armazem_id' => $this->armazem_id,
            'corredor_id' => $this->corredor_id,
            'posicao_id' => $this->posicao_id
        ]);
    }

    public function render()
    {
        return view('livewire.seletor-enderecamento');
    }
}
