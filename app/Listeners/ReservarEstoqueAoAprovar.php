<?php

namespace App\Listeners;

use App\Events\OrcamentoAprovado;
use App\Services\EstoqueService;

class ReservarEstoqueAoAprovar
{
    public function handle(OrcamentoAprovado $event): void
    {
        \Log::info("ReservarEstoqueAoAprovar@handle chamado para Orçamento #{$event->orcamento->id}");
        app(EstoqueService::class)->reservarParaOrcamento($event->orcamento);
    }
}
