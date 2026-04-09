<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpirarOrcamentosPendenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_pending_budgets_older_than_5_days()
    {
        $user = User::factory()->create();
        Vendedor::factory()->create(['user_id' => $user->id]);
        $cliente = Cliente::factory()->create();

        // Orçamento vencido (validade era ontem)
        $orcamentoVencido = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'Pendente',
            'obra' => 'Obra Vencida',
            'valor_total_itens' => 1000,
            'valor_com_desconto' => 1000,
            'validade' => now()->subDay(),
        ]);

        // Orçamento não vencido (validade em 4 dias)
        $orcamentoAtivo = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'Pendente',
            'obra' => 'Obra Ativa',
            'valor_total_itens' => 1000,
            'valor_com_desconto' => 1000,
            'validade' => now()->addDays(4),
        ]);

        // Orçamento já expirado (não deve ser processado novamente ou deve manter o status)
        $orcamentoJaExpirado = Orcamento::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'Expirado',
            'obra' => 'Obra Já Expirada',
            'valor_total_itens' => 1000,
            'valor_com_desconto' => 1000,
            'validade' => now()->subDays(10),
        ]);

        // Executa o comando
        Artisan::call('orcamentos:expirar-pendentes');

        // Verifica se o vencido expirou
        $this->assertEquals('Expirado', $orcamentoVencido->fresh()->status);

        // Verifica se o ativo continua pendente
        $this->assertEquals('Pendente', $orcamentoAtivo->fresh()->status);

        // Verifica se o já expirado continua expirado
        $this->assertEquals('Expirado', $orcamentoJaExpirado->fresh()->status);
    }
}
