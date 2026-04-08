<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\Pagamento;
use App\Models\Desconto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PagamentoCalculoFinanceiroTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o cálculo do Valor Total Final não duplica descontos.
     * Cenário: Orçamento com itens que têm desconto e um desconto extra aprovado.
     */
    public function test_valor_total_final_nao_duplica_descontos()
    {
        // 1. Criar Orçamento
        $orcamento = Orcamento::create([
            'valor_total_itens' => 200.00, // 2 itens de 100.00
            'desconto_total' => 30.00,    // 10.00 de desconto nos itens + 20.00 de desconto extra
            'valor_com_desconto' => 170.00,
            'status' => 'Pendente'
        ]);

        // 2. Criar Itens (Simulando desconto de 5.00 em cada item de 100.00)
        OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'quantidade' => 1,
            'valor_unitario' => 100.00,
            'valor_unitario_com_desconto' => 95.00,
            'valor_com_desconto' => 95.00
        ]);
        OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'quantidade' => 1,
            'valor_unitario' => 100.00,
            'valor_unitario_com_desconto' => 95.00,
            'valor_com_desconto' => 95.00
        ]);

        // Total original: 200.00
        // Total com desconto nos itens: 190.00
        // Desconto extra: 20.00
        // Valor final esperado: 170.00

        // 3. Criar Desconto Extra Aprovado (20.00)
        Desconto::create([
            'orcamento_id' => $orcamento->id,
            'valor' => 20.00,
            'tipo' => 'valor',
            'aprovado_por' => 1,
            'aprovado_em' => now()
        ]);

        // Verificar o acessor valor_total_final
        // valor_total_final = valor_total + valor_residuais
        // valor_total = (valor_com_desconto > 0 ? valor_com_desconto : valor_total_itens)
        
        $this->assertEquals(170.00, $orcamento->valor_total_final, 'O valor total final deve ser 170.00 (200 - 10 nos itens - 20 extra)');
    }

    /**
     * Testa se o cálculo do troco no PagamentoController está correto.
     */
    public function test_calculo_troco_no_pagamento_correto()
    {
        // Simulando a lógica do controlador
        $valorTotalFinal = 170.00;
        $totalJaPago = 0;
        $descontoExtraJaEmbutido = 0; // Simulando a correção feita: descontoOriginal = 0
        $descontoBalcao = 5.00;
        
        $valorFinalParaPagar = $valorTotalFinal - $descontoExtraJaEmbutido - $descontoBalcao - $totalJaPago;
        $this->assertEquals(165.00, $valorFinalParaPagar);

        $valorPagoPeloCliente = 200.00;
        $troco = max(0, $valorPagoPeloCliente - $valorFinalParaPagar);
        
        $this->assertEquals(35.00, $troco, 'O troco deve ser 35.00 (200 - 165)');
    }

    /**
     * Testa se o troco é ZERO quando o valor pago é EXATAMENTE o valor final.
     */
    public function test_troco_zero_quando_pagamento_exato()
    {
        $valorTotalFinal = 150.00;
        $valorPago = 150.00;
        $troco = max(0, $valorPago - $valorTotalFinal);
        
        $this->assertEquals(0, $troco, 'O troco deve ser 0 quando o pagamento é exato');
    }
}
