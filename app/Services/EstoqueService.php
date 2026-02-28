<?php

namespace App\Services;

use App\Models\Conferencia;
use App\Models\EstoqueReserva;
use App\Models\Orcamento;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

final class EstoqueService
{
    public function reservarParaOrcamento(Orcamento $orcamento): void
    {
        $orcamento->load('itens.produto');

        DB::transaction(function () use ($orcamento) {
            foreach ($orcamento->itens->whereNotNull('produto_id') as $oi) { // ✅ pula encomendas
                $produto    = $oi->produto;
                $quantidade = (float) $oi->quantidade;

                if (!$produto) continue; // ✅ segurança extra

                $this->checarEstoqueMinimo($produto, $quantidade);

                EstoqueReserva::create([
                    'orcamento_id'  => $orcamento->id,
                    'produto_id'    => $produto->id,
                    'quantidade'    => $quantidade,
                    'status'        => 'ativa',
                    'criado_por_id' => auth()->id(),
                ]);
            }
        });
    }

    public function liberarReservas(Orcamento $orcamento, array $consumos): void
    {
        // $consumos: [produto_id => qty_consumida]
        $reservas = EstoqueReserva::where('orcamento_id', $orcamento->id)
            ->where('status', 'ativa')->get();

        foreach ($reservas as $reserva) {
            $consumir = (float) ($consumos[$reserva->produto_id] ?? 0);
            if ($consumir > 0) {
                $reserva->status = 'consumida';
            } else {
                $reserva->status = 'cancelada';
            }
            $reserva->save();
        }
    }

    public function baixarSaida(Conferencia $conf): void
    {
        $conf->load('itens.produto', 'orcamento');

        DB::transaction(function () use ($conf) {
            $consumos = [];

            foreach ($conf->itens as $ci) {
                //  Pula itens de encomenda sem produto físico
                if ($ci->is_encomenda ?? false) continue;
                if (!$ci->produto) continue;

                $produto = $ci->produto;
                $q       = (float) $ci->qty_conferida;

                if ($q <= 0) continue;

                $produto->decrement('estoque_atual', $q);

                $consumos[$produto->id] = ($consumos[$produto->id] ?? 0) + $q;
            }

            $this->liberarReservas($conf->orcamento, $consumos);
        });
    }

    public function checarEstoqueMinimo(Produto $produto, float $quantidadeReservar): bool
    {
        $reservado = (float) EstoqueReserva::where('produto_id', $produto->id)
            ->where('status', 'ativa')
            ->sum('quantidade');

        $disponivelAposReserva = (float) $produto->estoque_atual - $reservado - $quantidadeReservar;
        $min = (float) ($produto->estoque_minimo ?? 0);

        return $disponivelAposReserva >= $min;
    }

     public function liberarReservaDoOrcamento(Orcamento $orcamento): void
    {
        // Reutilizamos a lógica do método liberarReservas, informando que
        // o consumo de todos os produtos foi zero.
        // Ao passar um array de consumos vazio, a lógica interna do liberarReservas
        // irá automaticamente marcar todas as reservas como 'cancelada'.
        $this->liberarReservas($orcamento, []);
    }
}
