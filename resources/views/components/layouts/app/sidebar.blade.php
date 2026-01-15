<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>
        <flux:navlist.item icon="calculator" :href="route('orcamentos.index')"
            :current="request()->routeIs('orcamentos.index')" wire:navigate>{{ __('Orçamentos') }}
        </flux:navlist.item>
        <flux:navlist.item icon="chart-bar" :href="route('orcamentos.status_orcamentos')"
            :current="request()->routeIs('orcamentos.status_orcamentos')" wire:navigate>{{ __('Status orçamentos') }}
        </flux:navlist.item>
        <flux:navlist.group heading="Balcão" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('orcamentos.balcao')"
                :current="request()->routeIs('orcamentos.balcao')" wire:navigate>{{ __('Caixa') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('orcamentos.balcao_concluidos')"
                :current="request()->routeIs('orcamentos.balcao_concluidos')" wire:navigate>
                {{ __('Pedidos Finalizados') }}</flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Clientes" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('clientes.create')"
                :current="request()->routeIs('clientes.create')" wire:navigate>{{ __('Pré-cadastro') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('clientes.create_completo')"
                :current="request()->routeIs('clientes.create-completo')" wire:navigate>
                {{ __('Cadastrar cliente') }}</flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('clientes.index')"
                :current="request()->routeIs('clientes.index')" wire:navigate>{{ __('Clientes') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('orcamentos.index')"
                :current="request()->routeIs('orcamentos.index')" wire:navigate>{{ __('Orçamentos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('bloqueios.index')"
                :current="request()->routeIs('bloqueios.index')" wire:navigate>{{ __('Bloqueados') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Logística" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('separacao.index')"
                :current="request()->routeIs('separacao.index')" wire:navigate>{{ __('Separação') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('conferencia.index')"
                :current="request()->routeIs('conferencia.index')" wire:navigate>{{ __('Conferência') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Compras" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('fornecedores.index')"
                :current="request()->routeIs('fornecedores.index')" wire:navigate>{{ __('Listar fornecedores') }}
            </flux:navlist.item>
            <flux:navlist.item icon="truck" :href="route('fornecedores.create')"
                :current="request()->routeIs('fornecedores.create')" wire:navigate>
                {{ __('Cadastrar fornecedor') }}</flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('consulta_preco.index')"
                :current="request()->routeIs('consulta_preco.index')" wire:navigate>{{ __('Encomendas') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Estoque" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('movimentacao.index')"
                :current="request()->routeIs('movimentacao.index')" wire:navigate>{{ __('Movimentações') }}
            </flux:navlist.item>
            <flux:navlist.item icon="truck" :href="route('movimentacao.create')"
                :current="request()->routeIs('movimentacao.create')" wire:navigate>
                {{ __('Receber produto') }}</flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Produtos" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('produtos.index')"
                :current="request()->routeIs('produtos.index')" wire:navigate>{{ __('Listar produtos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('produtos.create')"
                :current="request()->routeIs('produtos.create')" wire:navigate>{{ __('Criar produto') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('consulta_preco.index')"
                :current="request()->routeIs('consulta_preco.index')" wire:navigate>{{ __('Encomendas') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Financeiro" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('clientes.index')"
                :current="request()->routeIs('clientes.index')" wire:navigate>{{ __('Clientes') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('descontos.aprovados')"
                :current="request()->routeIs('descontos.aprovados')" wire:navigate>{{ __('Descontos Aprovados') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('descontos.index')"
                :current="request()->routeIs('descontos.index')" wire:navigate>{{ __('Descontos Solicitados') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('orcamentos.concluidos')"
                :current="request()->routeIs('orcamentos.concluidos')" wire:navigate>{{ __('Movimentações') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Administração" expandable :expanded="false">

            <flux:navlist.group heading="Usuários e perfis" expandable :expanded="false">
                <flux:navlist.item icon="home" :href="route('usuarios.index')"
                    :current="request()->routeIs('usuarios.index')" wire:navigate>{{ __('Usuários') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('usuarios.create')"
                    :current="request()->routeIs('usuarios.create')" wire:navigate>{{ __('Criar usuário') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('vendedores.index')"
                    :current="request()->routeIs('vendedores.index')" wire:navigate>{{ __('Vendedores') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('vendedores.create')"
                    :current="request()->routeIs('vendedores.create')" wire:navigate>{{ __('Criar vendedor') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.item icon="lock-closed" :href="route('filament.admin.pages.dashboard')" wire:navigate>
                {{ __('Permissões') }}
            </flux:navlist.item>
            <flux:navlist.group heading="Cores" expandable :expanded="false">
                <flux:navlist.item icon="home" :href="route('cores.index')"
                    :current="request()->routeIs('cores.index')" wire:navigate>{{ __('Cores produtos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('cores.create')"
                    :current="request()->routeIs('cores.create')" wire:navigate>{{ __('Criar cor') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group heading="Categorias" expandable :expanded="false">
                <flux:navlist.item icon="home" :href="route('categorias.index')"
                    :current="request()->routeIs('categorias.index')" wire:navigate>{{ __('Listar categorias') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('categorias.create')"
                    :current="request()->routeIs('categorias.create')" wire:navigate>{{ __('Criar categoria') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <flux:navlist.group heading="Subcategorias" expandable :expanded="false">
                <flux:navlist.item icon="home" :href="route('subcategorias.index')"
                    :current="request()->routeIs('subcategorias.index')" wire:navigate>
                    {{ __('Listar subcategorias') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('subcategorias.create')"
                    :current="request()->routeIs('subcategorias.create')" wire:navigate>{{ __('Criar subcategoria') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group heading="NCM" expandable :expanded="false">
                <flux:navlist.item icon="home" :href="route('ncm.index')"
                    :current="request()->routeIs('ncm.index')" wire:navigate>
                    {{ __('Listar NCMs') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('ncm.create')"
                    :current="request()->routeIs('ncm.create')" wire:navigate>{{ __('Criar NCM') }}
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
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
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
</body>

</html>
