<x-layouts.app title="Relatório de Pedidos de Compra">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-document-chart-bar class="w-5 h-5 text-indigo-500" />
                    Relatório de Pedidos de Compra
                </h2>
                <div class="flex gap-2 print:hidden">
                    <button onclick="window.print()" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-700 rounded-md text-sm font-medium hover:bg-gray-50">
                        <x-heroicon-o-printer class="w-4 h-4" />
                        Imprimir
                    </button>
                </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="mb-6 p-4 bg-gray-50 dark:bg-neutral-800 rounded-lg print:hidden">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período Pedido</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded-md text-xs">
                            <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded-md text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor</label>
                        <select name="fornecedor_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach($fornecedores as $f)
                                <option value="{{ $f->id }}" @selected(request('fornecedor_id') == $f->id)>{{ $f->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            <option value="aguardando" @selected(request('status') == 'aguardando')>Aguardando</option>
                            <option value="parcialmente_recebido" @selected(request('status') == 'parcialmente_recebido')>Parcial</option>
                            <option value="recebido" @selected(request('status') == 'recebido')>Recebido</option>
                            <option value="cancelado" @selected(request('status') == 'cancelado')>Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Produto</label>
                        <select name="produto_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach($produtos as $p)
                                <option value="{{ $p->id }}" @selected(request('produto_id') == $p->id)>{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valores (Min/Max)</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <input type="number" name="valor_min" value="{{ request('valor_min') }}" placeholder="Min" class="block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded-md text-xs">
                            <input type="number" name="valor_max" value="{{ request('valor_max') }}" placeholder="Max" class="block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded-md text-xs">
                        </div>
                    </div>

                    <div class="flex items-end">
                        <x-button type="submit" variant="primary" class="w-full">Gerar Relatório</x-button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-neutral-700">
                    <thead class="bg-gray-100 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Data</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Número</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Fornecedor</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Previsão</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @php $somaTotal = 0; @endphp
                        @forelse($pedidos as $pedido)
                        @php $somaTotal += $pedido->valor_total; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">
                            <td class="px-4 py-2 text-xs">{{ $pedido->data_pedido?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-xs font-medium">{{ $pedido->numero_pedido ?? 'ID: '.$pedido->id }}</td>
                            <td class="px-4 py-2 text-xs">{{ $pedido->fornecedor->nome_fantasia }}</td>
                            <td class="px-4 py-2 text-xs uppercase">{{ $pedido->status }}</td>
                            <td class="px-4 py-2 text-xs">{{ $pedido->previsao_entrega?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-2 text-xs text-right font-medium">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Nenhum pedido encontrado para os filtros selecionados.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-neutral-800 font-bold">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-sm">TOTAL GERAL:</td>
                            <td class="px-4 py-2 text-right text-sm text-indigo-600">R$ {{ number_format($somaTotal, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
