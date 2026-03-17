<x-layouts.app :title="'Histórico de Compras'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Histórico de Compras</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Registro de todos os pedidos de compra realizados.</p>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.historico_compras') }}" method="GET" class="flex gap-4">
                <div class="flex flex-col">
                    <label class="text-xs font-semibold uppercase text-neutral-500">Início</label>
                    <input type="date" name="inicio" value="{{ request('inicio') }}" class="rounded-lg border-neutral-300 text-sm focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-semibold uppercase text-neutral-500">Fim</label>
                    <input type="date" name="fim" value="{{ request('fim') }}" class="rounded-lg border-neutral-300 text-sm focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Fornecedor</th>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($pedidos as $pedido)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="px-6 py-4 font-bold">#{{ $pedido->id }}</td>
                            <td class="px-6 py-4">{{ $pedido->fornecedor->nome_fantasia }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $pedido->status == 'recebido' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($pedido->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('pedido_compras.show', $pedido->id) }}" class="text-indigo-600 hover:underline">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">Nenhum pedido encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $pedidos->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
