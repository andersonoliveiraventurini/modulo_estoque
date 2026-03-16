<div>
    {{-- ══════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">

                {{-- Título + Abas --}}
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Encomendas</h1>
                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">Painel</span>
                    </div>

                    {{-- Abas --}}
                    <div class="flex items-center gap-1 border border-zinc-200 dark:border-zinc-700 rounded-lg p-1 bg-zinc-50 dark:bg-zinc-900">
                        <button wire:click="$set('aba', 'kanban')"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition
                                {{ $aba === 'kanban' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                Kanban
                            </span>
                        </button>
                        <button wire:click="$set('aba', 'lista')"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition
                                {{ $aba === 'lista' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Lista de Itens
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Filtros do kanban --}}
                @if ($aba === 'kanban')
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Buscar ID ou cliente..."
                                class="w-56 px-4 py-1.5 pl-8 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">
                            <svg class="absolute left-2.5 top-2 h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="clienteFilter"
                            placeholder="Cliente..."
                            class="w-36 px-3 py-1.5 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">
                        <input type="text" wire:model.live.debounce.300ms="vendedorFilter"
                            placeholder="Vendedor..."
                            class="w-36 px-3 py-1.5 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">
                        @if ($search || $clienteFilter || $vendedorFilter)
                            <button wire:click="limparFiltros" title="Limpar filtros"
                                class="p-1.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         ABA KANBAN
    ══════════════════════════════════════════════════════ --}}
    @if ($aba === 'kanban')
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="overflow-x-auto -mx-4 px-4">
                <div class="flex gap-4 min-w-max pb-4">

                    @foreach ($columns as $column)
                        @php
                            $dotColor = match($column['color']) {
                                'orange'  => 'bg-orange-500',
                                'amber'   => 'bg-amber-500',
                                'blue'    => 'bg-blue-500',
                                'yellow'  => 'bg-yellow-500',
                                'purple'  => 'bg-purple-500',
                                'green'   => 'bg-green-500',
                                'emerald' => 'bg-emerald-500',
                                default   => 'bg-zinc-500',
                            };
                            $borderTop = match($column['color']) {
                                'orange'  => 'border-t-2 border-t-orange-500',
                                'amber'   => 'border-t-2 border-t-amber-500',
                                'blue'    => 'border-t-2 border-t-blue-500',
                                'yellow'  => 'border-t-2 border-t-yellow-500',
                                'purple'  => 'border-t-2 border-t-purple-500',
                                'green'   => 'border-t-2 border-t-green-500',
                                'emerald' => 'border-t-2 border-t-emerald-500',
                                default   => 'border-t-2 border-t-zinc-400',
                            };
                        @endphp

                        <div class="flex-shrink-0 w-80">
                            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 h-full flex flex-col {{ $borderTop }}">

                                {{-- Column Header --}}
                                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full {{ $dotColor }}"></div>
                                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $column['title'] }}</h3>
                                        </div>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                            {{ $column['count'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-zinc-400 mb-1">{{ $column['description'] }}</p>
                                    @if ($column['valor_total'] > 0)
                                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            R$ {{ number_format($column['valor_total'], 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Cards --}}
                                <div class="flex-1 flex flex-col gap-3 p-3 min-h-[400px] overflow-y-auto" wire:key="column-{{ $column['id'] }}">

                                    @forelse ($column['grupos'] as $grupo)
                                        @php
                                            $totalItens    = $grupo->itens->count();
                                            $totalRecebido = 0;
                                            foreach ($grupo->entradas as $entrada) {
                                                foreach ($entrada->itens as $ei) {
                                                    if ($ei->recebido_completo) $totalRecebido++;
                                                }
                                            }
                                            $ultimaEntrada = $grupo->entradas->sortByDesc('created_at')->first();
                                        @endphp

                                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition group"
                                            wire:key="grupo-{{ $grupo->id }}">

                                            <div class="p-3 border-b border-zinc-100 dark:border-zinc-700">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-1.5 mb-1">
                                                            <span class="text-xs font-mono font-bold text-zinc-900 dark:text-white">#{{ $grupo->id }}</span>
                                                            <span class="text-xs px-1.5 py-0.5 rounded {{ $grupo->status === 'Aprovado' ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                                                {{ $grupo->status ?? 'Novo' }}
                                                            </span>
                                                            @if ($grupo->orcamento && $grupo->orcamento->workflow_status)
                                                                @php
                                                                    $wfStatus = $grupo->orcamento->workflow_status;
                                                                    $wfLabel = match($wfStatus) {
                                                                        'aguardando_separacao' => 'Ag. Separação',
                                                                        'em_separacao' => 'Em Separação',
                                                                        'separado' => 'Separado',
                                                                        'aguardando_conferencia' => 'Ag. Conferência',
                                                                        'em_conferencia' => 'Em Conferência',
                                                                        'conferido' => 'Conferido',
                                                                        'aguardando_pagamento' => 'Ag. Pagamento',
                                                                        'aguardando_faturamento' => 'Ag. Faturamento',
                                                                        'finalizado' => 'Finalizado',
                                                                        default => ucfirst(str_replace('_', ' ', $wfStatus))
                                                                    };
                                                                    $wfColor = match($wfStatus) {
                                                                        'aguardando_separacao', 'aguardando_conferencia', 'aguardando_pagamento', 'aguardando_faturamento' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800',
                                                                        'em_separacao', 'em_conferencia' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                                                        'separado', 'conferido', 'finalizado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'
                                                                    };
                                                                @endphp
                                                                <span class="text-[10px] uppercase tracking-wider px-1.5 py-0.5 rounded font-medium {{ $wfColor }}" title="Status de O.S (Workflow)">
                                                                    {{ $wfLabel }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate">
                                                            {{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome ?? 'Cliente não informado' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3 space-y-2">
                                                @if ($grupo->usuario)
                                                    <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        <span class="truncate">{{ $grupo->usuario->name }}</span>
                                                    </div>
                                                @endif

                                                <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                                    <span>{{ $totalItens }} {{ $totalItens === 1 ? 'item' : 'itens' }}</span>
                                                </div>

                                                @if ($grupo->entradas->isNotEmpty())
                                                    <div>
                                                        <div class="flex justify-between text-xs text-zinc-400 mb-1">
                                                            <span>Recebimento</span>
                                                            <span>{{ $totalRecebido }}/{{ $totalItens }}</span>
                                                        </div>
                                                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                                                            <div class="h-1.5 rounded-full {{ $totalRecebido >= $totalItens ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                                                style="width: {{ $totalItens > 0 ? round(($totalRecebido / $totalItens) * 100) : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($ultimaEntrada)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs
                                                        {{ $ultimaEntrada->status === 'Entregue' ? 'bg-emerald-100 text-emerald-700' : ($ultimaEntrada->status === 'Recebido completo' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                                        {{ $ultimaEntrada->status }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="px-3 py-2 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-100 dark:border-zinc-700 rounded-b-lg flex items-center justify-between">
                                                <span class="text-xs text-zinc-400">{{ $grupo->created_at->format('d/m/Y') }}</span>
                                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}" class="p-1 text-zinc-400 hover:text-blue-600" title="Ver cotação">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                    </a>
                                                    @if ($grupo->status === 'Aprovado')
                                                        <a href="{{ route('entrada_encomendas.create', ['grupo_id' => $grupo->id]) }}" class="p-1 text-zinc-400 hover:text-emerald-600" title="Registrar entrada">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                                            <svg class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                            <p class="text-xs text-zinc-400">Nenhuma encomenda</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         ABA LISTA DE ITENS
    ══════════════════════════════════════════════════════ --}}
    @if ($aba === 'lista')
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

            {{-- Perfil de filtro 
            <div class="flex items-center gap-3 mb-5">
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Visualização:</span>
                <div class="flex items-center gap-1 border border-zinc-200 dark:border-zinc-700 rounded-lg p-1 bg-zinc-50 dark:bg-zinc-900">
                    <button wire:click="$set('perfilFiltro', 'compras')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition
                            {{ $perfilFiltro === 'compras' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                        🛒 Compras
                    </button>
                    <button wire:click="$set('perfilFiltro', 'vendas')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition
                            {{ $perfilFiltro === 'vendas' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                        💼 Vendas
                    </button>
                </div>
            </div>--}}

            {{-- Filtros --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 mb-5">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Descrição</label>
                        <input type="text" wire:model.live.debounce.300ms="descricaoFiltro"
                            placeholder="Ex: perfil..."
                            class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Fornecedor</label>
                        <input type="text" wire:model.live.debounce.300ms="fornecedorFiltro"
                            placeholder="Nome do fornecedor..."
                            class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Vendedor</label>
                        <input type="text" wire:model.live.debounce.300ms="vendedorLista"
                            placeholder="Nome do vendedor..."
                            class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Cor</label>
                        <input type="text" wire:model.live.debounce.300ms="corFiltro"
                            placeholder="Ex: preto..."
                            class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Data início</label>
                        <input type="date" wire:model.live="dataInicio"
                            class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">Data fim</label>
                        <div class="flex gap-1">
                            <input type="date" wire:model.live="dataFim"
                                class="w-full px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            @if ($descricaoFiltro || $fornecedorFiltro || $vendedorLista || $corFiltro || $dataInicio || $dataFim)
                                <button wire:click="limparFiltrosLista" title="Limpar"
                                    class="px-2 text-zinc-400 hover:text-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                            <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                                <th class="px-4 py-3 text-left">Nº Pedido</th>
                                <th class="px-4 py-3 text-left">Descrição</th>
                                <th class="px-4 py-3 text-left">Cor</th>
                                <th class="px-4 py-3 text-right">Quantidade</th>
                                <th class="px-4 py-3 text-left">Fornecedor</th>
                                <th class="px-4 py-3 text-left">Vendedor</th>
                                <th class="px-4 py-3 text-left">Data Encomenda</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($itensLista as $item)
                                @php
                                    $forn   = $item->fornecedorSelecionado?->fornecedor;
                                    $status = $item->grupo?->status ?? '—';
                                    $statusClass = match($status) {
                                        'Aprovado'           => 'bg-emerald-100 text-emerald-700',
                                        'Aguardando preços'  => 'bg-blue-100 text-blue-700',
                                        'Preços preenchidos' => 'bg-purple-100 text-purple-700',
                                        default              => 'bg-zinc-100 text-zinc-600',
                                    };
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('consulta_preco.show_grupo', $item->grupo_id) }}"
                                           class="font-mono font-semibold text-blue-600 hover:underline">
                                            #{{ $item->grupo_id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $item->descricao }}
                                        @if ($item->part_number)
                                            <span class="block text-xs text-zinc-400">PN: {{ $item->part_number }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $item->cor?->nome ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-zinc-700 dark:text-zinc-300">
                                        {{ number_format($item->quantidade, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $forn?->nome_fantasia ?? $forn?->nome ?? '—' }}
                                        @if ($item->fornecedorSelecionado?->preco_compra)
                                            <span class="block text-xs text-zinc-400">
                                                R$ {{ number_format($item->fornecedorSelecionado->preco_compra, 2, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $item->grupo?->usuario?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500 text-xs">
                                        {{ $item->grupo?->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('consulta_preco.show_grupo', $item->grupo_id) }}"
                                           class="inline-flex items-center gap-1 px-2 py-1 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition"
                                           title="Ver cotação">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-zinc-400">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Nenhum item encontrado com os filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                @if ($itensLista && $itensLista->hasPages())
                    <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-700">
                        {{ $itensLista->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>