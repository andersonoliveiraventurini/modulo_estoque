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

        <flux:navlist.group heading="Clientes" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('clientes.create')"
                :current="request()->routeIs('clientes.create')" wire:navigate>{{ __('Pré-cadastro cliente') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('clientes.create_completo')"
                :current="request()->routeIs('clientes.create-completo')" wire:navigate>
                {{ __('Cadastrar cliente') }}</flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('clientes.index')"
                :current="request()->routeIs('clientes.index')" wire:navigate>{{ __('Listar clientes') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('bloqueios.index')"
                :current="request()->routeIs('bloqueios.index')" wire:navigate>{{ __('Clientes bloqueados') }}
            </flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Fornecedores" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('fornecedores.index')"
                :current="request()->routeIs('fornecedores.index')" wire:navigate>{{ __('Listar fornecedores') }}
            </flux:navlist.item>
            <flux:navlist.item icon="truck" :href="route('fornecedores.create')"
                :current="request()->routeIs('fornecedores.create')" wire:navigate>
                {{ __('Cadastrar fornecedor') }}</flux:navlist.item>
        </flux:navlist.group>


        <flux:navlist.group heading="Produtos" expandable :expanded="false">
            <flux:navlist.item icon="home" :href="route('produtos.index')"
                :current="request()->routeIs('produtos.index')" wire:navigate>{{ __('Listar produtos') }}
            </flux:navlist.item>
            <flux:navlist.item icon="home" :href="route('produtos.create')"
                :current="request()->routeIs('produtos.create')" wire:navigate>{{ __('Criar produto') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('RD Station')" class="grid">
                <flux:navlist.item icon="home" :href="route('rdstation.checar-token')"
                    :current="request()->routeIs('rdstation.checar-token')" wire:navigate>{{ __('Checar token') }}
                </flux:navlist.item>
                <flux:navlist.item icon="banknotes" :href="route('rdstation.listar-empresas')"
                    :current="request()->routeIs('rdstation.listar-empresas')" wire:navigate>{{ __('Empresas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="shopping-cart" :href="route('rdstation.listar-negociacoes')"
                    :current="request()->routeIs('rdstation.listar-negociacoes')" wire:navigate>
                    {{ __('Negociações') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Vendas')" class="grid">
                <flux:navlist.item icon="home" :href="route('orcamentos.create')"
                    :current="request()->routeIs('orcamentos.create')" wire:navigate>{{ __('Gerar orçamento') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('orcamentos.index')"
                    :current="request()->routeIs('orcamentos.index')" wire:navigate>{{ __('Listar orçamentos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('pedidos.index')"
                    :current="request()->routeIs('pedidos.index')" wire:navigate>{{ __('Listar pedidos') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
        
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Financeiro')" class="grid">
                <flux:navlist.item icon="home" :href="route('analise_creditos.index')"
                    :current="request()->routeIs('analise_creditos.index')" wire:navigate>
                    {{ __('Análises de crédito') }}
                </flux:navlist.item>

                <flux:navlist.item icon="home" :href="route('vendas.index')"
                    :current="request()->routeIs('vendas.index')" wire:navigate>{{ __('Listar vendas') }}
                </flux:navlist.item>

                <flux:navlist.item icon="home" :href="route('descontos.index')"
                    :current="request()->routeIs('descontos.index')" wire:navigate>{{ __('Descontos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('notas.index')"
                    :current="request()->routeIs('notas.index')" wire:navigate>{{ __('Listar notas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="home" :href="route('notas.create')"
                    :current="request()->routeIs('notas.create')" wire:navigate>{{ __('Gerar nota') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

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
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
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
