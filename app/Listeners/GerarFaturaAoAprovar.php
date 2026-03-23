<?php

namespace App\Listeners;

use App\Events\OrcamentoAprovado;
use App\Services\FaturaService;

class GerarFaturaAoAprovar
{
    public function handle(OrcamentoAprovado $event): void
    {
        app(FaturaService::class)->gerarFaturaPorOrcamento($event->orcamento);
    }
}
