<?php

namespace Tests\Feature;

use App\Events\OrcamentoAprovado;
use App\Events\OrcamentoCancelado;
use App\Events\OrcamentoFinalizado;
use App\Models\Cliente;
use App\Models\CondicoesPagamento;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\Produto;
use App\Models\TipoTransporte;
use App\Models\User;
use App\Models\Vendedor;
use App\Observers\OrcamentoObserver;
use App\Services\EstoqueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class OrcamentoReservaEstoqueTest extends TestCase
{
    use RefreshDatabase;

    protected $vendedor;
    protected $cliente;
    protected $condicao;
    protected $transporte;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass authorization for tests to focus on business logic
        Gate::before(fn() => true);
        Gate::define('approve', fn() => true);

        // Criar usuário e vendedor vinculados
        $this->vendedor = User::create([
            'name' => 'Vendedor Teste',
            'email' => 'vendedor@teste.com',
            'password' => bcrypt('password'),
        ]);
        
        Vendedor::create([
            'user_id' => $this->vendedor->id,
            'desconto' => 10,
        ]);

        $this->cliente = Cliente::create([
            'nome_fantasia' => 'Cliente Teste',
            'cpf_cnpj' => '12345678901',
        ]);

        $this->condicao = CondicoesPagamento::create([
            'nome' => 'Teste 30 dias',
            'tipo' => 'boleto',
        ]);

        $this->transporte = TipoTransporte::create([
            'nome' => 'Correios',
        ]);
    }

    /** @test */
    public function ao_aprovar_orcamento_com_estoque_disponivel_dispara_evento_aprovado()
    {
        Event::fake([
            OrcamentoAprovado::class,
            OrcamentoCancelado::class,
            OrcamentoFinalizado::class
        ]);

        $produto = Produto::create([
            'codigo' => 'P001',
            'nome' => 'Produto Teste',
            'preco_venda' => 100,
            'estoque_atual' => 10,
        ]);

        $orcamento = Orcamento::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'status' => 'Pendente',
            'valor_total_itens' => 500,
            'valor_com_desconto' => 500,
        ]);

        OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => 5,
            'valor_unitario' => 100,
            'valor_total' => 500,
        ]);

        // Simula aprovação mudando status
        $orcamento->status = 'Aprovado';
        $orcamento->save();

        Event::assertDispatched(OrcamentoAprovado::class);
    }

    /** @test */
    public function store_nao_deve_aprovar_se_estoque_for_insuficiente()
    {
        Event::fake([OrcamentoAprovado::class]);

        $produto = Produto::create([
            'codigo' => 'P002',
            'nome' => 'Produto Sem Estoque',
            'preco_venda' => 100,
            'estoque_atual' => 2,
        ]);

        $this->actingAs($this->vendedor);

        $response = $this->postJson(route('orcamentos.store'), [
            'cliente_id' => $this->cliente->id,
            'nome_obra' => 'Teste Obra',
            'itens' => [
                [
                    'id' => $produto->id,
                    'quantidade' => 5,
                    'preco_unitario' => 100,
                ]
            ],
            'vendedor_id' => $this->vendedor->id,
            'valor_total' => 500,
            'frete' => 'CIF',
            'tipo_documento' => 'Nota fiscal',
            'tipos_transporte' => $this->transporte->id,
            'condicao_id' => $this->condicao->id,
            'venda_triangular' => 0,
        ]);

        if ($response->status() !== 201) {
            fwrite(STDERR, "Status: " . $response->status() . "\n");
            fwrite(STDERR, print_r($response->json(), true));
        }

        $response->assertStatus(201);

        $orcamento = Orcamento::latest()->first();
        $this->assertEquals('Sem estoque', $orcamento->status);
        Event::assertNotDispatched(OrcamentoAprovado::class);
    }

    /** @test */
    public function ao_cancelar_orcamento_dispara_evento_cancelado()
    {
        Event::fake([OrcamentoCancelado::class]);

        $orcamento = Orcamento::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'status' => 'Aprovado',
            'valor_total_itens' => 100,
            'valor_com_desconto' => 100,
        ]);

        $orcamento->status = 'Cancelado';
        $orcamento->save();

        Event::assertDispatched(OrcamentoCancelado::class);
    }

    /** @test */
    public function ao_finalizar_orcamento_dispara_evento_finalizado()
    {
        Event::fake([OrcamentoFinalizado::class]);

        $orcamento = Orcamento::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'status' => 'Aprovado',
            'valor_total_itens' => 100,
            'valor_com_desconto' => 100,
        ]);

        $orcamento->status = 'Finalizado';
        $orcamento->save();

        Event::assertDispatched(OrcamentoFinalizado::class);
    }

    /** @test */
    public function garantir_idempotencia_na_reserva_de_estoque()
    {
        $produto = Produto::create([
            'codigo' => 'P003',
            'nome' => 'Produto Idempotencia',
            'preco_venda' => 100,
            'estoque_atual' => 10,
        ]);

        $orcamento = Orcamento::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'status' => 'Pendente',
            'valor_total_itens' => 200,
            'valor_com_desconto' => 200,
        ]);

        OrcamentoItens::create([
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => 2,
            'valor_unitario' => 100,
            'valor_total' => 200,
        ]);

        // Primeira reserva (ao aprovar)
        $orcamento->update(['status' => 'Aprovado']);
        
        $orcamento->refresh();
        $this->assertNotNull($orcamento->estoque_reservado_em);
        
        $dataReservaOriginal = $orcamento->estoque_reservado_em;

        // Tenta chamar o service manualmente
        app(EstoqueService::class)->reservarParaOrcamento($orcamento);

        $orcamento->refresh();
        $this->assertEquals($dataReservaOriginal->toDateTimeString(), $orcamento->estoque_reservado_em->toDateTimeString());
        
        $reservasCount = \App\Models\EstoqueReserva::where('orcamento_id', $orcamento->id)
            ->where('produto_id', $produto->id)
            ->where('status', 'ativa')
            ->count();
        
        $this->assertEquals(1, $reservasCount);
    }
}
