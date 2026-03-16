<?php

use App\Http\Controllers\AnaliseCreditoController;
use App\Http\Controllers\BlocokController;
use App\Http\Controllers\BlocokDescartesController;
use App\Http\Controllers\BlocokInsumosController;
use App\Http\Controllers\BlocokItemController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\CorredorController;
use App\Http\Controllers\PosicaoController;
use App\Http\Controllers\PedidoCompraController;
use  App\Http\Controllers\CorController;
use App\Http\Controllers\CategoriaController;
use App\Livewire\OrcamentoShow;
use App\Http\Controllers\SeparacaoController;
use App\Http\Controllers\ConferenciaController;
use App\Http\Controllers\EntradaEncomendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\SolicitacaoPagamentoController;
use App\Livewire\ListaConferencia;
use App\Livewire\ListaSeparacao;
use App\Livewire\Logistica\SeparacaoListaPage;
use App\Http\Controllers\InconsistenciaRecebimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RequisicaoCompraController;
use App\Http\Controllers\RomaneioController;

Volt::route('/', 'auth.login')
    ->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
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

    Route::get('ativar_produto/{produto_id}', [ProdutoController::class, 'ativar'])->name('produto.ativar');
    Route::get('inativar_produto/{produto_id}', [ProdutoController::class, 'inativar'])->name('produto.inativar');

    Route::resource('cores', CorController::class)->names('cores');
    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::resource('subcategorias', SubCategoriaController::class)->names('subcategorias');

    // Estoque - Movimentações
    Route::resource('movimentacao', MovimentacaoController::class);
    Route::post('movimentacao/{movimentacao}/aprovar', [MovimentacaoController::class, 'aprovar'])->name('movimentacao.aprovar');
    Route::post('movimentacao/{movimentacao}/rejeitar', [MovimentacaoController::class, 'rejeitar'])->name('movimentacao.rejeitar');

    // cotação de produto
    // Grupo de cotação
    Route::get('/cotacoes', [ConsultaPrecoController::class, 'index'])->name('consulta_preco.index');
    Route::get('/cotacoes/criar/{cliente_id}', [ConsultaPrecoController::class, 'criar_cotacao'])->name('consulta_preco.criar');
    Route::post('/cotacoes', [ConsultaPrecoController::class, 'store'])->name('consulta_preco.store');
    Route::get('/cotacoes/grupo/{grupoId}', [ConsultaPrecoController::class, 'showGrupo'])->name('consulta_preco.show_grupo');
    Route::post('/cotacoes/grupo/{grupoId}/aprovar', [ConsultaPrecoController::class, 'aprovarGrupo'])->name('consulta_preco.aprovar_grupo');
    Route::post('/cotacoes/grupo/{grupoId}/gerar-orcamento', [ConsultaPrecoController::class, 'gerarOrcamento'])->name('consulta_preco.gerar_orcamento');
    Route::post('/cotacoes/grupo/{grupoId}/adicionar-item', [ConsultaPrecoController::class, 'adicionarItem'])->name('consulta_preco.adicionar_item'); // ✅ movida para cá
    Route::delete('/cotacoes/grupo/{grupoId}', [ConsultaPrecoController::class, 'destroyGrupo'])->name('consulta_preco.destroy_grupo');

    // Item individual
    Route::get('/cotacoes/item/{consulta}', [ConsultaPrecoController::class, 'show'])->name('consulta_preco.show');
    Route::get('/cotacoes/item/{consult_id}/editar', [ConsultaPrecoController::class, 'edit'])->name('consulta_preco.edit');
    Route::put('/cotacoes/item/{consulta_id}', [ConsultaPrecoController::class, 'update'])->name('consulta_preco.update');
    Route::delete('/cotacoes/item/{consulta_id}', [ConsultaPrecoController::class, 'destroy'])->name('consulta_preco.destroy');

    // Adicionar fornecedor a item
    Route::post('/cotacoes/item/{consultaId}/fornecedor', [ConsultaPrecoController::class, 'adicionarFornecedor'])->name('consulta_preco.add_fornecedor');

    // PDF via token
    Route::get('/cotacoes/visualizar/{token}', [ConsultaPrecoController::class, 'visualizarCotacao'])->name('cotacoes.view');
    // Encomendas aprovadas — painel da área de compras
    Route::get('/encomendas/aprovadas', [EntradaEncomendaController::class, 'encomendasAprovadas'])
        ->name('entrada_encomendas.aprovadas');

    // CRUD de entrada de encomendas
    Route::resource('entrada-encomendas', EntradaEncomendaController::class)
        ->names('entrada_encomendas')
        ->parameters(['entrada-encomendas' => 'entradaEncomenda']);

    Route::get(
        'entrada-encomendas/{entradaEncomenda}/complementar',
        [EntradaEncomendaController::class, 'complementar']
    )
        ->name('entrada_encomendas.complementar');

    Route::get('/encomendas/kanban', [EntradaEncomendaController::class, 'kanban'])
        ->name('entrada_encomendas.kanban');

    Route::get('/produtos/create-from-item/{consultaPreco}', [ProdutoController::class, 'createFromItem'])
        ->name('produtos.create_from_item');

    //Route::resource('consulta_preco', ConsultaPrecoController::class)->names('consulta_preco');
    //Route::get('criar_cotacao/{cliente_id}', [ConsultaPrecoController::class, 'criar_cotacao'])->name('consulta_preco.criar_cotacao');
    //Route::get('/cotacoes/view/{token}', [CotacaoController::class, 'visualizarCotacao'])
    //    ->name('cotacoes.view');
    Route::resource('ncm', NcmController::class)->names('ncm');

    Route::resource('blocok/descartes', BlocokDescartesController::class)->names('blocok.descartes');
    Route::resource('blocok/insumos', BlocokInsumosController::class)->names('blocok.insumos');
    Route::resource('blocok/items', BlocokItemController::class)->names('blocok.items');
    Route::resource('blocok', BlocokController::class)->names('blocok');

    Route::resource('vendas', VendaController::class)->names('vendas');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::get('orcamento/cliente/{cliente_id}', [OrcamentoController::class, 'clienteOrcamento'])->name('orcamentos.cliente');
    Route::get('orcamento/criar/{cliente_id}', [OrcamentoController::class, 'criarOrcamento'])->name('orcamentos.criar');
    Route::get('orcamento/copiar', [OrcamentoController::class, 'copiarOrcamento'])->name('orcamentos.copiar');
    Route::post('/orcamentos/duplicar/{id}/{clienteID?}', [OrcamentoController::class, 'duplicar'])
        ->name('orcamentos.duplicar');

    Route::put('/orcamentos/{id}/status', [OrcamentoController::class, 'atualizarStatus'])->name('orcamentos.atualizar-status');
    Route::put('/orcamentos/{id}/aprovar-desconto', [OrcamentoController::class, 'aprovarDesconto'])->name('orcamentos.aprovar-desconto');
    Route::get('/orcamentos/{id}/gerenciar', OrcamentoShow::class)->name('orcamentos.gerenciar');

    // ========== ROTAS DESCONTOS ==========
    Route::get('/descontos/clientes', [DescontoController::class, 'descontosClientes'])->name('descontos.clientes');
    Route::get('/descontos/aprovados', [DescontoController::class, 'descontosAprovados'])->name('descontos.aprovados');
    Route::get('/descontos/orcamento/{orcamento_id}', [DescontoController::class, 'desconto_orcamento'])->name('descontos.orcamento');
    Route::put('/orcamentos/{id}/aprovar-desconto', [OrcamentoController::class, 'processarAprovacaoDesconto'])
        ->name('orcamentos.aprovar-desconto');

    Route::post('/descontos/{id}/avaliar', [DescontoController::class, 'avaliar'])
        ->name('descontos.avaliar');
    /*Route::post(
        '/descontos/{desconto}/avaliar',
        [DescontoController::class, 'avaliar']
    )->name('descontos.avaliar');*/

    Route::post('/descontos/{id}/aprovar', [DescontoController::class, 'aprovar'])
        ->name('descontos.aprovar');


    Route::post('/descontos/{id}/rejeitar', [DescontoController::class, 'rejeitar'])
        ->name('descontos.rejeitar');


    // ========== ROTAS EM LOTE ==========


    Route::post('/orcamentos/{orcamentoId}/descontos/aprovar-todos', [DescontoController::class, 'aprovarTodos'])
        ->name('descontos.aprovarTodos');

    Route::post('/orcamentos/{orcamentoId}/descontos/rejeitar-todos', [DescontoController::class, 'rejeitarTodos'])
        ->name('descontos.rejeitarTodos');

    Route::resource('descontos', DescontoController::class)->names('descontos');
    // fim descontos

    Route::get('balcao', [OrcamentoController::class, 'balcao'])->name('orcamentos.balcao');
    Route::get('orcamentos_concluidos', [OrcamentoController::class, 'orcamentos_concluidos'])->name('orcamentos.concluidos');
    Route::get('balcao_concluidos', [OrcamentoController::class, 'balcao_concluidos'])->name('orcamentos.balcao_concluidos');
    Route::get('status_orcamentos', [OrcamentoController::class, 'kanban_orcamentos'])->name('orcamentos.status_orcamentos');
    // rotas separação e conferência
    Route::get('/logistica/separacao', SeparacaoListaPage::class)->name('logistica.separacao.lista');

    // ========== ROTA DE ROMANEIOS (ENTREGAS) ==========
    Route::resource('romaneios', RomaneioController::class);
    Route::post('romaneios/{romaneio}/add-batches', [RomaneioController::class, 'addBatches'])->name('romaneios.add_batches');
    Route::delete('romaneios/{romaneio}/remove-batch/{batch}', [RomaneioController::class, 'removeBatch'])->name('romaneios.remove_batch');
    Route::post('romaneios/{romaneio}/update-status', [RomaneioController::class, 'updateStatus'])->name('romaneios.update_status');
    Route::get('romaneios/{romaneio}/pdf', [RomaneioController::class, 'exportPdf'])->name('romaneios.pdf');

    Route::get('/separacao', ListaSeparacao::class)->name('separacao.index');
    Route::get('/conferencia', ListaConferencia::class)->name('conferencia.index');

    Route::get('/orcamentos/{id}/separacao', [SeparacaoController::class, 'show'])->name('orcamentos.separacao.show');
    Route::post('/orcamentos/{id}/separacao/iniciar', [SeparacaoController::class, 'iniciar'])->name('orcamentos.separacao.iniciar');
    Route::patch('/picking/{batch}/item/{item}/separar', [SeparacaoController::class, 'separarItem'])->name('picking.item.separar');
    Route::post('/picking/{batch}/concluir', [SeparacaoController::class, 'concluir'])->name('picking.concluir');
    Route::get('/picking/{batch}/etiquetas', [\App\Http\Controllers\EtiquetaController::class, 'gerarEtiquetas'])->name('picking.etiquetas');
    Route::get('/picking/{batch}/etiqueta-simples', [\App\Http\Controllers\EtiquetaController::class, 'gerarEtiquetaSimples'])->name('picking.etiqueta_simples');

    /*  Route::get('/orcamentos/{id}/conferencia', [ConferenciaController::class, 'show'])->name('orcamentos.conferencia.show');
     Route::post('/orcamentos/{id}/conferencia/iniciar', [ConferenciaController::class, 'iniciar'])->name('orcamentos.conferencia.iniciar');
     Route::patch('/conferencia/{conf}/item/{item}/conferir', [ConferenciaController::class, 'conferirItem'])->name('conferencia.item.conferir');
     Route::post('/conferencia/{conf}/concluir', [ConferenciaController::class, 'concluir'])->name('conferencia.concluir');

 // Lista geral de orçamentos em conferência
 Route::get('/conferencia', [ConferenciaController::class, 'index'])
     ->name('conferencia.index');
     */

    Route::post('/orcamentos/{orcamento}/atualizar-precos', [OrcamentoController::class, 'atualizarPrecos'])
        ->name('orcamentos.atualizar-precos');

    // Tela de conferência de um orçamento específico
    Route::get('/orcamentos/{orcamento}/conferencia', [ConferenciaController::class, 'show'])
        ->name('orcamentos.conferencia.show');

    // Download do relatório PDF de conferência
    Route::get('/orcamentos/{orcamento}/conferencia/pdf', [ConferenciaController::class, 'downloadPdf'])
        ->name('orcamentos.conferencia.pdf');

    // CONFERÊNCIA DE COMPRAS (Módulo 2)
    Route::get('/pedido-compras/{pedido}/conferencia', [ConferenciaController::class, 'showCompra'])
        ->name('pedido-compras.conferencia.show');
    Route::post('/pedido-compras/{pedido}/conferencia/iniciar', [ConferenciaController::class, 'iniciarCompra'])
        ->name('pedido-compras.conferencia.iniciar');

    // fim rotas separação e conferência

    //Route::resource('pagamentos', PagamentoController::class)->names('pagamentos');
    //Route::get('realizar_pagamento/{orcamento_id}', [PagamentoController::class, 'realizar_pagamento'])->name('realizar_pagamento');

    // Formulário de pagamento de orçamento
    Route::get('/orcamentos/{orcamento}/pagamento', [PagamentoController::class, 'formPagamentoOrcamento'])
        ->name('orcamentos.pagamento');

    // Salvar pagamento de orçamento
    Route::post('/orcamentos/{orcamento}/pagamento', [PagamentoController::class, 'salvarPagamentoOrcamento'])
        ->name('orcamentos.pagamento.salvar');

    Route::resource('orcamentos', OrcamentoController::class)->names('orcamentos');


    // Ver detalhes de um pagamento
    Route::get('/pagamentos/{pagamento}', [PagamentoController::class, 'show'])
        ->name('pagamentos.show');

    // Estornar um pagamento
    Route::post('/pagamentos/{pagamento}/estornar', [PagamentoController::class, 'estornar'])
        ->name('pagamentos.estornar');


    Route::get('/solicitacoes-pagamento', [SolicitacaoPagamentoController::class, 'index'])
        ->name('solicitacoes-pagamento.index');

    Route::get('/solicitacoes-pagamento/aprovadas', [SolicitacaoPagamentoController::class, 'aprovadas'])
        ->name('solicitacoes-pagamento.aprovadas');

    Route::get('/solicitacoes-pagamento/{orcamento_id}/aprovar', [SolicitacaoPagamentoController::class, 'solicitacao_orcamento'])
        ->name('solicitacoes-pagamento.aprovar');

    Route::post('/solicitacoes-pagamento/{id}/avaliar', [SolicitacaoPagamentoController::class, 'avaliar'])
        ->name('solicitacoes-pagamento.avaliar');

    Route::get('/pagamentos/comprovantes/{comprovante}/download', [PagamentoController::class, 'downloadComprovante'])
        ->name('pagamentos.comprovante.download');

    Route::get('/pagamentos/{pagamento}/comprovante-pdf', [PagamentoController::class, 'verComprovantePdf'])
        ->name('pagamentos.comprovante-pdf')
        ->middleware('auth');

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
    Route::get('/api/cnpj/{cnpj}', [FornecedorController::class, 'checkCnpjApi'])->name('api.cnpj.check');


    Route::resource('armazens', ArmazemController::class)->names('armazens');
    Route::resource('corredores', CorredorController::class)->names('corredores')->parameters(['corredores' => 'corredor']);
    Route::resource('posicoes', PosicaoController::class)->names('posicoes');
    Route::resource('inconsistencias', InconsistenciaRecebimentoController::class)->names('inconsistencias')->only(['index', 'show', 'destroy']);
    // Pedidos de Compras
    Route::get('pedido_compras/{pedidoCompra}/itens', [\App\Http\Controllers\PedidoCompraController::class, 'getItensApi'])->name('pedido_compras.itens.api');
    Route::resource('pedido_compras', PedidoCompraController::class)->names('pedido_compras');
    Route::get('/pedido_compras/{pedidoCompra}/itens-json', [PedidoCompraController::class, 'itensJson'])->name('pedido_compras.itens_json');

    // Requisições de Compras
    Route::resource('requisicao_compras', RequisicaoCompraController::class)->names('requisicao_compras');
    Route::post('/requisicao_compras/{requisicao_compra}/aprovar', [RequisicaoCompraController::class, 'aprovar'])->name('requisicao_compras.aprovar');
    Route::post('/requisicao_compras/{requisicao_compra}/rejeitar', [RequisicaoCompraController::class, 'rejeitar'])->name('requisicao_compras.rejeitar');
    Route::post('/requisicao_compras/{requisicao_compra}/gerar-pedido', [RequisicaoCompraController::class, 'gerarPedido'])->name('requisicao_compras.gerar_pedido');

    // Carregar corredores dinamicamente para JS
    Route::get('/corredores-by-armazem/{armazem_id}', function($armazem_id) {
        $corredores = \App\Models\Corredor::where('armazem_id', $armazem_id)->get(['id', 'nome']);
        return response()->json($corredores);
    })->name('corredores.by_armazem');

    // Carregar posicoes dinamicamente para JS
    Route::get('/posicoes-by-corredor/{corredor_id}', function($corredor_id) {
        $posicoes = \App\Models\Posicao::where('corredor_id', $corredor_id)->get(['id', 'nome']);
        return response()->json($posicoes);
    })->name('posicoes.by_corredor');

    Route::resource('vendedores', VendedorController::class)
        ->parameters(['vendedores' => 'vendedor']);
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::get('/usuarios/{user}/edit-password', [UserController::class, 'editPassword'])->name('usuarios.editPassword');
    Route::put('/usuarios/{user}/update-password', [UserController::class, 'updatePassword'])->name('usuarios.updatePassword');

    Route::put('/usuarios/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('usuarios.toggleBlock');


    Route::get('rdstation/checar-token', [RdstationController::class, 'checarToken'])->name('rdstation.checar-token');
    Route::get('rdstation/listar-empresas', [RdstationController::class, 'listarEmpresas'])->name('rdstation.listar-empresas');
    Route::get('rdstation/listar-negociacoes', [RdstationController::class, 'listarNegociacoes'])->name('rdstation.listar-negociacoes');
    Route::get('rdstation/criar-empresa/{id}', [RdstationController::class, 'criarEmpresa'])->name('rdstation.criar-empresa.id');

    // Relatórios de Estoques e Compras
    Route::prefix('relatorios')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/estoque-critico', [RelatorioController::class, 'estoqueCritico'])->name('relatorios.estoque_critico');
        Route::get('/historico-compras', [RelatorioController::class, 'historicoCompras'])->name('relatorios.historico_compras');
        Route::get('/fornecedores-frequentes', [RelatorioController::class, 'fornecedoresFrequentes'])->name('relatorios.fornecedores_frequentes');
        Route::get('/comparativo-precos', [RelatorioController::class, 'comparativoPrecos'])->name('relatorios.comparativo_precos');
        
        // Novos Relatórios Solicidados
        Route::get('/vencimento-produtos', [RelatorioController::class, 'vencimentoProdutos'])->name('relatorios.vencimento_produtos');
        Route::get('/reposicao-estoque', [RelatorioController::class, 'reposicaoEstoque'])->name('relatorios.reposicao_estoque');
        Route::get('/recebimento-produtos', [RelatorioController::class, 'recebimentoProdutos'])->name('relatorios.recebimento_produtos');
        Route::get('/devolucoes', [RelatorioController::class, 'devolucoes'])->name('relatorios.devolucoes');
        Route::get('/nao-conformidade', [RelatorioController::class, 'naoConformidade'])->name('relatorios.nao_conformidade');
        Route::get('/saida-produtos', [RelatorioController::class, 'saidaProdutos'])->name('relatorios.saida_produtos');
        Route::get('/vendas-margem', [RelatorioController::class, 'vendasMargem'])->name('relatorios.vendas_margem');

        // ── Logística / Separação ───────────────────────────────────────────
        Route::get('/separacao-por-roteiro', [RelatorioController::class, 'separacaoPorRoteiro'])->name('relatorios.separacao_por_roteiro');
        Route::get('/separacao-por-roteiro/exportar', [RelatorioController::class, 'exportarSeparacaoPorRoteiro'])->name('relatorios.separacao_roteiro_export');
        Route::get('/separacao-por-roteiro/pdf', [RelatorioController::class, 'exportarSeparacaoPorRoteiroPdf'])->name('relatorios.separacao_roteiro_pdf');
        Route::get('/divergencias', [RelatorioController::class, 'divergencias'])->name('relatorios.divergencias');
    });
    // ── Faturamento e Inadimplência ──────────────────────────────────────────
    Route::prefix('faturamento')->name('faturamento.')->group(function () {
        Route::get('/', \App\Livewire\Faturas\ListaFaturas::class)->name('index');
        Route::get('/inadimplencia', \App\Livewire\Faturas\RelatorioInadimplencia::class)->name('inadimplencia');
        Route::get('/conferidos', [FaturamentoController::class, 'conferidos'])->name('conferidos');
    });
});

require __DIR__ . '/auth.php';
