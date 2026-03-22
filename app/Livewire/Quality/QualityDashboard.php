<?php

namespace App\Livewire\Quality;

use App\Models\NonConformity;
use App\Models\ProductReturn;
use Livewire\Component;
use Livewire\WithPagination;

class QualityDashboard extends Component
{
    use WithPagination;

    public $tab = 'returns'; // returns | rnc
    public $search = '';
    public $status_filter = '';
    public $date_start;
    public $date_end;

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedTab() { $this->resetPage(); }

    public function render()
    {
        $returns = ProductReturn::query()
            ->when($this->status_filter, fn($q) => $q->where('status', $this->status_filter))
            ->when($this->search, function($q) {
                $q->where('nr', 'like', "%{$this->search}%")
                  ->orWhereHas('cliente', fn($sq) => $sq->where('nome', 'like', "%{$this->search}%"));
            })
            ->with(['cliente', 'vendedor'])
            ->latest()
            ->paginate(10, ['*'], 'returnsPage');

        $rncs = NonConformity::query()
            ->when($this->search, function($q) {
                $q->where('nr', 'like', "%{$this->search}%")
                  ->orWhere('produto_nome', 'like', "%{$this->search}%")
                  ->orWhere('fornecedor_nome', 'like', "%{$this->search}%");
            })
            ->with(['usuario'])
            ->latest()
            ->paginate(10, ['*'], 'rncPage');

        return view('livewire.quality.quality-dashboard', [
            'returns' => $returns,
            'rncs' => $rncs
        ]);
    }
}
