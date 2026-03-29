<?php

namespace App\Listeners;

use App\Events\OrcamentoFinalizado;
use App\Services\EstoqueService;

class LiberarReservaAoFinalizar
{
    public function handle(OrcamentoFinalizado $event): void
    {
        app(EstoqueService::class)->consumirReservaDoOrcamento($event->orcamento);
    }
}
