<?php

namespace App\Listeners;

use App\Events\OrcamentoCancelado;
use App\Services\EstoqueService;

class LiberarReservaAoCancelar
{
    public function handle(OrcamentoCancelado $event): void
    {
        app(EstoqueService::class)->liberarReservaDoOrcamento($event->orcamento);
    }
}
