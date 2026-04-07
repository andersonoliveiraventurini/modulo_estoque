<x-layouts.app :title="'Relatório de Estoque Mínimo'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Relatório de Estoque Mínimo</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Cálculo dinâmico baseado no histórico de vendas.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('relatorios.estoque_minimo.historico') }}" class="inline-flex items-center rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white transition-colors">
                    <x-heroicon-o-clock class="mr-2 h-5 w-5 text-neutral-500" />
                    Ver Histórico
                </a>
                <a href="{{ route('relatorios.estoque_minimo.export', request()->all()) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                    <x-heroicon-o-document-arrow-down class="mr-2 h-5 w-5" />
                    Exportar PDF
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Filtros -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.estoque_minimo') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4 items-end">
                <div>
                    <label for="inicio" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Inicial</label>
                    <input type="date" name="inicio" id="inicio" value="{{ $inicio }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
                <div>
                    <label for="fim" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Final</label>
                    <input type="date" name="fim" id="fim" value="{{ $fim }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="apenas_abaixo" value="1" {{ request('apenas_abaixo') ? 'checked' : '' }} class="rounded border-neutral-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Apenas abaixo do mínimo</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="incluir_sem_giro" value="1" {{ request('incluir_sem_giro') ? 'checked' : '' }} class="rounded border-neutral-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Incluir produtos sem vendas</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <x-heroicon-o-magnifying-glass class="mr-2 h-5 w-5" />
                        Filtrar
                    </button>
                </div>
            </form>
            <div class="mt-2 text-xs text-neutral-500 flex justify-between">
                <span>* Período de análise: <strong>{{ number_format($numMeses, 1, ',', '.') }}</strong> meses.</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">Produto</th>
                        <th class="px-6 py-3">SKU</th>
                        <th class="px-6 py-3 text-center">Vendas Total</th>
                        <th class="px-6 py-3 text-center">Média Mensal</th>
                        <th class="px-6 py-3 text-center">Estoque Mín. Calc.</th>
                        <th class="px-6 py-3 text-center">Estoque Atual</th>
                        <th class="px-6 py-3 text-center">Status</th>
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
                            <td class="px-6 py-4 text-center">{{ number_format($produto->total_vendido, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">{{ number_format($produto->qtd_por_mes, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center font-bold">{{ number_format($produto->estoque_minimo_calculado, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $produto->abaixo_minimo ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                    {{ number_format($produto->estoque_atual, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($produto->abaixo_minimo)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        Abaixo do Mínimo
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        OK
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center">Nenhum produto encontrado no período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>