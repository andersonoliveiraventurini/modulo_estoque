<?php

namespace Tests\Feature;

use App\Models\ConsultaPreco;
use App\Models\ConsultaPrecoGrupo;
use App\Models\EntradaEncomenda;
use App\Models\EntradaEncomendaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntradaEncomendaDescricaoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $grupo;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->grupo = ConsultaPrecoGrupo::create([
            'cliente_id' => \App\Models\Cliente::factory()->create()->id,
            'status' => 'Aprovado',
        ]);

        $this->item = ConsultaPreco::create([
            'grupo_id' => $this->grupo->id,
            'descricao' => 'Produto de Teste',
            'quantidade' => 10,
        ]);
    }

    /** @test */
    public function it_can_save_product_description_during_creation()
    {
        $payload = [
            'grupo_id' => $this->grupo->id,
            'data_recebimento' => now()->format('Y-m-d'),
            'recebido_por' => $this->user->id,
            'itens' => [
                [
                    'consulta_preco_id' => $this->item->id,
                    'quantidade_solicitada' => 10,
                    'quantidade_recebida' => 10,
                    'descricao' => 'Descrição opcional do produto',
                ]
            ]
        ];

        $response = $this->post(route('entrada_encomendas.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('entrada_encomenda_itens', [
            'consulta_preco_id' => $this->item->id,
            'descricao' => 'Descrição opcional do produto',
        ]);
    }

    /** @test */
    public function it_can_edit_product_description()
    {
        $entrada = EntradaEncomenda::create([
            'grupo_id' => $this->grupo->id,
            'recebido_por' => $this->user->id,
            'data_recebimento' => now(),
            'status' => 'Recebido completo',
        ]);

        $itemEntrada = EntradaEncomendaItem::create([
            'entrada_id' => $entrada->id,
            'consulta_preco_id' => $this->item->id,
            'quantidade_solicitada' => 10,
            'quantidade_recebida' => 10,
            'recebido_completo' => true,
            'descricao' => 'Descrição inicial',
        ]);

        $payload = [
            'data_recebimento' => now()->format('Y-m-d'),
            'itens' => [
                [
                    'id' => $itemEntrada->id,
                    'quantidade_recebida' => 10,
                    'descricao' => 'Descrição editada',
                ]
            ]
        ];

        $response = $this->put(route('entrada_encomendas.update', $entrada->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('entrada_encomenda_itens', [
            'id' => $itemEntrada->id,
            'descricao' => 'Descrição editada',
        ]);
    }

    /** @test */
    public function description_has_maximum_length_of_500_characters()
    {
        $longDescription = str_repeat('a', 501);

        $payload = [
            'grupo_id' => $this->grupo->id,
            'data_recebimento' => now()->format('Y-m-d'),
            'itens' => [
                [
                    'consulta_preco_id' => $this->item->id,
                    'quantidade_solicitada' => 10,
                    'quantidade_recebida' => 10,
                    'descricao' => $longDescription,
                ]
            ]
        ];

        $response = $this->post(route('entrada_encomendas.store'), $payload);

        $response->assertSessionHasErrors(['itens.0.descricao']);
    }
}
