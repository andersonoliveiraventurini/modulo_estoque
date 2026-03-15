<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use App\Models\Venda;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPaidOrcamentosToVendas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-vendas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza orçamentos pagos antigos para a tabela de vendas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de orçamentos pagos...');

        $orcamentos = Orcamento::where('status', 'Pago')
            ->whereDoesntHave('pagamentos', function($q) {
                // Apenas se não houver registro na tabela de vendas ainda
                // Como Venda tem orcamento_id, podemos checar por orcamento_id na tabela vendas
            })
            ->get();
            
        // Correção da lógica: checar via Venda::where('orcamento_id', ...)
        $count = 0;
        
        foreach ($orcamentos as $orc) {
            if (!Venda::where('orcamento_id', $orc->id)->exists()) {
                Venda::create([
                    'orcamento_id' => $orc->id,
                    'cliente_id'   => $orc->cliente_id,
                    'vendedor_id'  => $orc->vendedor_id,
                    'valor_total'  => $orc->valor_com_desconto > 0 ? $orc->valor_com_desconto : $orc->valor_total_itens,
                    'status'       => 'concluida',
                    'data_venda'   => $orc->data_pagamento ?? $orc->updated_at,
                ]);
                $count++;
            }
        }

        $this->info("Sincronização concluída! {$count} orçamentos convertidos em vendas.");
        Log::info("Sincronização de vendas concluída: {$count} registros criados.");
    }
}
