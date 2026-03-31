<?php

namespace App\Livewire\Estoque;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\Posicao;
use Livewire\Component;

class AlocacaoModal extends Component
{
    public $show = false;
    public $productId;
    public $productName;
    public $totalQuantity;
    public $allocations = []; // Array of ['posicao_id' => X, 'quantity' => Y]
    public $availablePositions = [];

    protected $listeners = ['abrir-alocacao' => 'open'];

    public function mount()
    {
        // For simplicity, we can load active positions.
        // In a real system with thousands, this should be a search.
        $this->availablePositions = Posicao::with('corredor.armazem')
            ->whereHas('corredor.armazem', function($q) {
                $q->where('is_active', true);
            })
            ->get();
    }

    public function open($data)
    {
        $this->productId = $data['productId'];
        $this->productName = $data['productName'];
        $this->totalQuantity = $data['totalQuantity'];
        $this->allocations = $data['existingAllocations'] ?? [];
        $this->show = true;
    }

    public function addAllocation($posicaoId)
    {
        $this->allocations[] = [
            'posicao_id' => $posicaoId,
            'quantity' => 0,
            'posicao_nome' => Posicao::find($posicaoId)->nome_completo
        ];
    }

    public function removeAllocation($index)
    {
        unset($this->allocations[$index]);
        $this->allocations = array_values($this->allocations);
    }

    public function confirm()
    {
        $totalAllocated = array_sum(array_column($this->allocations, 'quantity'));
        
        if ($totalAllocated != $this->totalQuantity) {
            $this->dispatch('swal:error', [
                'title' => 'Quantidade Incorreta',
                'text' => "A soma das alocações ($totalAllocated) deve ser igual ao total do item ({$this->totalQuantity})."
            ]);
            return;
        }

        $this->dispatch('alocacao-confirmada', [
            'productId' => $this->productId,
            'allocations' => $this->allocations
        ]);
        
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.estoque.alocacao-modal');
    }
}
