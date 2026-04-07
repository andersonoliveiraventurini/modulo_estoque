<x-layouts.app :title="'Relatório de Vendas e Estoque Sugerido'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Vendas e Estoque Sugerido</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Análise de vendas no período e sugestão de estoque mínimo.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="inline-flex items-center rounded-lg bg-neutral-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-neutral-500">
                    <x-heroicon-o-printer class="mr-2 h-5 w-5" />
                    Imprimir
                </button>
                <a href="{{ route('relatorios.vendas_estoque_sugerido.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    <x-heroicon-o-document-arrow-down class="mr-2 h-5 w-5" />
                    PDF
                </a>
                <a href="{{ route('relatorios.vendas_estoque_sugerido.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    <x-heroicon-o-table-cells class="mr-2 h-5 w-5" />
                    Excel
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.vendas_estoque_sugerido') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5 items-end">
                <div>
                    <label for="inicio" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Inicial</label>
                    <input type="date" name="inicio" id="inicio" value="{{ $inicio }}" required class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
                <div>
                    <label for="fim" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Final</label>
                    <input type="date" name="fim" id="fim" value="{{ $fim }}" required class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
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
                <div>
                    <label for="fornecedor_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Fornecedor</label>
                    <select name="fornecedor_id" id="fornecedor_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Todos</option>
                        @foreach($fornecedores as $forn)
                            <option value="{{ $forn->id }}" {{ request('fornecedor_id') == $forn->id ? 'selected' : '' }}>{{ $forn->nome_fantasia }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="armazem_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Local de Estoque</label>
                    <select name="armazem_id" id="armazem_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Todos</option>
                        @foreach($armazens as $arm)
                            <option value="{{ $arm->id }}" {{ request('armazem_id') == $arm->id ? 'selected' : '' }}>{{ $arm->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-5 flex justify-end">
                    <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <x-heroicon-o-magnifying-glass class="mr-2 h-5 w-5" />
                        Gerar Relatório
                    </button>
                </div>
            </form>
        </div>

        @if(count($produtos) > 0)
            <!-- Gráficos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-lg font-semibold mb-4 dark:text-white">Top 10 Produtos - Quantidade Vendida</h3>
                    <canvas id="chartVendas" height="200"></canvas>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-lg font-semibold mb-4 dark:text-white">Comparativo: Média vs Estoque Sugerido (Top 10)</h3>
                    <canvas id="chartComparativo" height="200"></canvas>
                </div>
            </div>

            <!-- Tabela -->
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
                <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                    <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                        <tr>
                            <th class="px-6 py-3">Produto</th>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3 text-center">Total Vendido</th>
                            <th class="px-6 py-3 text-center">Média Mensal</th>
                            <th class="px-6 py-3 text-center">Estoque Sugerido</th>
                            <th class="px-6 py-3 text-center">Estoque Atual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($produtos as $produto)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                                <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $produto->nome }}
                                </td>
                                <td class="px-6 py-4">{{ $produto->sku }}</td>
                                <td class="px-6 py-4 text-center">{{ number_format($produto->total_vendido, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">{{ number_format($produto->qtd_por_mes, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-600">{{ number_format($produto->estoque_minimo_sugerido, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="{{ $produto->estoque_atual < $produto->estoque_minimo_sugerido ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                        {{ number_format($produto->estoque_atual, 2, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-xl border border-neutral-200 bg-white p-10 text-center dark:border-neutral-700 dark:bg-neutral-800">
                <p class="text-neutral-500">Nenhum dado encontrado para os filtros selecionados.</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @if(count($produtos) > 0)
            const topProdutos = {!! json_encode($produtos->sortByDesc('total_vendido')->take(10)->values()) !!};
            
            // Gráfico de Vendas
            new Chart(document.getElementById('chartVendas'), {
                type: 'bar',
                data: {
                    labels: topProdutos.map(p => p.nome.substring(0, 20) + '...'),
                    datasets: [{
                        label: 'Total Vendido',
                        data: topProdutos.map(p => p.total_vendido),
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Gráfico Comparativo
            new Chart(document.getElementById('chartComparativo'), {
                type: 'bar',
                data: {
                    labels: topProdutos.map(p => p.sku),
                    datasets: [
                        {
                            label: 'Média Mensal',
                            data: topProdutos.map(p => p.qtd_por_mes),
                            backgroundColor: 'rgba(156, 163, 175, 0.6)',
                        },
                        {
                            label: 'Estoque Sugerido',
                            data: topProdutos.map(p => p.estoque_minimo_sugerido),
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        @endif
    </script>
    @endpush
</x-layouts.app>
