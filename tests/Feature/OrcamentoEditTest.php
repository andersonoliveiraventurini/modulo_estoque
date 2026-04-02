<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CondicoesPagamento;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\Produto;
use App\Models\TipoTransporte;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrcamentoEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_view_loads_with_correct_data()
    {
        // 1. Setup data
        $user = User::factory()->create();
        $vendedor = Vendedor::factory()->create(['user_id' => $user->id]);
        
        $cliente = Cliente::factory()->create();
        $produto = Produto::factory()->create(['preco_venda' => 100.00]);
        $condicao = CondicoesPagamento::factory()->create(['nome' => 'A Vista']);
        $transporte = TipoTransporte::factory()->create(['nome' => 'Retira']);

        $orcamento = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'obra' => 'Obra Teste',
            'valor_total_itens' => 100.00,
            'status' => 'Pendente',
            'condicao_id' => $condicao->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
        ]);

        $orcamento->itens()->create([
            'produto_id' => $produto->id,
            'quantidade' => 1,
            'valor_unitario' => 100.00,
            'valor_unitario_com_desconto' => 100.00,
            'desconto' => 0,
            'valor_com_desconto' => 100.00,
        ]);

        $orcamento->transportes()->attach($transporte->id);

        // 2. Act
        $response = $this->actingAs($user)->get(route('orcamentos.edit', $orcamento->id));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('Obra Teste');
        $response->assertSee($produto->nome);
        $response->assertSee('A Vista');
        $response->assertSee('Retira');
        
        // Check if seller select is present and correct
        $response->assertSee($user->name);
    }

    public function test_orcamento_update_preserves_data()
    {
        // 1. Setup data
        $user = User::factory()->create();
        $vendedor = Vendedor::factory()->create(['user_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        $produto = Produto::factory()->create(['preco_venda' => 100.00]);
        $condicao = CondicoesPagamento::factory()->create();
        $transporte = TipoTransporte::factory()->create();

        $orcamento = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'obra' => 'Obra Original',
            'valor_total_itens' => 100.00,
            'status' => 'Pendente',
            'condicao_id' => $condicao->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
        ]);

        $item = $orcamento->itens()->create([
            'produto_id' => $produto->id,
            'quantidade' => 1,
            'valor_unitario' => 100.00,
            'valor_unitario_com_desconto' => 100.00,
            'desconto' => 0,
            'valor_com_desconto' => 100.00,
        ]);

        $orcamento->transportes()->attach($transporte->id);

        // 2. Update data
        $updateData = [
            'obra' => 'Obra Atualizada',
            'condicao_id' => $condicao->id,
            'tipo_documento' => 'Nota fiscal',
            'venda_triangular' => 0,
            'homologacao' => 0,
            'tipos_transporte' => $transporte->id,
            'valor_total' => '100,00',
            'produtos' => [
                [
                    'produto_id' => $produto->id,
                    'quantidade' => 2,
                    'valor_unitario' => '100,00',
                    'preco_original' => '100,00',
                    'liberar_desconto' => 1,
                    'tipo_desconto' => 'percentual',
                ]
            ]
        ];

        $response = $this->actingAs($user)->put(route('orcamentos.update', $orcamento->id), $updateData);

        // 3. Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('orcamentos', [
            'id' => $orcamento->id,
            'obra' => 'Obra Atualizada',
            'valor_total_itens' => 200.00,
        ]);

        $this->assertDatabaseHas('orcamento_itens', [
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'valor_unitario' => 100.00,
            'valor_com_desconto' => 200.00,
        ]);
    }
}
