<?php

use App\Livewire\Faturas\RelatorioInadimplencia;
use App\Models\Fatura;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('inadimplencia dashboard renders correctly and aggregates values', function () {
    $user = User::factory()->create();
    $cliente = Cliente::factory()->create(['nome' => 'Cliente Devedor Teste']);

    // Devendo 100
    Fatura::factory()->create([
        'cliente_id' => $cliente->id, 
        'valor_total' => 100, 
        'valor_pago' => 0, 
        'status' => 'vencido', 
        'data_vencimento' => now()->subDays(5)
    ]);
    
    // Devendo 50
    Fatura::factory()->create([
        'cliente_id' => $cliente->id, 
        'valor_total' => 100, 
        'valor_pago' => 50, 
        'status' => 'vencido', 
        'data_vencimento' => now()->subDays(2)
    ]);
    
    // Pago 300 - Nao deve entrar na soma
    Fatura::factory()->create([
        'cliente_id' => $cliente->id, 
        'valor_total' => 300, 
        'valor_pago' => 300, 
        'status' => 'pago', 
        'data_vencimento' => now()->subDays(10)
    ]);

    Livewire::actingAs($user)
        ->test(RelatorioInadimplencia::class)
        ->assertStatus(200)
        ->assertSee('Cliente Devedor Teste')
        ->assertSee('150,00'); // 100 + 50 devidos pelas 2 faturas vencidas
});
