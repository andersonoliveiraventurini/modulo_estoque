<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class RelatorioVendasTest extends TestCase
{
    /**
     * Teste do cálculo de número de meses baseado em dias.
     */
    public function test_calculo_numero_meses()
    {
        $inicio = Carbon::parse('2026-01-01');
        $fim = Carbon::parse('2026-01-31');
        
        $diffDias = $inicio->diffInDays($fim);
        $numMeses = max(0.1, round($diffDias / 30, 2));
        
        // 30 dias / 30 = 1.0 mês
        $this->assertEquals(1.0, $numMeses);

        $fim2 = Carbon::parse('2026-02-15'); // 45 dias
        $diffDias2 = $inicio->diffInDays($fim2);
        $numMeses2 = max(0.1, round($diffDias2 / 30, 2));
        
        $this->assertEquals(1.5, $numMeses2);
    }

    /**
     * Teste do cálculo de estoque mínimo sugerido.
     */
    public function test_calculo_estoque_minimo_sugerido()
    {
        $totalVendido = 100;
        $numMeses = 2.0;

        $qtdPorMes = $totalVendido / $numMeses; // 50
        $estoqueSugerido = round($qtdPorMes, 2); // 50

        $this->assertEquals(50, $qtdPorMes);
        $this->assertEquals(50, $estoqueSugerido);
    }

    /**
     * Teste de validação de datas.
     */
    public function test_validacao_datas()
    {
        $inicio = Carbon::parse('2026-02-01');
        $fim = Carbon::parse('2026-01-01');

        $isValid = $fim->greaterThanOrEqualTo($inicio);
        $this->assertFalse($isValid);

        $fim2 = Carbon::parse('2026-03-01');
        $isValid2 = $fim2->greaterThanOrEqualTo($inicio);
        $this->assertTrue($isValid2);
    }
}
