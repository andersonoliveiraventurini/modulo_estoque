<x-layouts.app>
    <x-slot:title>Fila de Carga (Entregas)</x-slot:title>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">

        {{-- Cabeçalho --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Fila de Carga (Entregas)</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos concluídos aguardando atribuição a um Romaneio</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('romaneios.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700
                          text-white text-sm font-semibold shadow-sm transition-colors">
                    Ver Romaneios
                </a>
                <a href="{{ route('romaneios.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-600 hover:bg-zinc-700
                          text-white text-sm font-semibold shadow-sm transition-colors">
                    Novo Romaneio
                </a>
            </div>
        </div>

        {{-- Alerta Informativo --}}
        <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Esta lista exibe pedidos prontos que ainda não foram escalados. 
                        Crie um <strong>Romaneio</strong> para agrupar estes pedidos para o motorista.
                    </p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('relatorios.separacao_por_roteiro') }}"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Localidade (Cidade/Bairro)</label>
                    <input type="text" name="roteiro" value="{{ request('roteiro') }}"
                           placeholder="Ex: Vinhedo, Centro..."
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

        {{-- Conteúdo Agrupado --}}
        <div class="space-y-8">
            @forelse ($groupedBatches as $roteiroNome => $lotes)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    {{-- Cabeçalho da Rota --}}
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600">
                                <x-heroicon-o-truck class="w-6 h-6" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 uppercase tracking-tight">
                                    {{ $roteiroNome }}
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ count($lotes) }} {{ count($lotes) === 1 ? 'entrega' : 'entregas' }} nesta localidade
                                </p>
                            </div>
                        </div>
                        @if($roteiroNome === 'SEM ENDEREÇO')
                            <span class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded-full font-medium">Endereço Ausente</span>
                        @endif
                    </div>

                    {{-- Lista de Entregas --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($lotes as $batch)
                            @php
                                $orcamento = $batch->orcamento;
                                $cliente   = $orcamento?->cliente;
                                $endereco  = $cliente?->enderecos->where('tipo', 'entrega')->first() 
                                          ?? $cliente?->enderecos->where('tipo', 'comercial')->first();
                                $volumes   = collect([
                                    $batch->qtd_caixas   ? $batch->qtd_caixas . ' cx'   : null,
                                    $batch->qtd_sacos    ? $batch->qtd_sacos  . ' sc'   : null,
                                    $batch->qtd_sacolas  ? $batch->qtd_sacolas . ' sa'  : null,
                                    $batch->outros_embalagem ?: null,
                                ])->filter()->implode(' · ');
                            @endphp
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all flex flex-col md:flex-row gap-6">
                                {{-- Info Lote e Cliente --}}
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono text-gray-400">LOTE #{{ $batch->id }}</span>
                                        <span class="text-gray-300">|</span>
                                        @if ($orcamento)
                                            <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="text-xs text-indigo-600 hover:underline font-medium">
                                                Orç. #{{ $orcamento->id }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $cliente?->nome ?? '—' }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-1 mt-1">
                                            <x-heroicon-o-map-pin class="w-4 h-4 flex-shrink-0 mt-0.5 text-gray-400" />
                                            @if ($endereco)
                                                {{ $endereco->logradouro }}, {{ $endereco->numero }} — {{ $endereco->bairro }}
                                                <br>
                                                {{ $endereco->cidade }}/{{ $endereco->estado }}
                                            @else
                                                <span class="text-red-400 italic">Endereço não localizado</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Info Itens e Volumes --}}
                                <div class="w-full md:w-64 space-y-3">
                                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Conteúdo Separado</div>
                                        <div class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2">
                                            {{ $batch->items->map(fn($i) => $i->quantidade_separada . 'x ' . ($i->produto?->nome ?? 'item'))->implode(', ') }}
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center px-1">
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-o-cube class="w-4 h-4 text-emerald-500" />
                                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $volumes ?: '—' }}</span>
                                        </div>
                                        <div class="text-right">
                                        </div>
                                    </div>
                                </div>

                                {{-- Datas e Vendedor --}}
                                <div class="w-full md:w-48 text-left md:text-right flex flex-col justify-center border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-800 pt-4 md:pt-0 md:pl-6">
                                    <div class="text-[10px] text-gray-400 uppercase">Vendedor</div>
                                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $orcamento?->vendedor?->name ?? '—' }}</div>
                                    
                                    <div class="text-[10px] text-gray-400 uppercase">Concluído em</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $batch->finished_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
                    <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 mb-4">
                        <x-heroicon-o-document-magnifying-glass class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Nenhuma separação concluída</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Experimente ajustar os filtros de data ou roteiro.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
