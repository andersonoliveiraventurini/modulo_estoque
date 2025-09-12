<x-layouts.app :title="__('Visualizar usuário')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Detalhes do Usuário
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Informações completas do usuário selecionado.
                </p>

                <!-- Abas -->
                <x-tabs default="basico">
                    <!-- Informações básicas -->
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-show-field label="Nome" :value="$usuario->name" />
                            <x-show-field label="E-mail" :value="$usuario->email" />
                            <x-show-field label="Ativo" :value="$usuario->ativo ? 'Sim' : 'Não'" />
                        </div>
                    </x-tab>

                    @if ($usuario->vendedor)
                        <x-tab name="pedidos" label="Pedidos">
                            <div class="space-y-4">
                                @forelse($usuario->vendedor->pedidos as $pedido)
                                    <div class="border rounded-lg p-4 dark:border-neutral-700">
                                        <p><span class="font-medium">#{{ $pedido->id }}</span> - {{ $pedido->status }}
                                        </p>
                                        <p class="text-sm text-neutral-500">
                                            Valor: R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                                        </p>
                                        <p class="text-sm text-neutral-500">
                                            Criado em: {{ $pedido->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-sm text-neutral-500">Nenhum pedido encontrado.</p>
                                @endforelse
                            </div>
                        </x-tab>

                        <x-tab name="clientes" label="Clientes">
                            <div class="space-y-4">
                                @forelse($usuario->vendedor->clientes as $cliente)
                                    <div class="border rounded-lg p-4 dark:border-neutral-700">
                                        <p class="font-medium">{{ $cliente->nome }}</p>
                                        <p class="text-sm text-neutral-500">{{ $cliente->email }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-neutral-500">Nenhum cliente encontrado.</p>
                                @endforelse
                            </div>
                        </x-tab>
                    @endif

                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <x-button href="{{ route('usuarios.edit', $usuario) }}">
                        Editar
                    </x-button>
                    <x-button href="{{ route('usuarios.index') }}">
                        Voltar
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
