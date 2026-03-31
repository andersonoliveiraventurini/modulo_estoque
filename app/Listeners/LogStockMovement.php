<?php

namespace App\Listeners;

use App\Events\StockMovementRegistered;
use App\Models\StockMovementLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogStockMovement implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(StockMovementRegistered $event): void
    {
        try {
            StockMovementLog::create($event->data);
        } catch (\Exception $e) {
            Log::error("Erro ao registrar log de movimentação de estoque: " . $e->getMessage(), [
                'data' => $event->data
            ]);
        }
    }
}
