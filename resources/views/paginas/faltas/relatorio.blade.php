<x-layouts.app title="Relatório de Faltas">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-document-chart-bar class="w-5 h-5 text-indigo-500" />
                    Relatório de Faltas sem Pedido
                </h2>
                <button onclick="window.print()" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-700 rounded-md text-sm font-medium hover:bg-gray-50 print:hidden transition">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    Imprimir Relatório
                </button>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="mb-6 p-4 bg-gray-50 dark:bg-neutral-800 rounded-lg print:hidden">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Produto</label>
                        <select name="produto_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos os produtos</option>
                            @foreach($produtos as $p)
                                <option value="{{ $p->id }}" @selected(request('produto_id') == $p->id)>{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                        <select name="vendedor_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(request('vendedor_id') == $v->id)>{{ $v->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-input name="cliente" label="Cliente" placeholder="Filtrar cliente..." value="{{ request('cliente') }}" />
                    
                    <div class="lg:col-span-2 flex items-end gap-2">
                        <div class="flex-1 grid grid-cols-2 gap-2">
                            <x-input name="data_inicio" label="De" type="date" value="{{ request('data_inicio') }}" />
                            <x-input name="data_fim" label="Até" type="date" value="{{ request('data_fim') }}" />
                        </div>
                        <x-button type="submit" variant="primary">Filtrar</x-button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto border border-gray-200 dark:border-neutral-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-neutral-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">Data</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">Nº Falta</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">Cliente</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">SKU/ID</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">Descrição Produto</th>
                            <th class="px-3 py-2 text-center text-[10px] font-bold text-gray-600 uppercase">Qtd</th>
                            <th class="px-3 py-2 text-right text-[10px] font-bold text-gray-600 uppercase">Total Estimado</th>
                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-600 uppercase">Vendedor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 text-xs">
                        @php $totalGeral = 0; @endphp
                        @forelse($faltas as $falta)
                            @foreach($falta->itens as $item)
                            @php $totalGeral += $item->valor_total; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/30">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $falta->created_at->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 font-medium text-indigo-600">{{ $falta->numero_falta }}</td>
                                <td class="px-3 py-2 truncate max-w-[150px]">{{ $falta->cliente?->nome ?? $falta->nome_cliente ?? '-' }}</td>
                                <td class="px-3 py-2 font-mono text-gray-400">{{ $item->produto?->sku ?? $item->produto?->id ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $item->descricao_produto }}</td>
                                <td class="px-3 py-2 text-center font-medium">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-bold">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $falta->vendedor?->user->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 italic">Nenhum registro encontrado para estes filtros.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-neutral-900 border-t-2 border-gray-200 dark:border-neutral-700">
                        <tr class="font-bold">
                            <td colspan="6" class="px-3 py-3 text-right text-gray-500 uppercase text-xs">Soma Total do Período:</td>
                            <td class="px-3 py-3 text-right text-indigo-600 dark:text-indigo-400 text-sm">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
