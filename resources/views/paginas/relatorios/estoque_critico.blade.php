<x-layouts.app :title="'Estoque Crítico'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Estoque Crítico</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Produtos com saldo abaixo do estoque mínimo definido.</p>
            </div>
            <a href="{{ route('pedido_compras.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <x-heroicon-o-plus class="mr-2 h-5 w-5" />
                Novo Pedido de Compra
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">Produto</th>
                        <th class="px-6 py-3">SKU</th>
                        <th class="px-6 py-3">Estoque Atual</th>
                        <th class="px-6 py-3">Estoque Mínimo</th>
                        <th class="px-6 py-3">Diferença</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($produtos as $produto)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                {{ $produto->nome }}
                                @if($produto->cor)
                                    <span class="ml-1 text-xs text-neutral-400">({{ $produto->cor->nome }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $produto->sku }}</td>
                            <td class="px-6 py-4 font-bold text-red-600">
                                {{ $produto->estoque_atual }} {{ $produto->unidade_medida }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $produto->estoque_minimo }} {{ $produto->unidade_medida }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $produto->estoque_atual - $produto->estoque_minimo }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('produtos.show', $produto->id) }}" class="text-indigo-600 hover:underline">Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">Nenhum produto com estoque crítico encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $produtos->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
