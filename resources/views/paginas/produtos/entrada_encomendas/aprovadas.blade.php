<x-layouts.app :title="__('Encomendas Aprovadas — Painel de Compras')">
    <div class="flex flex-col gap-6">

        {{-- ── CABEÇALHO ────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Encomendas Aprovadas</h1>
                <p class="text-sm text-zinc-500 mt-1">Itens que precisam ser comprados e aguardados.</p>
            </div>
            <a href="{{ route('entrada_encomendas.create') }}">
                <x-button variant="primary">
                    <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                    Registrar Entrada
                </x-button>
            </a>
        </div>

        {{-- ── LISTA DE GRUPOS ──────────────────────────────── --}}
        @forelse ($grupos as $grupo)
            @php
                // Monta mapa de quantidade já recebida por consulta_preco_id
                $recebidoMap = [];
                foreach ($grupo->entradas as $entrada) {
                    foreach ($entrada->itens as $ei) {
                        $id = $ei->consulta_preco_id;
                        $recebidoMap[$id] = ($recebidoMap[$id] ?? 0) + $ei->quantidade_recebida;
                    }
                }
            @endphp

            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow overflow-hidden">

                {{-- Cabeçalho do grupo --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div>
                        <span class="text-xs text-zinc-400 uppercase tracking-wider">Cotação</span>
                        <h2 class="text-base font-bold text-zinc-800 dark:text-white">#{{ $grupo->id }}</h2>
                        <p class="text-sm text-zinc-500">
                            Cliente: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome ?? '—' }}</span>
                            &nbsp;·&nbsp;
                            Vendedor: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $grupo->usuario->name }}</span>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}">
                            <x-button size="sm" variant="secondary">
                                <x-heroicon-o-eye class="w-4 h-4" /> Ver Cotação
                            </x-button>
                        </a>
                        <a href="{{ route('entrada_encomendas.create', ['grupo_id' => $grupo->id]) }}">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-inbox-arrow-down class="w-4 h-4" /> Dar Entrada
                            </x-button>
                        </a>
                    </div>
                </div>

                {{-- Tabela de itens --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                            <th class="px-4 py-2 text-left">Item</th>
                            <th class="px-4 py-2 text-left">Part Number</th>
                            <th class="px-4 py-2 text-left">Fornecedor</th>
                            <th class="px-4 py-2 text-left">Comprador</th>
                            <th class="px-4 py-2 text-center">Prazo</th>
                            <th class="px-4 py-2 text-right">Qtd Pedida</th>
                            <th class="px-4 py-2 text-right">Qtd Recebida</th>
                            <th class="px-4 py-2 text-right">Preço Compra</th>
                            <th class="px-4 py-2 text-center">Situação</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($grupo->itens as $item)
                            @php
                                $forn          = $item->fornecedorSelecionado;
                                $qtdRecebida   = $recebidoMap[$item->id] ?? 0;
                                $qtdPendente   = max(0, (float)$item->quantidade - $qtdRecebida);
                                $completo      = $qtdPendente <= 0;
                            @endphp
                            <tr class="{{ $completo ? 'opacity-50' : '' }} hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition">
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $item->descricao }}
                                    @if ($item->cor)
                                        <span class="text-xs text-zinc-400">· {{ $item->cor->nome }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-500">{{ $item->part_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                    {{ $forn?->fornecedor?->nome_fantasia ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-500 text-xs">
                                    {{ $forn?->comprador?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center text-zinc-500 text-xs">
                                    {{ $forn?->prazo_entrega ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($item->quantidade, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($qtdRecebida, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-300">
                                    {{ $forn?->preco_compra ? 'R$ ' . number_format($forn->preco_compra, 2, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($completo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            <x-heroicon-o-check-circle class="w-3 h-3" /> Recebido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            <x-heroicon-o-clock class="w-3 h-3" /> Aguardando ({{ number_format($qtdPendente, 0, ',', '.') }})
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Entradas já registradas --}}
                @if ($grupo->entradas->isNotEmpty())
                    <div class="px-6 py-3 border-t border-zinc-100 dark:border-zinc-700">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Entradas registradas</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($grupo->entradas as $entrada)
                                <a href="{{ route('entrada_encomendas.show', $entrada->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border
                                   {{ $entrada->status === 'Entregue' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' :
                                      ($entrada->status === 'Recebido completo' ? 'bg-blue-50 border-blue-200 text-blue-700' :
                                      'bg-amber-50 border-amber-200 text-amber-700') }}">
                                    <x-heroicon-o-inbox-arrow-down class="w-3 h-3" />
                                    Entrada #{{ $entrada->id }} — {{ $entrada->data_recebimento->format('d/m/Y') }}
                                    <span class="ml-1 opacity-70">{{ $entrada->status }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-12 text-center text-zinc-400">
                Nenhuma encomenda aprovada aguardando recebimento.
            </div>
        @endforelse

        {{-- Paginação --}}
        @if ($grupos->hasPages())
            <div class="mt-2">{{ $grupos->links() }}</div>
        @endif
    </div>
</x-layouts.app>