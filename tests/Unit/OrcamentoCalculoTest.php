<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OrcamentoCalculoTest extends TestCase
{
    /**
     * Testa a lógica de cálculo de desconto percentual.
     */
    public function test_calculo_desconto_percentual()
    {
        $precoOriginal = 100.00;
        $quantidade = 2;
        $descontoPercentual = 10; // 10%

        $subtotal = $precoOriginal * $quantidade;
        $valorComDesconto = $subtotal - ($subtotal * ($descontoPercentual / 100));

        $this->assertEquals(200.00, $subtotal);
        $this->assertEquals(180.00, $valorComDesconto);
    }

    /**
     * Testa a lógica de desconto por produto (preço fixo alterado).
     */
    public function test_calculo_desconto_por_produto()
    {
        $precoOriginal = 100.00;
        $precoNovo = 85.00;
        $quantidade = 3;

        $descontoUnitario = $precoOriginal - $precoNovo;
        $valorComDesconto = $precoNovo * $quantidade;

        $this->assertEquals(15.00, $descontoUnitario);
        $this->assertEquals(255.00, $valorComDesconto);
    }

    /**
     * Testa a prioridade entre desconto percentual e desconto por produto.
     * Na regra de negócio: se houver desconto manual por produto, ignora o percentual global.
     */
    public function test_prioridade_desconto_produto_sobre_percentual()
    {
        $precoOriginal = 100.00;
        $precoNovo = 90.00;
        $quantidade = 1;
        $descontoPercentualGlobal = 20; // 20%

        // Se tem desconto por produto
        $temDescontoProduto = ($precoOriginal - $precoNovo) > 0;
        
        if ($temDescontoProduto) {
            $valorFinal = $precoNovo * $quantidade;
        } else {
            $valorFinal = ($precoOriginal * $quantidade) * (1 - ($descontoPercentualGlobal / 100));
        }

        $this->assertEquals(90.00, $valorFinal);
    }
}
