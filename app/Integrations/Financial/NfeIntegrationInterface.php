<?php

namespace App\Integrations\Financial;

use App\Models\Orcamento;

interface NfeIntegrationInterface
{
    /**
     * Envia o orçamento para emissão de nota fiscal.
     * 
     * @param Orcamento $orcamento
     * @return array [success => bool, protocol => string, message => string]
     */
    public function emitir(Orcamento $orcamento): array;

    /**
     * Consulta o status de uma nota emitida.
     */
    public function consultar(string $protocolo): array;

    /**
     * Cancela uma nota fiscal.
     */
    public function cancelar(string $protocolo, string $motivo): array;
}
