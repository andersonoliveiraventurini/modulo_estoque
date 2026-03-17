<x-layouts.app :title="'Comparativo de Preços'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Comparativo de Preços</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Analise a variação de custo de um produto entre fornecedores.</p>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.comparativo_precos') }}" method="GET" class="flex gap-4">
                <div class="flex min-w-[300px] flex-col">
                    <label class="text-xs font-semibold uppercase text-neutral-500">Produto</label>
                    <select name="produto_id" class="rounded-lg border-neutral-300 text-sm focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900">
                        <option value="">Selecione um produto</option>
                        @foreach($produtos as $prod)
                            <option value="{{ $prod->id }}" {{ request('produto_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->nome }} ({{ $prod->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Comparar</button>
                </div>
            </form>
        </div>

        @if(request()->filled('produto_id'))
            <div class="grid grid-cols-1 gap-6">
                <!-- Vencedor de Preço (Card de Destaque) -->
                @if($ranking->count() > 0)
                <div class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-800/30 dark:bg-green-900/10">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30">
                            <x-heroicon-o-trophy class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-green-800 dark:text-green-300 uppercase tracking-wider">Melhor Opção (Média de Preços)</h3>
                            <p class="text-2xl font-black text-green-900 dark:text-green-100">
                                {{ $ranking->first()->nome_fantasia }} 
                                <span class="ml-2 text-lg font-normal opacity-70">R$ {{ number_format($ranking->first()->preco_medio, 2, ',', '.') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Gráfico Comparativo --}}
                @if($ranking->count() > 0)
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                    <h3 class="text-sm font-bold text-neutral-800 dark:text-white uppercase mb-4">Análise Visual de Preços</h3>
                    <div class="h-64">
                        <canvas id="chartComparativo"></canvas>
                    </div>
                </div>
                @endif

                <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                        <h2 class="font-bold text-neutral-800 dark:text-white uppercase text-xs tracking-widest">Ranking Completo</h2>
                    </div>
                    <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                        <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                            <tr>
                                <th class="px-6 py-3">Posição</th>
                                <th class="px-6 py-3">Fornecedor</th>
                                <th class="px-6 py-3 text-center">Frequência</th>
                                <th class="px-6 py-3 text-center">Menor Preço</th>
                                <th class="px-6 py-3 text-center">Último Preço</th>
                                <th class="px-6 py-3 text-right">Média</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse($ranking as $index => $item)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors">
                                    <td class="px-6 py-4">
                                        @if($index == 0)
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-400 text-white font-black shadow-sm">1</span>
                                        @elseif($index == 1)
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-300 text-white font-black">2</span>
                                        @elseif($index == 2)
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-600/60 text-white font-black">3</span>
                                        @else
                                            <span class="px-3 font-bold text-neutral-400">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                        {{ $item->nome_fantasia }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 px-2 py-1 rounded-md text-xs font-bold">
                                            {{ $item->total_compras }}x
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-green-600 dark:text-green-400 font-medium">
                                        R$ {{ number_format($item->preco_min, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="{{ $item->ultimo_preco <= $item->preco_medio ? 'text-green-600' : 'text-red-500' }}">
                                            R$ {{ number_format($item->ultimo_preco, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-neutral-900 dark:text-white bg-neutral-50/50 dark:bg-neutral-900/10">
                                        R$ {{ number_format($item->preco_medio, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center">Nenhum histórico de preços encontrado para este produto em pedidos de compra.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('chartComparativo');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($ranking->pluck('nome_fantasia')) !!},
                        datasets: [
                            {
                                label: 'Preço Médio (R$)',
                                data: {!! json_encode($ranking->pluck('preco_medio')) !!},
                                backgroundColor: 'rgba(79, 70, 229, 0.6)',
                                borderColor: 'rgb(79, 70, 229)',
                                borderWidth: 1
                            },
                            {
                                label: 'Último Preço (R$)',
                                data: {!! json_encode($ranking->pluck('ultimo_preco')) !!},
                                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
</x-layouts.app>
