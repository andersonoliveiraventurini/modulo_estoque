<x-layouts.app title="Fluxo de Caixa">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white uppercase tracking-tight">Fluxo de Caixa</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Comparativo entre Previsto e Realizado (Entradas e Saídas).</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.fluxo_caixa') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <flux:label>Data Início</flux:label>
                    <flux:input type="date" name="data_inicio" value="{{ request('data_inicio', now()->startOfMonth()->format('Y-m-d')) }}" />
                </div>
                <div>
                    <flux:label>Data Fim</flux:label>
                    <flux:input type="date" name="data_fim" value="{{ request('data_fim', now()->endOfMonth()->format('Y-m-d')) }}" />
                </div>
                <div>
                    <flux:button type="submit" variant="filled" color="indigo" class="w-full">Filtrar</flux:button>
                </div>
            </form>
        </div>

        {{-- Cards de Resumo --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-800/30 dark:bg-green-900/10">
                <h3 class="text-xs font-bold text-green-800 dark:text-green-300 uppercase">Entradas Realizadas</h3>
                <p class="text-2xl font-black text-green-900 dark:text-green-100">R$ {{ number_format($dados['resumo']['total_entradas_realizadas'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-6 dark:border-neutral-700 dark:bg-neutral-800/50">
                <h3 class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase">Entradas Previstas</h3>
                <p class="text-2xl font-black text-neutral-800 dark:text-white">R$ {{ number_format($dados['resumo']['total_entradas_previstas'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-800/30 dark:bg-red-900/10">
                <h3 class="text-xs font-bold text-red-800 dark:text-red-300 uppercase">Saídas Realizadas</h3>
                <p class="text-2xl font-black text-red-900 dark:text-red-100">R$ {{ number_format($dados['resumo']['total_saidas_realizadas'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-6 dark:border-neutral-700 dark:bg-neutral-800/50">
                <h3 class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase">Saídas Previstas</h3>
                <p class="text-2xl font-black text-neutral-800 dark:text-white">R$ {{ number_format($dados['resumo']['total_saidas_previstas'], 2, ',', '.') }}</p>
            </div>
        </div>

        {{-- Gráfico --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <h2 class="font-bold text-neutral-800 dark:text-white uppercase text-sm mb-6">Análise de Tendência</h2>
            <div class="h-80">
                <canvas id="chartFluxoCaixa"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('chartFluxoCaixa');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($dados['detalhes']['entradas_previstas']->pluck('data')->merge($dados['detalhes']['saidas_previstas']->pluck('data'))->unique()->sort()->values()) !!},
                        datasets: [
                            {
                                label: 'Entradas (Prev)',
                                data: {!! json_encode($dados['detalhes']['entradas_previstas']->pluck('valor')) !!},
                                borderColor: 'rgb(79, 70, 229)',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Saídas (Prev)',
                                data: {!! json_encode($dados['detalhes']['saidas_previstas']->pluck('valor')) !!},
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR');
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
