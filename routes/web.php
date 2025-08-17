<?php

use App\Http\Controllers\BlocokController;
use App\Http\Controllers\BlocokDescartesController;
use App\Http\Controllers\BlocokInsumosController;
use App\Http\Controllers\BlocokItemController;
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

    // Resource routes for various controllers
    Route::resource('blocok/descartes', BlocokDescartesController::class)->names('blocok.descartes');
    Route::resource('blocok/insumos', BlocokInsumosController::class)->names('blocok.insumos');
    Route::resource('blocok/items', BlocokItemController::class)->names('blocok.items');
    Route::resource('blocok', BlocokController::class)->names('blocok');


    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('fornecedores', FornecedorController::class)->names('fornecedores');
    Route::resource('produtos', ProdutoController::class)->names('produtos');
    Route::resource('telefones', TelefoneController::class)->names('telefones');
    Route::resource('enderecos', EnderecoController::class)->names('enderecos');
    Route::resource('vendas', VendaController::class)->names('vendas');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::resource('descontos', DescontoController::class)->names('descontos');
    Route::resource('emails', \App\Http\Controllers\EmailController::class)->names('emails');
    Route::resource('armazens', \App\Http\Controllers\ArmazemController::class)->names('armazens');

});

require __DIR__.'/auth.php';
