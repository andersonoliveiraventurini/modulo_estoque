<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Expira créditos vencidos todos os dias às 00:00
        $schedule->command('creditos:expirar --force')
            ->daily()
            ->at('00:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Créditos vencidos expirados com sucesso');
            })
            ->onFailure(function () {
                \Log::error('Falha ao expirar créditos vencidos');
            });

        // Verifica estoque dos orçamentos Pendentes a cada 30 minutos
        $schedule->command('orcamentos:verificar-estoque')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Verificação de estoque concluída com sucesso');
            })
            ->onFailure(function () {
                \Log::error('Falha na verificação de estoque dos orçamentos');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
