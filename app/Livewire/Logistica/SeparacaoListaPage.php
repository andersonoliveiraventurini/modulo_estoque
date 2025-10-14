<?php

namespace App\Livewire\Logistica;

use App\Models\EstoqueReserva;
use App\Models\PickingItem;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class SeparacaoListaPage extends Component
{
    use WithPagination;

    // Filtros (sem tipagem para máxima compatibilidade)
    public $f_cliente = '';
    public $f_sku = '';
    public $f_status_lote = 'em_separacao';// aberto|em_separacao
    public $f_busca = '';
    public $f_armazem_id = null;

    public $perPage = 20;

    protected $queryString = [
        'f_cliente' => ['except' => ''],
        'f_sku' => ['except' => ''],
        'f_status_item' => ['except' => ''],
        'f_status_lote' => ['except' => 'em_separacao'],
        'f_busca' => ['except' => ''],
        'f_armazem_id' => ['except' => null],
        'perPage' => ['except' => 20],
        'page' => ['except' => 1],
    ];

    public function updating($field)
    {
        if (in_array($field, ['f_cliente','f_sku','f_status_item','f_status_lote','f_busca','f_armazem_id','perPage'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = PickingItem::query()
            ->with(['produto', 'batch.orcamento.cliente', 'batch'])
            ->whereHas('batch', function (Builder $q) {
                $status = $this->f_status_lote ?: 'em_separacao';
                if ($status === 'aberto') {
                    $q->whereIn('status', ['aberto', 'em_separacao']);
                } else {
                    $q->whereIn('status', ['em_separacao', 'aberto']);
                }
                if (!empty($this->f_armazem_id)) {
                    $q->where('armazem_id', $this->f_armazem_id);
                }
            });

        if ($this->f_status_item !== '') {
            $query->where('status', $this->f_status_item);
        }

        if ($this->f_sku !== '') {
            $sku = trim($this->f_sku);
            $query->whereHas('produto', function (Builder $q) use ($sku) {
                $q->where('sku', 'like', "%{$sku}%")
                  ->orWhere('codigo_barras', 'like', "%{$sku}%")
                  ->orWhere('nome', 'like', "%{$sku}%");
            });
        }

        if ($this->f_cliente !== '') {
            $cliente = trim($this->f_cliente);
            $query->whereHas('batch.orcamento.cliente', function (Builder $q) use ($cliente) {
                $q->where('nome', 'like', "%{$cliente}%")
                  ->orWhere('nome_fantasia', 'like', "%{$cliente}%");
            });
        }

        if ($this->f_busca !== '') {
            $term = trim($this->f_busca);
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('produto', function (Builder $qp) use ($term) {
                    $qp->where('nome', 'like', "%{$term}%")
                       ->orWhere('sku', 'like', "%{$term}%")
                       ->orWhere('codigo_barras', 'like', "%{$term}%");
                })->orWhereHas('batch.orcamento', function (Builder $qo) use ($term) {
                    $qo->where('id', (int) $term);
                });
            });
        }

        $itens = $query->orderByDesc('id')->paginate((int) $this->perPage);

        // Reservas agregadas por produto (evita N+1)
        $reservasPorProduto = [];
        if ($itens->count() > 0) {
            $produtoIds = $itens->pluck('produto_id')->filter()->unique()->values();
            if ($produtoIds->count() > 0) {
                $rows = EstoqueReserva::selectRaw('produto_id, SUM(quantidade) as total')
                    ->whereIn('produto_id', $produtoIds)
                    ->where('status', 'ativa')
                    ->groupBy('produto_id')
                    ->get();
                foreach ($rows as $r) {
                    $reservasPorProduto[$r->produto_id] = (float) $r->total;
                }
            }
        }

        return view('livewire.logistica.separacao-lista-page', [
            'itens' => $itens,
            'reservasPorProduto' => $reservasPorProduto,
        ]);
    }
}