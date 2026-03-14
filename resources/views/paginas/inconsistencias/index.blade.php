<x-layouts.app title="Inconsistências no Recebimento">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500" />
                    Inconsistências no Recebimento
                </h2>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Data</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Pedido Compra</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Produto</th>
                            <th class="py-3 px-4 font-semibold text-sm font-bold text-red-600">Qtd. Esperada</th>
                            <th class="py-3 px-4 font-semibold text-sm font-bold text-red-600">Qtd. Recebida</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Conferente</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inconsistencias as $inc)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $inc->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('pedido_compras.show', $inc->pedido_compra_id) }}" class="underline">
                                        #{{ $inc->pedido_compra_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ $inc->produto->nome }}</span><br>
                                    <small class="text-gray-500 text-xs">{{ $inc->produto->sku }}</small>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $inc->quantidade_esperada }}</td>
                                <td class="py-3 px-4 text-sm font-bold text-red-600">{{ $inc->quantidade_recebida }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $inc->usuario->name }}</td>
                                <td class="py-3 px-4 text-right flex justify-end gap-2">
                                    <a href="{{ route('movimentacao.show', $inc->movimentacao_id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition" title="Ver Movimentação">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </a>
                                    <form action="{{ route('inconsistencias.destroy', $inc->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover este registro de inconsitência?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 rounded-lg transition" title="Remover Log">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Nenhuma inconsistência registrada. Tudo em ordem! 
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $inconsistencias->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
