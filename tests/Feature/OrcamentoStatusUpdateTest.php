<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Orcamento;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrcamentoStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $orcamento;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Vendedor::factory()->create(['user_id' => $this->user->id]);
        
        $cliente = Cliente::factory()->create();
        
        $this->orcamento = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $this->user->id,
            'status' => 'Sem estoque',
            'obra' => 'Obra Teste',
            'valor_total_itens' => 1000,
            'valor_com_desconto' => 1000,
            'validade' => now()->addDays(2),
        ]);
    }

    public function test_vendedor_can_change_status_from_sem_estoque_to_cancelado()
    {
        $this->actingAs($this->user);

        $response = $this->putJson(route('orcamentos.atualizar-status', $this->orcamento->id), [
            'status' => 'Cancelado'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Cancelado', $this->orcamento->fresh()->status);
    }

    public function test_vendedor_can_change_status_from_sem_estoque_to_reprovado()
    {
        $this->actingAs($this->user);

        $response = $this->putJson(route('orcamentos.atualizar-status', $this->orcamento->id), [
            'status' => 'Reprovado'
        ]);

        $response->assertStatus(200);
        // O controller deve mapear 'Reprovado' (view) para 'Rejeitado' (banco)
        $this->assertEquals('Rejeitado', $this->orcamento->fresh()->status);
    }

    public function test_vendedor_can_change_status_from_sem_estoque_to_expirado()
    {
        $this->actingAs($this->user);

        $response = $this->putJson(route('orcamentos.atualizar-status', $this->orcamento->id), [
            'status' => 'Expirado'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Expirado', $this->orcamento->fresh()->status);
    }

    public function test_vendedor_cannot_change_to_invalid_status()
    {
        $this->actingAs($this->user);

        $response = $this->putJson(route('orcamentos.atualizar-status', $this->orcamento->id), [
            'status' => 'StatusInexistente'
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Sem estoque', $this->orcamento->fresh()->status);
    }
}
