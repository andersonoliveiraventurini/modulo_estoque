<x-layouts.app title="Pedidos de Compras">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-indigo-500" />
                        Pedidos de Compras
                    </h2>
                    <a href="{{ route('pedido_compras.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">
                        + Novo Pedido
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 text-green-600 bg-green-100 p-4 rounded text-sm">{{ session('success') }}</div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº Pedido</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Pedido</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prev. Entrega</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                            @forelse ($pedidos as $pedido)
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $pedido->id }}</td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ $pedido->numero_pedido ?: '-' }}</td>
                                    <td class="px-4 py-3 dark:text-gray-100">{{ optional($pedido->fornecedor)->nome_fantasia }}</td>
                                    <td class="px-4 py-3 dark:text-gray-300">{{ $pedido->data_pedido->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 dark:text-gray-300">{{ optional($pedido->previsao_entrega)?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold dark:text-gray-100">R$ {{ number_format($pedido->valor_total ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $colors = [
                                                'aguardando' => 'yellow',
                                                'parcialmente_recebido' => 'blue',
                                                'recebido' => 'green',
                                                'cancelado' => 'red',
                                            ];
                                            $labels = [
                                                'aguardando' => 'Aguardando',
                                                'parcialmente_recebido' => 'Parcial',
                                                'recebido' => 'Recebido',
                                                'cancelado' => 'Cancelado',
                                            ];
                                            $c = $colors[$pedido->status] ?? 'gray';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $c }}-100 text-{{ $c }}-800 dark:bg-{{ $c }}-900 dark:text-{{ $c }}-300">
                                            {{ $labels[$pedido->status] ?? $pedido->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <a href="{{ route('pedido_compras.show', $pedido->id) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                        <a href="{{ route('pedido_compras.edit', $pedido->id) }}" class="text-gray-600 hover:text-gray-900">Editar</a>
                                        <form action="{{ route('pedido_compras.destroy', $pedido->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este pedido? O preço de custo dos produtos será revertido ao valor anterior.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Deletar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum pedido de compra cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $pedidos->links() }}</div>
            </div>
        </div>
    </div>
</x-layouts.app>
