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
            foreach ($orcamento->itens as $oi) {
                $produto = $oi->produto;
                $quantidade = (float) $oi->quantidade;

                // valida mínimo
                if (!$this->checarEstoqueMinimo($produto, $quantidade)) {
                    // apenas registra reserva e risco — a decisão de bloquear pode ser tratada na UI
                }

                EstoqueReserva::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'status' => 'ativa',
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
                $produto = $ci->produto;
                $q = (float) $ci->qty_conferida;

                if ($q <= 0) continue;

                // baixa estoque
                $produto->decrement('estoque_atual', $q);

                // movimentação (seu projeto já tem MovimentacaoController/Model)
                // aqui você pode criar a linha de saída

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