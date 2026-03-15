<?php

use App\Services\FaturaService;
use App\Models\Fatura;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\CondicoesPagamento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gerar faturas distribui corretamente os valores e cria no banco', function () {
    $cliente = Cliente::factory()->create();
    $condicao = CondicoesPagamento::factory()->create();
    $orcamento = Orcamento::factory()->create([
        'cliente_id' => $cliente->id,
        'valor_total' => 100.00
    ]);

    $dadosPagamento = [
        'condicao_pagamento_id' => $condicao->id,
        'metodos_pagamento' => [
            [
                'metodo_id' => 1,
                'valor' => 100.00,
                'parcelas' => 2
            ]
        ]
    ];

    $service = new FaturaService();
    $service->gerarFaturasVenda($orcamento, $dadosPagamento);

    $this->assertDatabaseCount('faturas', 2);
    $this->assertDatabaseHas('faturas', [
        'orcamento_id' => $orcamento->id,
        'numero_parcela' => 1,
        'valor_total' => 50.00,
        'status' => 'pago' // Assuming vencimento is today
    ]);
});

test('verificar inadimplencia marca faturas atrasadas como vencidas', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create([
        'cliente_id' => $cliente->id,
        'status' => 'pendente',
        'data_vencimento' => Carbon::yesterday(),
    ]);

    $service = new FaturaService();
    $afetadas = $service->verificarInadimplencia();

    expect($afetadas)->toBe(1);
    expect($fatura->fresh()->status)->toBe('vencido');
});
