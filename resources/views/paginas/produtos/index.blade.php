<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Clientes <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os clientes.</flux:text>
                </div>
            </div>

            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Produtos <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os produtos.</flux:text>
                </div>
            </div>
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Vendas <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos as vendas.</flux:text>
                </div>
            </div>
        </div>
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <!-- Título -->
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    Lista de Produtos
                </h2>

                <!-- Tabela -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr
                                class="text-left text-sm font-semibold text-zinc-600 dark:text-zinc-300 border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Nome</th>
                                <th class="px-4 py-3">Preço</th>
                                <th class="px-4 py-3">Criado em</th>
                                <th class="px-4 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($produtos as $produto)
                                <tr
                                    class="text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    <td class="px-4 py-3">{{ $produto->id }}</td>
                                    <td class="px-4 py-3">{{ $produto->nome }}</td>
                                    <td class="px-4 py-3">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ $produto->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('produtos.edit', $produto) }}"
                                            class="text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="mt-4">
                    {{ $produtos->links('pagination::tailwind') }}
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
