<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricoFinanceiroIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'nome';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $clientes = Cliente::query()
            ->with(['analisesCredito'])
            ->when($this->search, function ($q) {
                $q->where('nome', 'like', "%{$this->search}%")
                  ->orWhere('cpf', 'like', "%{$this->search}%")
                  ->orWhere('cnpj', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.historico-financeiro-index', [
            'clientes' => $clientes,
        ])->layout('components.layouts.app');
    }
}
