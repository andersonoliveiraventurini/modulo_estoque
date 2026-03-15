<?php

use App\Models\Fatura;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('fatura is atrasada when pendente and past due', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create([
        'cliente_id' => $cliente->id,
        'status' => 'pendente',
        'data_vencimento' => Carbon::yesterday(),
    ]);

    expect($fatura->isAtrasada())->toBeTrue();
});

test('fatura is not atrasada when pago', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create([
        'cliente_id' => $cliente->id,
        'status' => 'pago',
        'data_vencimento' => Carbon::yesterday(),
    ]);

    expect($fatura->isAtrasada())->toBeFalse();
});

test('fatura is not atrasada when pendente but not past due', function () {
    $cliente = Cliente::factory()->create();
    $fatura = Fatura::factory()->create([
        'cliente_id' => $cliente->id,
        'status' => 'pendente',
        'data_vencimento' => Carbon::tomorrow(),
    ]);

    expect($fatura->isAtrasada())->toBeFalse();
});
