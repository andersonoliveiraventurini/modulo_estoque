<?php

namespace App\Integrations\Financial\Mock;

use App\Integrations\Financial\BoletoIntegrationInterface;
use App\Models\Fatura;
use Illuminate\Support\Facades\Log;

class MockBoletoService implements BoletoIntegrationInterface
{
    public function gerar(Fatura $fatura): array
    {
        Log::info("[MOCK BOLETO] Gerando boleto para Fatura #{$fatura->id}");

        return [
            'success'   => true,
            'barcode'   => '00190.00009 02345.678901 23456.789012 8 00000000000000',
            'url'       => '#',
            'id'        => 'BOL-' . strtoupper(uniqid()),
            'message'   => 'Boleto gerado em ambiente de simulação.'
        ];
    }

    public function consultar(string $boletoId): array
    {
        return [
            'success' => true,
            'status'  => 'Pendente',
            'id'      => $boletoId
        ];
    }

    public function cancelar(string $boletoId): array
    {
        Log::warning("[MOCK BOLETO] Cancelando boleto $boletoId");
        return [
            'success' => true,
            'message' => 'Boleto cancelado no banco (MOCK).'
        ];
    }
}
