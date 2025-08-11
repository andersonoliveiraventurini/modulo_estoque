<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\TelefoneController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DescontoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('fornecedores', FornecedorController::class)->names('fornecedores');
    Route::resource('produtos', ProdutoController::class)->names('produtos');
    Route::resource('telefones', TelefoneController::class)->names('telefones');
    Route::resource('enderecos', EnderecoController::class)->names('enderecos');
    Route::resource('vendas', VendaController::class)->names('vendas');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::resource('descontos', DescontoController::class)->names('descontos');
});

require __DIR__.'/auth.php';
