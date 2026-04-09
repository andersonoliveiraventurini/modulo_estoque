<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use App\Models\SystemAlert;
use App\Models\SystemLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirarOrcamentosPendente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orcamentos:expirar-pendentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifica orçamentos pendentes com validade vencida e altera seu status para Expirado.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando rotina de expiração de orçamentos...');

        // Busca orçamentos com status "Pendente" que tenham a validade vencida
        // A validade agora é de 5 dias (conforme alteração nos controllers)
        $orcamentosVencidos = Orcamento::where('status', 'Pendente')
            ->whereNotNull('validade')
            ->where('validade', '<', now())
            ->get();

        if ($orcamentosVencidos->isEmpty()) {
            $this->info('Nenhum orçamento pendente vencido encontrado.');
            return 0;
        }

        $count = 0;
        foreach ($orcamentosVencidos as $orcamento) {
            DB::beginTransaction();
            try {
                $statusAntigo = $orcamento->status;
                $orcamento->update([
                    'status' => 'Expirado',
                    'updated_at' => now(),
                ]);

                // Log de auditoria no SystemLog
                SystemLog::create([
                    'level' => 'info',
                    'message' => "Orçamento #{$orcamento->id} expirado automaticamente por validade vencida.",
                    'context' => [
                        'orcamento_id' => $orcamento->id,
                        'status_antigo' => $statusAntigo,
                        'status_novo' => 'Expirado',
                        'validade' => $orcamento->validade ? $orcamento->validade->format('Y-m-d H:i:s') : null,
                    ],
                    'user_id' => null, // Executado pelo sistema
                ]);

                // Notificação no SystemAlert
                SystemAlert::create([
                    'tipo' => 'pending_approval',
                    'mensagem' => "O orçamento #{$orcamento->id} do cliente " . ($orcamento->cliente->nome ?? 'N/A') . " expirou por falta de aprovação no prazo de 5 dias.",
                    'orcamento_id' => $orcamento->id,
                ]);

                DB::commit();
                $count++;
                $this->line("Orçamento #{$orcamento->id} atualizado para Expirado.");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Erro ao expirar orçamento #{$orcamento->id}: " . $e->getMessage());
                $this->error("Erro ao expirar orçamento #{$orcamento->id}.");
            }
        }

        $this->info("Rotina concluída. {$count} orçamentos expirados.");
        return 0;
    }
}
