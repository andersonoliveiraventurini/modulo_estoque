<?php

namespace Tests\Feature;

use App\Models\ConsultaPreco;
use App\Models\ConsultaPrecoGrupo;
use App\Models\EntradaEncomenda;
use App\Models\EntradaEncomendaItem;
use App\Models\Orcamento;
use App\Models\User;
use App\Models\PickingBatch;
use App\Models\PickingItem;
use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\KanbanOrcamentos;
use App\Livewire\Orcamentos\SeparacaoPage;
use App\Livewire\Orcamentos\ConferenciaOrcamento;

class EncomendaWorkflowRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $orcamento;
    protected $grupo;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->orcamento = Orcamento::create([
            'cliente_id' => \App\Models\Cliente::factory()->create()->id,
            'vendedor_id' => $this->user->id,
            'status' => 'Aprovado',
            'workflow_status' => 'aguardando_separacao',
            'encomenda' => 1
        ]);

        $this->grupo = ConsultaPrecoGrupo::create([
            'cliente_id' => $this->orcamento->cliente_id,
            'orcamento_id' => $this->orcamento->id,
            'status' => 'Aprovado',
        ]);

        $this->item = ConsultaPreco::create([
            'grupo_id' => $this->grupo->id,
            'orcamento_id' => $this->orcamento->id,
            'descricao' => 'Produto de Encomenda',
            'quantidade' => 10,
        ]);
    }

    /** @test */
    public function it_blocks_kanban_move_if_encomenda_not_received()
    {
        Livewire::test(KanbanOrcamentos::class)
            ->call('updateWorkflowStatus', $this->orcamento->id, 'finalizado')
            ->assertDispatched('showNotification', function ($data) {
                return $data['type'] === 'error' && str_contains($data['message'], 'itens de encomenda que ainda não foram totalmente recebidos');
            });

        $this->orcamento->refresh();
        $this->assertNotEquals('finalizado', $this->orcamento->workflow_status);
    }

    /** @test */
    public function it_blocks_separation_start_if_encomenda_not_received()
    {
        Livewire::test(SeparacaoPage::class, ['id' => $this->orcamento->id])
            ->call('iniciarSeparacao')
            ->assertHasFlash('error', 'Este orçamento possui itens de encomenda que ainda não foram totalmente recebidos no estoque. O recebimento físico é obrigatório antes da separação.');

        $this->assertDatabaseMissing('picking_batches', [
            'orcamento_id' => $this->orcamento->id
        ]);
    }

    /** @test */
    public function it_limits_separation_quantity_to_received_quantity()
    {
        // Recebe parcialmente (5 de 10)
        $entrada = EntradaEncomenda::create([
            'grupo_id' => $this->grupo->id,
            'recebido_por' => $this->user->id,
            'data_recebimento' => now(),
            'status' => 'Recebido parcialmente'
        ]);

        EntradaEncomendaItem::create([
            'entrada_id' => $entrada->id,
            'consulta_preco_id' => $this->item->id,
            'quantidade_solicitada' => 10,
            'quantidade_recebida' => 5,
            'recebido_completo' => false
        ]);

        // Simula que o orçamento já foi totalmente recebido para permitir iniciar separação no teste
        // Na vida real o usuário teria que receber os outros 5
        
        // Mocking receipt for the check in iniciarSeparacao
        $this->item->refresh(); 
        
        // Vamos forçar o início da separação ignorando a trava global para testar a trava por item
        $batch = PickingBatch::create([
            'orcamento_id' => $this->orcamento->id,
            'status' => 'em_separacao',
            'started_at' => now(),
            'criado_por_id' => $this->user->id
        ]);

        $pi = PickingItem::create([
            'picking_batch_id' => $batch->id,
            'consulta_preco_id' => $this->item->id,
            'is_encomenda' => true,
            'qty_solicitada' => 10,
            'qty_separada' => 0,
            'status' => 'pendente'
        ]);

        Livewire::test(SeparacaoPage::class, ['id' => $this->orcamento->id])
            ->set("inputs.{$pi->id}.qty", 8) // Tenta separar 8 mas só recebeu 5
            ->call('salvarItem', $pi->id)
            ->assertHasFlash('warning', 'Quantidade limitada ao saldo recebido fisicamente (5).');

        $pi->refresh();
        $this->assertEquals(5, $pi->qty_separada);
    }

    /** @test */
    public function it_blocks_conference_start_if_encomenda_not_received()
    {
        Livewire::test(ConferenciaOrcamento::class, ['orcamento' => $this->orcamento])
            ->call('iniciarConferencia')
            ->assertHasFlash('error', 'Este orçamento possui itens de encomenda que ainda não foram totalmente recebidos no estoque. O recebimento físico é obrigatório antes da conferência.');
    }
}
