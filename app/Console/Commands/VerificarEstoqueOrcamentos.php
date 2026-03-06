<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarEstoqueOrcamentos extends Command
{
    protected $signature   = 'orcamentos:verificar-estoque';
    protected $description = 'Verifica orçamentos Pendentes e marca como "Sem estoque" se faltar produto';

    public function handle(): void
    {
        $orcamentos = Orcamento::with(['itens.produto'])
            ->where('status', 'Pendente')
            ->get();

        $alterados = 0;

        foreach ($orcamentos as $orcamento) {
            // Ignora orçamentos de encomenda (sem produto físico em estoque próprio)
            if ($orcamento->encomenda) {
                continue;
            }

            $temItemSemEstoque = $orcamento->itens->contains(function ($item) {
                $produto = $item->produto;
                if (! $produto) return true;

                $disponivel = ($produto->estoque_atual ?? 0) - ($produto->estoque_web ?? 0);
                return $disponivel < $item->quantidade;
            });

            if ($temItemSemEstoque) {
                $orcamento->status = 'Sem estoque';
                $orcamento->save();
                $alterados++;

                Log::info("Orçamento #{$orcamento->id} alterado para 'Sem estoque' pelo verificador agendado.");
            }
        }

        $this->info("Verificação concluída. {$alterados} orçamento(s) marcado(s) como 'Sem estoque'.");
    }
}