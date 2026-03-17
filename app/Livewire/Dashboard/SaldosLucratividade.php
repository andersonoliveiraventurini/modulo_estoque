<?php

namespace App\Livewire\Dashboard;

use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SaldosLucratividade extends Component
{
    public function render()
    {
        $stats = Produto::query()
            ->where('estoque_atual', '>', 0)
            ->selectRaw('SUM(estoque_atual * preco_custo) as total_custo')
            ->selectRaw('SUM(estoque_atual * preco_venda) as total_venda')
            ->first();

        $totalCusto = (float) ($stats instanceof Produto || $stats instanceof \Illuminate\Database\Eloquent\Model ? $stats->total_custo : 0);
        $totalVenda = (float) ($stats instanceof Produto || $stats instanceof \Illuminate\Database\Eloquent\Model ? $stats->total_venda : 0);
        $lucroPotencial = $totalVenda - $totalCusto;
        
        $markup = $totalCusto > 0 
            ? (($totalVenda - $totalCusto) / $totalCusto) * 100 
            : 0;

        return view('livewire.dashboard.saldos-lucratividade', [
            'totalCusto' => $totalCusto,
            'totalVenda' => $totalVenda,
            'lucroPotencial' => $lucroPotencial,
            'markup' => $markup
        ]);
    }
}
