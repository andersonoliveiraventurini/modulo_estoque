<?php

use App\Http\Controllers\AnaliseCreditoController;
use App\Http\Controllers\BlocokController;
use App\Http\Controllers\BlocokDescartesController;
use App\Http\Controllers\BlocokInsumosController;
use App\Http\Controllers\BlocokItemController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DescontoController;
use App\Http\Controllers\RdstationController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\ArmazemController;
use App\Http\Controllers\BloqueioController;
use App\Http\Controllers\ClassificarFornecedorController;
use App\Http\Controllers\ConsultaPrecoController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\NcmController;
use App\Http\Controllers\NotaFiscalController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\SubCategoriaController;
use App\Http\Controllers\UserController;
use  App\Http\Controllers\CorController;
use App\Http\Controllers\CategoriaController;
use App\Livewire\OrcamentoShow;
use App\Http\Controllers\SeparacaoController;
use App\Http\Controllers\ConferenciaController;
use App\Http\Controllers\PagamentoController;
use App\Livewire\ListaConferencia;
use App\Livewire\ListaSeparacao;
use App\Livewire\Logistica\SeparacaoListaPage;

Volt::route('/', 'auth.login')
    ->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// orçamento não precisa estar logado para acessar
Route::get('/orcamento/view/{token}', [OrcamentoController::class, 'visualizarPublico'])
    ->name('orcamentos.view');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::resource('produtos', ProdutoController::class)->names('produtos');
    Route::get('/categorias/{id}/subcategorias', [SubCategoriaController::class, 'subcategorias']);

    Route::patch('produtos/{produto}/imagens/{imagem}/principal', [ProdutoController::class, 'definirPrincipal'])
        ->name('produtos.imagens.principal');

    Route::delete('produtos/{produto}/imagens/{imagem}', [ProdutoController::class, 'destroyImagem'])
        ->name('produtos.imagens.destroy');

    Route::resource('cores', CorController::class)->names('cores');
    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::resource('subcategorias', SubCategoriaController::class)->names('subcategorias');

    Route::resource('movimentacao', MovimentacaoController::class)->names('movimentacao');
    Route::resource('consulta_preco', ConsultaPrecoController::class)->names('consulta_preco');
    Route::resource('ncm', NcmController::class)->names('ncm');

    Route::resource('blocok/descartes', BlocokDescartesController::class)->names('blocok.descartes');
    Route::resource('blocok/insumos', BlocokInsumosController::class)->names('blocok.insumos');
    Route::resource('blocok/items', BlocokItemController::class)->names('blocok.items');
    Route::resource('blocok', BlocokController::class)->names('blocok');

    Route::resource('vendas', VendaController::class)->names('vendas');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::get('orcamento/cliente/{cliente_id}', [OrcamentoController::class, 'clienteOrcamento'])->name('orcamentos.cliente');
    Route::get('orcamento/criar/{cliente_id}', [OrcamentoController::class, 'criarOrcamento'])->name('orcamentos.criar');
    Route::post('/orcamentos/{id}/duplicar', [OrcamentoController::class, 'duplicar'])
        ->name('orcamentos.duplicar');

    Route::put('/orcamentos/{id}/status', [OrcamentoController::class, 'atualizarStatus'])->name('orcamentos.atualizar-status');
    Route::put('/orcamentos/{id}/aprovar-desconto', [OrcamentoController::class, 'aprovarDesconto'])->name('orcamentos.aprovar-desconto');
    Route::get('/orcamentos/{id}/gerenciar', OrcamentoShow::class)->name('orcamentos.gerenciar');

    Route::resource('orcamentos', OrcamentoController::class)->names('orcamentos');

    Route::get('balcao', [OrcamentoController::class, 'balcao'])->name('orcamentos.balcao');
    Route::get('balcao_concluidos', [OrcamentoController::class, 'balcao_concluidos'])->name('orcamentos.balcao_concluidos');
    Route::get('status_orcamentos', [OrcamentoController::class, 'kanban_orcamentos'])->name('orcamentos.status_orcamentos');
    // rotas separação e conferência
    Route::get('/logistica/separacao', SeparacaoListaPage::class)->name('logistica.separacao.lista');

    Route::get('/separacao', ListaSeparacao::class)->name('separacao.index');
    Route::get('/conferencia', ListaConferencia::class)->name('conferencia.index');

    Route::get('/orcamentos/{id}/separacao', [SeparacaoController::class, 'show'])->name('orcamentos.separacao.show');
    Route::post('/orcamentos/{id}/separacao/iniciar', [SeparacaoController::class, 'iniciar'])->name('orcamentos.separacao.iniciar');
    Route::patch('/picking/{batch}/item/{item}/separar', [SeparacaoController::class, 'separarItem'])->name('picking.item.separar');
    Route::post('/picking/{batch}/concluir', [SeparacaoController::class, 'concluir'])->name('picking.concluir');

    Route::get('/orcamentos/{id}/conferencia', [ConferenciaController::class, 'show'])->name('orcamentos.conferencia.show');
    Route::post('/orcamentos/{id}/conferencia/iniciar', [ConferenciaController::class, 'iniciar'])->name('orcamentos.conferencia.iniciar');
    Route::patch('/conferencia/{conf}/item/{item}/conferir', [ConferenciaController::class, 'conferirItem'])->name('conferencia.item.conferir');
    Route::post('/conferencia/{conf}/concluir', [ConferenciaController::class, 'concluir'])->name('conferencia.concluir');


    Route::put('/orcamentos/{id}/aprovar-desconto', [OrcamentoController::class, 'processarAprovacaoDesconto'])
        ->name('orcamentos.aprovar-desconto');
    // fim rotas separação e conferência

    Route::resource('pagamentos', PagamentoController::class)->names('pagamentos');
    Route::get('realizar_pagamento/{orcamento_id}', [PagamentoController::class, 'realizar_pagamento'])->name('realizar_pagamento');


    Route::resource('notas', NotaFiscalController::class)->names('notas');

    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::get('cliente/create_completo', [ClienteController::class, 'create_completo'])->name('clientes.create_completo');
    Route::resource('bloqueios', BloqueioController::class)->names('bloqueios');
    Route::get('bloquear/{cliente_id}/cliente', [BloqueioController::class, 'bloquear'])->name('bloquear.cliente');
    Route::get('bloqueios/{cliente_id}/mostrar', [BloqueioController::class, 'bloqueios'])->name('bloqueios.mostrar');
    Route::resource('analise_creditos', AnaliseCreditoController::class)->names('analise_creditos');
    Route::get('analise_creditos/{cliente_id}/mostrar', [AnaliseCreditoController::class, 'mostrar'])->name('analise_creditos.mostrar');
    Route::get('analise_creditos/{cliente_id}/analisar', [AnaliseCreditoController::class, 'analisar'])->name('analise_creditos.analisar');


    Route::resource('fornecedores', FornecedorController::class)->names('fornecedores');
    Route::resource('fornecedores.classificacao', ClassificarFornecedorController::class)->names('fornecedores.classificacao');
    Route::get('fornecedor/{fornecedor_id}/classificar', [ClassificarFornecedorController::class, 'create'])->name('fornecedores.classificar');
    Route::get('fornecedor/{fornecedor_id}/precos', [FornecedorController::class, 'tabelaPrecos'])->name('fornecedores.precos');
    Route::resource('enderecos', EnderecoController::class)->names('enderecos');
    Route::resource('descontos', DescontoController::class)->names('descontos');
    Route::resource('armazens', ArmazemController::class)->names('armazens');

    Route::resource('vendedores', VendedorController::class)->names('vendedores');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::get('/usuarios/{user}/edit-password', [UserController::class, 'editPassword'])->name('usuarios.editPassword');
    Route::put('/usuarios/{user}/update-password', [UserController::class, 'updatePassword'])->name('usuarios.updatePassword');

    Route::put('/usuarios/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('usuarios.toggleBlock');


    Route::get('rdstation/checar-token', [RdstationController::class, 'checarToken'])->name('rdstation.checar-token');
    Route::get('rdstation/listar-empresas', [RdstationController::class, 'listarEmpresas'])->name('rdstation.listar-empresas');
    Route::get('rdstation/listar-negociacoes', [RdstationController::class, 'listarNegociacoes'])->name('rdstation.listar-negociacoes');
    Route::any('rdstation/criar-empresa/{id}', [RdstationController::class, 'criarEmpresa'])->name('rdstation.criar-empresa.id');
});

require __DIR__ . '/auth.php';
