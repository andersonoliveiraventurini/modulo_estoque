<?php

namespace App\Livewire\Faturas;

use Livewire\Component;
use App\Models\Fatura;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

#[Title('Faturas e Inadimplência')]
class ListaFaturas extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $dataInicio = '';
    public $dataFim = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'status'     => ['except' => ''],
        'dataInicio' => ['except' => ''],
        'dataFim'    => ['except' => ''],
    ];

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingStatus()    { $this->resetPage(); }
    public function updatingDataInicio(){ $this->resetPage(); }
    public function updatingDataFim()   { $this->resetPage(); }

    public function limparFiltros(): void
    {
        $this->reset(['search', 'status', 'dataInicio', 'dataFim']);
        $this->resetPage();
    }

    #[On('fatura-baixada')]
    public function refreshTable()
    {
        // Re-renders the component to fetch updated status/values
    }

    public function render()
    {
        $baseQuery = Fatura::query()
            ->when($this->search, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('nome', 'like', '%' . $this->search . '%')
                      ->orWhere('cpf', 'like', '%' . $this->search . '%')
                      ->orWhere('cnpj', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $this->dataFim));

        // KPI summary (always uses the same filters, without pagination)
        $stats = [
            'total_a_receber' => (clone $baseQuery)->whereIn('status', ['pendente', 'parcial', 'vencido'])
                ->sum(DB::raw('valor_total - valor_pago')),
            'total_vencido'   => (clone $baseQuery)->where('status', 'vencido')
                ->sum(DB::raw('valor_total - valor_pago')),
            'total_pago_mes'  => (clone $baseQuery)->where('status', 'pago')
                ->whereMonth('data_pagamento', now()->month)
                ->sum('valor_pago'),
            'count_pendentes' => (clone $baseQuery)->whereIn('status', ['pendente', 'parcial', 'vencido'])->count(),
        ];

        $faturas = $baseQuery->with(['cliente', 'orcamento', 'pedido'])
            ->orderBy('data_vencimento', 'asc')
            ->paginate(15);

        return view('livewire.faturas.lista-faturas', [
            'faturas' => $faturas,
            'stats'   => $stats,
        ]);
    }
}
