<x-layouts.app>
    <x-slot:title>Relatório de Separação por Roteiro</x-slot:title>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

        {{-- Cabeçalho --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Separação por Roteiro</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Lotes de separação concluídos, organizados por roteiro de entrega</p>
            </div>
            <a href="{{ route('relatorios.separacao_roteiro_export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700
                      text-white text-sm font-semibold shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M8 12l4 4m0 0l4-4m-4 4V4"/>
                </svg>
                Exportar CSV
            </a>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('relatorios.separacao_por_roteiro') }}"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Roteiro</label>
                    <input type="text" name="roteiro" value="{{ request('roteiro') }}"
                           placeholder="Ex: NORTE, SUL, CENTRO…"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800
                                  text-sm text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Data início</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800
                                  text-sm text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Data fim</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800
                                  text-sm text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors">
                        Filtrar
                    </button>
                    <a href="{{ route('relatorios.separacao_por_roteiro') }}"
                       class="px-4 py-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600
                              text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors">
                        Limpar
                    </a>
                </div>
            </div>
        </form>

        {{-- Sumário por Roteiro --}}
        @if ($roteiros->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 self-center">Roteiros no período:</span>
                @foreach ($roteiros as $r)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold
                                 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
                        {{ $r->roteiro ?? '(sem roteiro)' }}
                        <span class="ml-1 bg-indigo-200 dark:bg-indigo-800 px-1.5 rounded-full">{{ $r->total_lotes }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Tabela --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Roteiro</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lote #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Orçamento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Endereço</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendedor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Concluído em</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Volumes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($batches as $batch)
                        @php
                            $orcamento = $batch->orcamento;
                            $cliente   = $orcamento?->cliente;
                            $endereco  = $cliente?->enderecos?->first();
                            $roteiro   = $endereco?->roteiro ?? '—';
                            $volumes   = collect([
                                $batch->qtd_caixas   ? $batch->qtd_caixas . ' cx'   : null,
                                $batch->qtd_sacos    ? $batch->qtd_sacos  . ' sc'   : null,
                                $batch->qtd_sacolas  ? $batch->qtd_sacolas . ' sa'  : null,
                                $batch->outros_embalagem ?: null,
                            ])->filter()->implode(' · ');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                             bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
                                    {{ $roteiro }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-mono">#{{ $batch->id }}</td>
                            <td class="px-4 py-3">
                                @if ($orcamento)
                                    <a href="{{ route('orcamentos.show', $orcamento->id) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                        Orç. #{{ $orcamento->id }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $cliente?->nome ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                @if ($endereco)
                                    {{ $endereco->logradouro }}, {{ $endereco->numero }}
                                    <span class="text-gray-400">— {{ $endereco->bairro }}, {{ $endereco->cidade }}/{{ $endereco->uf }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $orcamento?->vendedor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $batch->finished_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                {{ $volumes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                Nenhuma separação concluída encontrada com os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($batches->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $batches->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
