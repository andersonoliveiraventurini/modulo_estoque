<?php

namespace App\Livewire\Estoque;

use App\Models\StockMovementLog;
use App\Models\Produto;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Logs de Movimentação de Estoque')]
class StockMovementLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $tipo_movimentacao = '';
    public $colaborador_id = '';
    public $data_inicio;
    public $data_fim;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo_movimentacao' => ['except' => ''],
        'colaborador_id' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = StockMovementLog::with(['produto', 'posicao.corredor.armazem', 'colaborador'])
            ->latest();

        if ($this->search) {
            $query->whereHas('produto', function ($q) {
                $q->where('nome', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo_movimentacao) {
            $query->where('tipo_movimentacao', $this->tipo_movimentacao);
        }

        if ($this->colaborador_id) {
            $query->where('colaborador_id', $this->colaborador_id);
        }

        if ($this->data_inicio) {
            $query->whereDate('created_at', '>=', $this->data_inicio);
        }

        if ($this->data_fim) {
            $query->whereDate('created_at', '<=', $this->data_fim);
        }

        return view('livewire.estoque.stock-movement-logs', [
            'logs' => $query->paginate(20),
            'colaboradores' => User::all(),
        ]);
    }
}
