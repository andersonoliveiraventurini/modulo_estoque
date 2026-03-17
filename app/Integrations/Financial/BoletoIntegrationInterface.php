<?php

namespace App\Integrations\Financial;

use App\Models\Fatura;

interface BoletoIntegrationInterface
{
    /**
     * Gera um boleto para uma fatura.
     * 
     * @param Fatura $fatura
     * @return array [success => bool, barcode => string, url => string, id => string]
     */
    public function gerar(Fatura $fatura): array;

    /**
     * Consulta situação do boleto (pago, vencido, etc).
     */
    public function consultar(string $boletoId): array;

    /**
     * Cancela o boleto no banco.
     */
    public function cancelar(string $boletoId): array;
}
