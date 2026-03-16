<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    @include('partials.head')
    @stack('styles')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>
        <flux:navlist.item icon="pencil-square" :href="route('clientes.index')" :current="request()->routeIs('clientes.index')"
            wire:navigate>{{ __('Novo Orçamento') }}
        </flux:navlist.item>
        <flux:navlist.item icon="calculator" :href="route('orcamentos.index')"
            :current="request()->routeIs('orcamentos.index')" wire:navigate>{{ __('Orçamentos') }}
        </flux:navlist.item>
        <flux:navlist.item icon="truck" :href="route('entrada_encomendas.kanban')"
            :current="request()->routeIs('entrada_encomendas.kanban')" wire:navigate>{{ __('Encomendas') }}
        </flux:navlist.item>
        <flux:navlist.item icon="chart-bar" :href="route('orcamentos.status_orcamentos')"
            :current="request()->routeIs('orcamentos.status_orcamentos')" wire:navigate>{{ __('Status Pedido') }}
        </flux:navlist.item>
        <flux:navlist.group heading="Balcão" expandable :expanded="false">
            <flux:navlist.item icon="banknotes" :href="route('orcamentos.balcao')"
                :current="request()->routeIs('orcamentos.balcao')" wire:navigate>{{ __('Caixa') }}
            </flux:navlist.item>
            <flux:navlist.item icon="check-badge" :href="route('orcamentos.balcao_concluidos')"
                :current="request()->routeIs('orcamentos.balcao_concluidos')" wire:navigate>
                {{ __('Pedidos Finalizados') }}</flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Clientes" expandable :expanded="false">
            <flux:navlist.item icon="user-plus" :href="route('clientes.create')"
                :current="request()->routeIs('clientes.create')" wire:navigate>{{ __('Pré-cadastro') }}
            </flux:navlist.item>
            <flux:navlist.item icon="user-group" :href="route('clientes.create_completo')"
                :current="request()->routeIs('clientes.create-completo')" wire:navigate>
                {{ __('Cadastrar Cliente') }}</flux:navlist.item>
            <flux:navlist.item icon="users" :href="route('clientes.index')"
                :current="request()->routeIs('clientes.index')" wire:navigate>{{ __('Lista de Clientes') }}
            </flux:navlist.item>
            <flux:navlist.item icon="document-text" :href="route('orcamentos.index')"
                :current="request()->routeIs('orcamentos.index')" wire:navigate>{{ __('Orçamentos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="document-duplicate" :href="route('orcamentos.copiar')"
                :current="request()->routeIs('orcamentos.copiar')" wire:navigate>{{ __('Copiar Orçamento') }}
            </flux:navlist.item>
            <flux:navlist.item icon="no-symbol" :href="route('bloqueios.index')"
                :current="request()->routeIs('bloqueios.index')" wire:navigate>{{ __('Clientes Bloqueados') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Logística" expandable :expanded="false">
            <flux:navlist.item icon="list-bullet" :href="route('separacao.index')"
                :current="request()->routeIs('separacao.index')" wire:navigate>{{ __('Painel de Separação') }}
            </flux:navlist.item>
            <flux:navlist.item icon="queue-list" :href="route('logistica.separacao.lista')"
                :current="request()->routeIs('logistica.separacao.lista')" wire:navigate>{{ __('Fila de Itens') }}
            </flux:navlist.item>
            <flux:navlist.item icon="check-circle" :href="route('conferencia.index')"
                :current="request()->routeIs('conferencia.index')" wire:navigate>{{ __('Conferência') }}
            </flux:navlist.item>
            <flux:navlist.item icon="truck" :href="route('romaneios.index')"
                :current="request()->routeIs('romaneios.*')" wire:navigate>{{ __('Romaneios (Entregas)') }}
            </flux:navlist.item>
            <flux:navlist.item icon="document-chart-bar" :href="route('relatorios.separacao_por_roteiro')"
                :current="request()->routeIs('relatorios.separacao_por_roteiro')" wire:navigate>{{ __('Fila de Carga') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Compras" expandable :expanded="false">
            <flux:navlist.item icon="building-office-2" :href="route('fornecedores.index')"
                :current="request()->routeIs('fornecedores.index')" wire:navigate>{{ __('Fornecedores') }}
            </flux:navlist.item>
            <flux:navlist.item icon="user-plus" :href="route('fornecedores.create')"
                :current="request()->routeIs('fornecedores.create')" wire:navigate>
                {{ __('Cadastrar Fornecedor') }}</flux:navlist.item>
            <flux:navlist.item icon="magnifying-glass" :href="route('consulta_preco.index')"
                :current="request()->routeIs('consulta_preco.index')" wire:navigate>{{ __('Cotações de Preço') }}
            </flux:navlist.item>
            <flux:navlist.item icon="arrow-down-tray" :href="route('entrada_encomendas.index')" :current="request()->routeIs('entrada_encomendas.index')" wire:navigate>
                {{ __('Receber Encomendas') }}</flux:navlist.item>
            <flux:navlist.item icon="clipboard-document-list" :href="route('pedido_compras.index')" :current="request()->routeIs('pedido_compras.*')" wire:navigate>
                {{ __('Pedidos de Compra') }}
            </flux:navlist.item>
            <flux:navlist.item icon="pencil-square" :href="route('requisicao_compras.index')" :current="request()->routeIs('requisicao_compras.*')" wire:navigate>
                {{ __('Requisições de Compra') }}
            </flux:navlist.item>

            <flux:navlist.group heading="Relatórios de Compra" expandable :expanded="false">
                <flux:navlist.item icon="document-chart-bar" :href="route('relatorios.historico_compras')" wire:navigate>Histórico de Compras</flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('relatorios.fornecedores_frequentes')" wire:navigate>Fornecedores Comuns</flux:navlist.item>
                <flux:navlist.item icon="arrows-right-left" :href="route('relatorios.comparativo_precos')" wire:navigate>Comparativo de Preços</flux:navlist.item>
                <flux:navlist.item icon="exclamation-triangle" :href="route('relatorios.estoque_critico')" wire:navigate>Estoque Crítico</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist.group>

        <flux:navlist.group heading="Estoque" expandable :expanded="false">
            <flux:navlist.item icon="arrows-right-left" :href="route('movimentacao.index')"
                :current="request()->routeIs('movimentacao.index')" wire:navigate>{{ __('Histórico de Movimentações') }}
            </flux:navlist.item>
            <flux:navlist.item icon="plus-circle" :href="route('movimentacao.create')" :current="request()->routeIs('movimentacao.create')" wire:navigate>
                {{ __('Nova Movimentação (Entrada/Saída)') }}</flux:navlist.item>

            <flux:navlist.group heading="Endereços Físicos" expandable :expanded="false">
                <flux:navlist.item icon="building-office" :href="route('armazens.index')" :current="request()->routeIs('armazens.*')" wire:navigate>
                    {{ __('Armazéns') }}
                </flux:navlist.item>
                <flux:navlist.item icon="map" :href="route('corredores.index')" :current="request()->routeIs('corredores.*')" wire:navigate>
                    {{ __('Corredores') }}
                </flux:navlist.item>
                <flux:navlist.item icon="map-pin" :href="route('posicoes.index')" :current="request()->routeIs('posicoes.*')" wire:navigate>
                    {{ __('Posições') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.item icon="exclamation-triangle" :href="route('inconsistencias.index')" :current="request()->routeIs('inconsistencias.*')" wire:navigate>
                {{ __('Inconsistências no Recebimento') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Produtos" expandable :expanded="false">
            <flux:navlist.item icon="cube" :href="route('produtos.index')"
                :current="request()->routeIs('produtos.index')" wire:navigate>{{ __('Lista de Produtos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="plus" :href="route('produtos.create')"
                :current="request()->routeIs('produtos.create')" wire:navigate>{{ __('Novo Produto') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Indústria" expandable :expanded="false">
            <flux:navlist.item icon="wrench-screwdriver" :href="route('blocok.index')"
                :current="request()->routeIs('blocok.*')" wire:navigate>{{ __('Ordens de Produção (Bloco K)') }}
            </flux:navlist.item>
            <flux:navlist.item icon="trash" :href="route('blocok.descartes.index')"
                :current="request()->routeIs('blocok.descartes.*')" wire:navigate>{{ __('Descartes de Produção') }}
            </flux:navlist.item>
            <flux:navlist.item icon="view-columns" :href="route('blocok.insumos.index')"
                :current="request()->routeIs('blocok.insumos.*')" wire:navigate>{{ __('Insumos de Produção') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Financeiro" expandable :expanded="false">
            <flux:navlist.item icon="users" :href="route('clientes.index')"
                :current="request()->routeIs('clientes.index')" wire:navigate>{{ __('Clientes e Créditos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="clipboard-document-check" :href="route('analise_creditos.index')"
                :current="request()->routeIs('analise_creditos.*')" wire:navigate>{{ __('Análise de Crédito') }}
            </flux:navlist.item>
            <flux:navlist.item icon="banknotes" :href="route('faturamento.index')"
                :current="request()->routeIs('faturamento.index')" wire:navigate>{{ __('Contas a Receber (Faturas)') }}
            </flux:navlist.item>
            <flux:navlist.item icon="credit-card" :href="route('solicitacoes-pagamento.index')"
                :current="request()->routeIs('solicitacoes-pagamento.*')" wire:navigate>{{ __('Contas a Pagar') }}
            </flux:navlist.item>
            <flux:navlist.item icon="check-circle" :href="route('faturamento.conferidos')"
                :current="request()->routeIs('faturamento.conferidos')" wire:navigate>{{ __('Orçamentos Conferidos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="exclamation-triangle" :href="route('faturamento.inadimplencia')"
                :current="request()->routeIs('faturamento.inadimplencia')" wire:navigate>{{ __('Inadimplência') }}
            </flux:navlist.item>
            <flux:navlist.item icon="document-text" :href="route('notas.index')"
                :current="request()->routeIs('notas.*')" wire:navigate>{{ __('Notas Fiscais') }}
            </flux:navlist.item>
            <flux:navlist.item icon="currency-dollar" :href="route('orcamentos.concluidos')"
                :current="request()->routeIs('orcamentos.concluidos')" wire:navigate>{{ __('Movimentações Financeiras') }}
            </flux:navlist.item>
            <flux:navlist.item icon="presentation-chart-line" wire:navigate>{{ __('Relatórios Financeiros') }}
            </flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Descontos" expandable :expanded="false">
            <flux:navlist.item icon="check-circle" :href="route('descontos.aprovados')"
                :current="request()->routeIs('descontos.aprovados')" wire:navigate>{{ __('Descontos Aprovados') }}
            </flux:navlist.item>
            <flux:navlist.item icon="clock" :href="route('descontos.index')"
                :current="request()->routeIs('descontos.index')" wire:navigate>{{ __('Solicitações Pendentes') }}
            </flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Administração" expandable :expanded="false">

            <flux:navlist.group heading="Configurações" expandable :expanded="false">
                <flux:navlist.item icon="user-circle" :href="route('usuarios.index')"
                    :current="request()->routeIs('usuarios.index')" wire:navigate>{{ __('Gerenciar Usuários') }}
                </flux:navlist.item>
                <flux:navlist.item icon="plus" :href="route('usuarios.create')"
                    :current="request()->routeIs('usuarios.create')" wire:navigate>{{ __('Criar Usuário') }}
                </flux:navlist.item>
                <flux:navlist.item icon="identification" :href="route('vendedores.index')"
                    :current="request()->routeIs('vendedores.index')" wire:navigate>{{ __('Vendedores') }}
                </flux:navlist.item>
                <flux:navlist.item icon="lock-closed" :href="route('filament.admin.pages.dashboard')" wire:navigate>
                    {{ __('Permissões e Níveis') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.group heading="Cadastros Auxiliares" expandable :expanded="false">
                <flux:navlist.item icon="swatch" :href="route('cores.index')"
                    :current="request()->routeIs('cores.index')" wire:navigate>{{ __('Cores e Acabamentos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="tag" :href="route('categorias.index')"
                    :current="request()->routeIs('categorias.index')" wire:navigate>{{ __('Categorias') }}
                </flux:navlist.item>
                <flux:navlist.item icon="list-bullet" :href="route('subcategorias.index')"
                    :current="request()->routeIs('subcategorias.index')" wire:navigate>
                    {{ __('Subcategorias') }}
                </flux:navlist.item>
                <flux:navlist.item icon="key" :href="route('ncm.index')"
                    :current="request()->routeIs('ncm.index')" wire:navigate>
                    {{ __('NCMs (Fiscal)') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist.group>
        <!--
        <flux:navlist variant="outline">
            <flux:navlist.group : heading="__('RD Station')" class="grid">
                <flux:navlist.item icon="home" : href="route('rdstation.checar-token')"
                    : current="request()->routeIs('rdstation.checar-token')" wire:navigate>{ { __('Checar token') }}
                </flux:navlist.item>
                <flux:navlist.item icon="banknotes" : href="route('rdstation.listar-empresas')"
                    : current="request()->routeIs('rdstation.listar-empresas')" wire:navigate>{ { __('Empresas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="shopping-cart" : href="route('rdstation.listar-negociacoes')"
                    : current="request()->routeIs('rdstation.listar-negociacoes')" wire:navigate>
                    { { __('Negociações') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>-->

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="#" target="_blank">
                {{ __('Acav') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="#" target="_blank">
                {{ __('Processos') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Configurações') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('Sair') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Configurações') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('Sair') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
    @stack('scripts')
</body>

</html>
