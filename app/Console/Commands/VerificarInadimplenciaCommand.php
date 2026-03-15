<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerificarInadimplenciaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faturas:verificar-inadimplencia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica faturas pendentes e vencidas para marcar como inadimplentes';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\FaturaService $faturaService)
    {
        $this->info('Iniciando verificação de inadimplência...');
        \Illuminate\Support\Facades\Log::info('Comando faturas:verificar-inadimplencia iniciado.');

        try {
            $quantidadeAfetada = $faturaService->verificarInadimplencia();
            
            $this->info("Verificação concluída. {$quantidadeAfetada} fatura(s) marcada(s) como vencida(s).");
            \Illuminate\Support\Facades\Log::info("Comando faturas:verificar-inadimplencia concluído.", ['afetadas' => $quantidadeAfetada]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro ao verificar inadimplência: {$e->getMessage()}");
            \Illuminate\Support\Facades\Log::error("Erro no comando faturas:verificar-inadimplencia", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
