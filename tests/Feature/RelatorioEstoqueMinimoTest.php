<?php

namespace Tests\Feature;

use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioEstoqueMinimoTest extends TestCase
{
    use RefreshDatabase;

    public function test_relatorio_calcula_estoque_minimo_corretamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Criar produto
        $produto = Produto::factory()->create([
            'estoque_atual' => 5,
            'estoque_minimo' => 10,
        ]);

        // Criar vendas para o produto nos últimos 3 meses
        // Mês 1: 10 unidades
        // Mês 2: 20 unidades
        // Mês 3: 30 unidades
        // Total: 60 unidades em 3 meses -> Média 20/mês
        
        $this->criarVenda($produto, 10, now()->subMonths(2));
        $this->criarVenda($produto, 20, now()->subMonths(1));
        $this->criarVenda($produto, 30, now());

        $response = $this->get(route('relatorios.estoque_minimo', [
            'inicio' => now()->subMonths(3)->toDateString(),
            'fim' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        
        // Verificar se o produto está no relatório com os valores corretos
        $response->assertViewHas('produtos', function ($produtos) use ($produto) {
            $p = $produtos->where('id', $produto->id)->first();
            return $p->total_vendido == 60 
                && round($p->qtd_por_mes, 0) == 20
                && $p->estoque_minimo_calculado == 20;
        });
    }

    private function criarVenda($produto, $quantidade, $data)
    {
        $orcamento = Orcamento::factory()->create();
        OrcamentoItens::factory()->create([
            'orcamento_id' => $orcamento->id,
            'produto_id' => $produto->id,
            'quantidade' => $quantidade,
        ]);

        Venda::factory()->create([
            'orcamento_id' => $orcamento->id,
            'data_venda' => $data,
        ]);
    }
}