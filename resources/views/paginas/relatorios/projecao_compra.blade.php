<x-layouts.app :title="'Projeção de Compra'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Projeção de Compra</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Cálculo inteligente de reposição baseado em curva de consumo.</p>
            </div>
            @if(isset($produtos))
            <div class="flex gap-2">
                <a href="{{ route('relatorios.projecao_compra.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    <x-heroicon-o-document-arrow-down class="mr-2 h-5 w-5" />
                    PDF
                </a>
                <a href="{{ route('relatorios.projecao_compra.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    <x-heroicon-o-table-cells class="mr-2 h-5 w-5" />
                    Excel
                </a>
            </div>
            @endif
        </div>

        <!-- Formulário de Entrada -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.projecao_compra') }}" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5 items-end">
                    <div>
                        <label for="data_pedido" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data do Pedido</label>
                        <input type="date" name="data_pedido" id="data_pedido" value="{{ request('data_pedido', today()->toDateString()) }}" required class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    </div>
                    <div>
                        <label for="previsao_recebimento" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Previsão Recebimento</label>
                        <input type="date" name="previsao_recebimento" id="previsao_recebimento" value="{{ request('previsao_recebimento', today()->addDays(15)->toDateString()) }}" required class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    </div>
                    <div>
                        <label for="meses_compra" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Meses de Compra (1-24)</label>
                        <input type="number" name="meses_compra" id="meses_compra" value="{{ request('meses_compra', 1) }}" min="1" max="24" required class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    </div>
                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Categoria</label>
                        <select name="categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center h-10">
                        <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <x-heroicon-o-cpu-chip class="mr-2 h-5 w-5" />
                            Calcular Projeção
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 border-t border-neutral-100 pt-4 dark:border-neutral-700">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="abater_estoque_atual" value="1" {{ request('abater_estoque_atual', '1') == '1' ? 'checked' : '' }} class="rounded border-neutral-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Abater estoque atual do cálculo</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="abater_consumo_ate_recebimento" value="1" {{ request('abater_consumo_ate_recebimento', '1') == '1' ? 'checked' : '' }} class="rounded border-neutral-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Abater consumo previsto até o recebimento</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="apenas_criticos" value="1" {{ request('apenas_criticos') ? 'checked' : '' }} class="rounded border-neutral-300 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400 font-semibold">Mostrar apenas produtos críticos</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer ml-auto">
                        <input type="checkbox" name="save_history" value="1" class="rounded border-neutral-300 text-green-600 shadow-sm focus:ring-green-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Salvar no histórico</span>
                    </label>
                </div>
            </form>
        </div>

        @if(isset($produtos))
            <!-- Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-xl bg-indigo-50 p-4 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800">
                    <p class="text-sm text-indigo-600 dark:text-indigo-400">Total de Itens Sugeridos</p>
                    <p class="text-2xl font-bold text-indigo-900 dark:text-white">{{ number_format($produtos->where('quantidade_sugerida', '>', 0)->count(), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-green-50 p-4 border border-green-100 dark:bg-green-900/20 dark:border-green-800">
                    <p class="text-sm text-green-600 dark:text-green-400">Valor Total Estimado</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-white">R$ {{ number_format($produtos->sum(function($p){ return $p->quantidade_sugerida * $p->preco_custo; }), 2, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800">
                    <p class="text-sm text-red-600 dark:text-red-400">Produtos Críticos (Abaixo do Mín.)</p>
                    <p class="text-2xl font-bold text-red-900 dark:text-white">{{ $produtos->where('abaixo_minimo', true)->count() }}</p>
                </div>
            </div>

            <!-- Listagem -->
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
                <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                    <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                        <tr>
                            <th class="px-4 py-3">Produto</th>
                            <th class="px-4 py-3 text-center">Consumo Mensal</th>
                            <th class="px-4 py-3 text-center">Estoque Atual</th>
                            <th class="px-4 py-3 text-center">Consumo Previsto</th>
                            <th class="px-4 py-3 text-center bg-indigo-50 dark:bg-indigo-900/30">Sugestão Compra</th>
                            <th class="px-4 py-3 text-center">Preço Custo</th>
                            <th class="px-4 py-3 text-center">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($produtos->where('quantidade_sugerida', '>', 0) as $p)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-neutral-900 dark:text-white">{{ $p->nome }}</div>
                                    <div class="text-xs text-neutral-400">SKU: {{ $p->sku }}</div>
                                    @if($p->abaixo_minimo)
                                        <span class="mt-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-medium text-red-800">CRÍTICO</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">{{ number_format($p->consumo_mensal, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center">{{ number_format($p->estoque_atual, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center text-neutral-400">{{ number_format($p->detalhe_consumo_previsao, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center font-bold text-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/10">{{ number_format($p->quantidade_sugerida, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center">R$ {{ number_format($p->preco_custo, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center font-medium">R$ {{ number_format($p->quantidade_sugerida * $p->preco_custo, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">Nenhuma sugestão de compra para os parâmetros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-neutral-300 p-20 text-center dark:border-neutral-700">
                <x-heroicon-o-calculator class="mx-auto h-12 w-12 text-neutral-300" />
                <h3 class="mt-2 text-sm font-semibold text-neutral-900 dark:text-white">Nenhum cálculo realizado</h3>
                <p class="mt-1 text-sm text-neutral-500">Preencha os dados acima e clique em "Calcular Projeção" para iniciar.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
