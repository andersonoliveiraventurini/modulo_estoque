<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CondicoesPagamento;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\TipoTransporte;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrcamentoZeroValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_orcamento_with_zero_discount_and_guia()
    {
        // 1. Setup
        $user = User::factory()->create();
        Vendedor::factory()->create(['user_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        $condicao = CondicoesPagamento::factory()->create();
        $transporte = TipoTransporte::factory()->create();

        // 2. Request Data with "0,00"
        $data = [
            'cliente_id' => $cliente->id,
            'nome_obra' => 'Obra Zero',
            'condicao_id' => $condicao->id,
            'tipos_transporte' => $transporte->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
            'desconto_especifico' => '0,00',
            'guia_recolhimento' => '0,00',
            'valor_total' => '100,00',
            'itens' => [
                [
                    'id' => Produto::factory()->create()->id,
                    'quantidade' => 1,
                    'preco_unitario' => '100,00',
                ]
            ]
        ];

        // 3. Post to store
        $response = $this->actingAs($user)->post(route('orcamentos.store'), $data);

        // 4. Assert
        $response->assertRedirect();
        $orcamento = Orcamento::where('obra', 'Obra Zero')->first();
        $this->assertNotNull($orcamento);
        $this->assertEquals(0, $orcamento->desconto_especifico); // Should be normalized to 0 or handled by logic
        $this->assertEquals(0, $orcamento->guia_recolhimento);
        
        // Ensure no discount record was created for "0,00"
        $this->assertCount(0, $orcamento->descontos->where('tipo', 'fixo'));
    }

    public function test_can_update_orcamento_with_zero_discount_and_guia()
    {
        // 1. Setup
        $user = User::factory()->create();
        Vendedor::factory()->create(['user_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        $condicao = CondicoesPagamento::factory()->create();
        $transporte = TipoTransporte::factory()->create();

        $orcamento = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'obra' => 'Original',
            'status' => 'Pendente',
            'condicao_id' => $condicao->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
        ]);

        // 2. Update Data with "0,00"
        $updateData = [
            'obra' => 'Obra Atualizada Zero',
            'condicao_id' => $condicao->id,
            'tipos_transporte' => $transporte->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
            'desconto_especifico' => '0,00',
            'guia_recolhimento' => '0,00',
            'valor_total' => '100,00',
        ];

        // 3. Put to update
        $response = $this->actingAs($user)->put(route('orcamentos.update', $orcamento->id), $updateData);

        // 4. Assert
        $response->assertRedirect();
        $orcamento->refresh();
        $this->assertEquals('Obra Atualizada Zero', $orcamento->obra);
        $this->assertEquals(0, $orcamento->guia_recolhimento);
        
        // Ensure no discount record was created
        $this->assertCount(0, $orcamento->descontos->where('tipo', 'fixo'));
    }
}
