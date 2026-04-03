<?php

namespace Tests\Feature;

use App\Models\Conferencia;
use App\Models\ConferenciaItem;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\PickingBatch;
use App\Models\PickingItem;
use App\Models\Produto;
use App\Models\User;
use App\Livewire\Orcamentos\ConferenciaOrcamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConferenciaMultiplosLotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_iniciar_conferencia_para_segundo_lote_quando_primeiro_esta_concluido()
    {
        // 1. Setup: Criar usuário, orçamento e produto
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $produto = Produto::factory()->create(['nome' => 'Produto Teste']);
        $orcamento = Orcamento::factory()->create();
        $orcamentoItem = OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => 10,
            'valor_unitario' => 100,
        ]);

        // 2. Criar Lote #1 e sua conferência concluída
        $batch1 = PickingBatch::create([
            'orcamento_id' => $orcamento->id,
            'status' => 'concluido',
            'criado_por_id' => $user->id,
            'finished_at' => now()->subHour(),
        ]);
        
        $pi1 = PickingItem::create([
            'picking_batch_id' => $batch1->id,
            'orcamento_item_id' => $orcamentoItem->id,
            'produto_id' => $produto->id,
            'qty_solicitada' => 5,
            'qty_separada' => 5,
            'status' => 'separado',
        ]);

        $conf1 = Conferencia::create([
            'orcamento_id' => $orcamento->id,
            'picking_batch_id' => $batch1->id,
            'status' => 'concluida',
            'conferente_id' => $user->id,
            'started_at' => now()->subHour(),
            'finished_at' => now()->subMinutes(30),
            'qtd_caixas' => 1,
        ]);

        ConferenciaItem::create([
            'conferencia_id' => $conf1->id,
            'picking_item_id' => $pi1->id,
            'orcamento_item_id' => $orcamentoItem->id,
            'produto_id' => $produto->id,
            'qty_separada' => 5,
            'qty_conferida' => 5,
            'status' => 'ok',
        ]);

        // 3. Criar Lote #2 (Concluído, mas sem conferência ainda)
        $batch2 = PickingBatch::create([
            'orcamento_id' => $orcamento->id,
            'status' => 'concluido',
            'criado_por_id' => $user->id,
            'finished_at' => now(),
        ]);

        $pi2 = PickingItem::create([
            'picking_batch_id' => $batch2->id,
            'orcamento_item_id' => $orcamentoItem->id,
            'produto_id' => $produto->id,
            'qty_solicitada' => 5,
            'qty_separada' => 5,
            'status' => 'separado',
        ]);

        // 4. Executar o componente Livewire e iniciar nova conferência
        Livewire::test(ConferenciaOrcamento::class, ['orcamento' => $orcamento])
            ->call('iniciarConferencia')
            ->assertStatus(200);

        // 5. Asserções: Deve ter criado uma nova conferência vinculada ao Lote #2
        $this->assertDatabaseHas('conferencias', [
            'orcamento_id' => $orcamento->id,
            'picking_batch_id' => $batch2->id,
            'status' => 'em_conferencia',
        ]);

        $novaConf = Conferencia::where('picking_batch_id', $batch2->id)->first();
        $this->assertNotNull($novaConf);
        
        $this->assertDatabaseHas('conferencia_items', [
            'conferencia_id' => $novaConf->id,
            'picking_item_id' => $pi2->id,
            'qty_separada' => 5,
        ]);
    }

    public function test_iniciar_conferencia_para_lote_parcialmente_conferido()
    {
        // Setup similar ao anterior, mas com um lote que foi conferido apenas parcialmente
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $produto = Produto::factory()->create();
        $orcamento = Orcamento::factory()->create();
        $orcamentoItem = OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => 10,
            'valor_unitario' => 100,
        ]);

        $batch1 = PickingBatch::create([
            'orcamento_id' => $orcamento->id,
            'status' => 'concluido',
            'criado_por_id' => $user->id,
        ]);
        
        $pi1 = PickingItem::create([
            'picking_batch_id' => $batch1->id,
            'orcamento_item_id' => $orcamentoItem->id,
            'produto_id' => $produto->id,
            'qty_solicitada' => 10,
            'qty_separada' => 10,
            'status' => 'separado',
        ]);

        // Conferência parcial (conferiu apenas 4 de 10)
        $conf1 = Conferencia::create([
            'orcamento_id' => $orcamento->id,
            'picking_batch_id' => $batch1->id,
            'status' => 'concluida',
            'conferente_id' => $user->id,
            'finished_at' => now(),
            'qtd_caixas' => 1,
        ]);

        ConferenciaItem::create([
            'conferencia_id' => $conf1->id,
            'picking_item_id' => $pi1->id,
            'orcamento_item_id' => $orcamentoItem->id,
            'produto_id' => $produto->id,
            'qty_separada' => 10,
            'qty_conferida' => 4,
            'status' => 'divergente',
        ]);

        // Iniciar nova conferência para o mesmo lote (deve pegar os 6 restantes)
        Livewire::test(ConferenciaOrcamento::class, ['orcamento' => $orcamento])
            ->call('iniciarConferencia')
            ->assertStatus(200);

        $novaConf = Conferencia::where('status', 'em_conferencia')->first();
        $this->assertNotNull($novaConf);
        $this->assertEquals($batch1->id, $novaConf->picking_batch_id);

        $this->assertDatabaseHas('conferencia_items', [
            'conferencia_id' => $novaConf->id,
            'picking_item_id' => $pi1->id,
            'qty_separada' => 6, // 10 - 4 = 6
        ]);
    }
}
