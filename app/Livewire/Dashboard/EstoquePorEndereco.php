<?php

namespace App\Livewire\Dashboard;

use App\Models\MovimentacaoProduto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EstoquePorEndereco extends Component
{
    public function render()
    {
        $dados = DB::table('movimentacao_produtos')
            ->join('movimentacoes', 'movimentacoes.id', '=', 'movimentacao_produtos.movimentacao_id')
            ->leftJoin('armazens', 'armazens.id', '=', 'movimentacao_produtos.armazem_id')
            ->leftJoin('corredores', 'corredores.id', '=', 'movimentacao_produtos.corredor_id')
            ->leftJoin('posicoes', 'posicoes.id', '=', 'movimentacao_produtos.posicao_id')
            ->where('movimentacoes.status', 'aprovado')
            ->select(
                'armazens.nome as armazem',
                'corredores.nome as corredor',
                'posicoes.nome as posicao',
                DB::raw("SUM(CASE WHEN movimentacoes.tipo = 'entrada' THEN movimentacao_produtos.quantidade ELSE -movimentacao_produtos.quantidade END) as saldo")
            )
            ->groupBy('armazens.nome', 'corredores.nome', 'posicoes.nome')
            ->having('saldo', '>', 0)
            ->orderBy('armazens.nome')
            ->orderBy('corredores.nome')
            ->get();

        return view('livewire.dashboard.estoque-por-endereco', [
            'estoquePorEndereco' => $dados
        ]);
    }
}
