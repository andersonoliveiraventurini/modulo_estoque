<?php

use App\Models\User;
use App\Models\PickingBatch;
use App\Models\Orcamento;
use App\Models\Cliente;
use App\Models\Vendedor;
use App\Models\Endereco;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Barryvdh\DomPDF\Facade\Pdf;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Configura o ambiente mínimo para gerar o PDF sem dar throws de relacionamento vazio
    $vendedor = Vendedor::factory()->create();
    $cliente = Cliente::factory()->create();
    $endereco = Endereco::factory()->create([
        'cliente_id' => $cliente->id, 
        'roteiro' => 'Rota Sul'
    ]);

    $this->orcamento = Orcamento::factory()->create([
        'cliente_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'workflow_status' => 'em_separacao'
    ]);

    $this->batch = PickingBatch::factory()->create([
        'orcamento_id' => $this->orcamento->id,
        'status' => 'concluido',
        'qtd_caixas' => 2,
        'qtd_sacos' => 1,
        'qtd_sacolas' => 0,
        'outros_embalagem' => null
    ]);
});

it('não permite gerar etiquetas para lote aberto', function () {
    $this->batch->update(['status' => 'aberto']);

    $response = $this->actingAs($this->user)->get(route('picking.etiquetas', $this->batch->id));

    $response->assertSessionHas('error');
    $response->assertRedirect();
});

it('gera pdf stream com sucessso para lote concluído', function () {
    Pdf::shouldReceive('loadView')
        ->once()
        ->andReturnSelf();

    Pdf::shouldReceive('setPaper')
        ->once()
        ->with('a6', 'landscape')
        ->andReturnSelf();

    Pdf::shouldReceive('stream')
        ->once()
        ->with("etiquetas-lote-{$this->batch->id}.pdf")
        ->andReturn(response('PDF_CONTENT', 200, ['Content-Type' => 'application/pdf']));

    $response = $this->actingAs($this->user)->get(route('picking.etiquetas', $this->batch->id));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});
