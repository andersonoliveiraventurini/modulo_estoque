<?php

namespace App\Integrations\Financial\Mock;

use App\Integrations\Financial\NfeIntegrationInterface;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Log;

class MockNfeService implements NfeIntegrationInterface
{
    public function emitir(Orcamento $orcamento): array
    {
        Log::info("[MOCK NFE] Emitindo nota para Orçamento #{$orcamento->id}");

        return [
            'success' => true,
            'protocol' => 'MOCK-' . strtoupper(uniqid()),
            'message' => 'Nota fiscal emitida em ambiente de homologação (MOCK).',
            'xml_url' => '#'
        ];
    }

    public function consultar(string $protocolo): array
    {
        return [
            'success' => true,
            'status' => 'Autorizada',
            'protocol' => $protocolo
        ];
    }

    public function cancelar(string $protocolo, string $motivo): array
    {
        Log::warning("[MOCK NFE] Cancelando nota $protocolo. Motivo: $motivo");
        return [
            'success' => true,
            'message' => 'Nota cancelada com sucesso (MOCK).'
        ];
    }
}
