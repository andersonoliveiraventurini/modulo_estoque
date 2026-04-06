<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corrigir orçamentos que possuem itens mas estão com valor total zerado
        $orcamentos = DB::table('orcamentos')
            ->where('valor_total_itens', 0)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('orcamento_itens')
                      ->whereRaw('orcamento_itens.orcamento_id = orcamentos.id');
            })
            ->get();

        foreach ($orcamentos as $orc) {
            // Calcular total bruto dos itens
            $totalBruto = DB::table('orcamento_itens')
                ->where('orcamento_id', $orc->id)
                ->sum(DB::raw('valor_unitario * quantidade'));

            // Calcular total bruto dos vidros
            $totalVidros = DB::table('orcamento_vidros')
                ->where('orcamento_id', $orc->id)
                ->sum('valor_total');

            $totalGeralBruto = $totalBruto + $totalVidros;

            // Calcular total com desconto
            // Para itens
            $totalComDescontoItens = DB::table('orcamento_itens')
                ->where('orcamento_id', $orc->id)
                ->get()
                ->sum(function($item) {
                    return $item->valor_com_desconto > 0 ? $item->valor_com_desconto : ($item->valor_unitario * $item->quantidade);
                });

            // Para vidros
            $totalComDescontoVidros = DB::table('orcamento_vidros')
                ->where('orcamento_id', $orc->id)
                ->get()
                ->sum(function($vidro) {
                    return $vidro->valor_com_desconto > 0 ? $vidro->valor_com_desconto : $vidro->valor_total;
                });

            $totalGeralComDesconto = $totalComDescontoItens + $totalComDescontoVidros;

            // Subtrair desconto fixo global se houver
            $descontoFixoGlobal = DB::table('descontos')
                ->where('orcamento_id', $orc->id)
                ->where('tipo', 'fixo')
                ->sum('valor');
            
            $totalGeralComDesconto = max(0, $totalGeralComDesconto - $descontoFixoGlobal);

            // Atualizar o orçamento
            DB::table('orcamentos')
                ->where('id', $orc->id)
                ->update([
                    'valor_total_itens' => round($totalGeralBruto, 2),
                    'valor_com_desconto' => round($totalGeralComDesconto, 2),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é necessário reverter dados corrigidos.
    }
};
