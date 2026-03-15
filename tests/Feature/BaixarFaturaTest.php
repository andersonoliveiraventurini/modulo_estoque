<?php

use App\Livewire\Faturas\BaixarFaturaModal;
use App\Models\Fatura;
use App\Models\MetodoPagamento;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('component renders successfully', function () {
    Livewire::test(BaixarFaturaModal::class)
        ->assertStatus(200);
});

test('can validate inputs before saving baixa', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create(['cliente_id' => $cliente->id, 'valor_total' => 100, 'valor_pago' => 0]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BaixarFaturaModal::class)
        ->call('openModal', $fatura->id)
        ->set('form.valor_pago', '')
        ->set('form.metodo_pagamento_id', '')
        ->call('salvar')
        ->assertHasErrors(['form.valor_pago', 'form.metodo_pagamento_id']);
});

test('can successfully process a full payment for a fatura', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create(['cliente_id' => $cliente->id, 'valor_total' => 100, 'valor_pago' => 50, 'status' => 'parcial']);
    $user = User::factory()->create();
    
    // To pretend there's an active method even without factory, we use ID 1, but safely creating one is better.
    // Assuming you have MetodoPagamento (id 1 usually 'Dinheiro')
    DB::table('metodos_pagamento')->insertOrIgnore(['id' => 1, 'nome' => 'Pix', 'ativo' => true]);

    Livewire::actingAs($user)
        ->test(BaixarFaturaModal::class)
        ->call('openModal', $fatura->id)
        ->set('form.valor_pago', 50.00) // Paying the remaining
        ->set('form.data_pagamento', now()->format('Y-m-d'))
        ->set('form.metodo_pagamento_id', 1)
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertDispatched('fatura-baixada');

    $this->assertDatabaseHas('faturas', [
        'id' => $fatura->id,
        'valor_pago' => 100.00,
        'status' => 'pago'
    ]);

    $this->assertDatabaseHas('pagamentos', [
        'tipo_documento' => 'recibo',
        'valor_pago' => 50.00
    ]);
});
