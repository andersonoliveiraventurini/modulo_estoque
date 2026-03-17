<x-layouts.app :title="'Fornecedores Frequentes'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Fornecedores Frequentes</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Classificação de fornecedores pelo volume de pedidos.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">Fornecedor</th>
                        <th class="px-6 py-3">Qtd. Pedidos</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($fornecedores as $fornecedor)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">{{ $fornecedor->nome_fantasia }}</td>
                            <td class="px-6 py-4">{{ $fornecedor->pedidos_compra_count }} pedidos</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('fornecedores.show', $fornecedor->id) }}" class="text-indigo-600 hover:underline">Ver Perfil</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center">Nenhum fornecedor encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $fornecedores->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
