<div>
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                {{-- Logo/Brand --}}
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        Status dos Pedidos
                    </h1>
                    <span
                        class="ml-3 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                        Painel
                    </span>
                </div>

                {{-- Search & Filters --}}
                <div class="flex items-center gap-3">
                    {{-- Search --}}
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por ID, obra, cliente..."
                            class="w-72 px-4 py-2 pl-10 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">
                        {{-- <svg class="absolute left-3 top-2.5 h-5 w-5 text-zinc-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg> --}}
                    </div>

                    {{-- Filter Cliente --}}
                    <input type="text" wire:model.live.debounce.300ms="clienteFilter"
                        placeholder="Filtrar por cliente..."
                        class="w-48 px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">

                    {{-- Filter Vendedor --}}
                    <input type="text" wire:model.live.debounce.300ms="vendedorFilter"
                        placeholder="Filtrar por vendedor..."
                        class="w-48 px-3 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white">

                    {{-- Clear Filters --}}
                    @if ($search || $clienteFilter || $vendedorFilter)
                        <button wire:click="limparFiltros"
                            class="px-3 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"
                            title="Limpar filtros">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif

                    {{-- Actions
                    <button
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Novo Orçamento
                    </button> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Kanban Board --}}
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="overflow-x-auto -mx-4 px-4">
            <div class="flex gap-4 min-w-max pb-4">
                @foreach ($columns as $column)
                    <div class="flex-shrink-0 w-96">
                        <div
                            class="rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 h-full flex flex-col">
                            {{-- Column Header --}}
                            <div
                                class="px-4 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-3 h-3 rounded-full {{ $column['color'] === 'zinc' ? 'bg-zinc-500' : ($column['color'] === 'blue' ? 'bg-blue-500' : ($column['color'] === 'yellow' ? 'bg-yellow-500' : ($column['color'] === 'purple' ? 'bg-purple-500' : ($column['color'] === 'green' ? 'bg-green-500' : 'bg-emerald-500')))) }}">
                                        </div>
                                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                            {{ $column['title'] }}
                                        </h3>
                                    </div>
                                    <button class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                        </svg>
                                    </button>
                                </div>

                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                                    {{ $column['description'] }}
                                </p>

                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ $column['count'] }} {{ $column['count'] === 1 ? 'orçamento' : 'orçamentos' }}
                                    </span>
                                    @if ($column['valor_total'] > 0)
                                        <span class="text-xs font-semibold text-green-600 dark:text-green-400">
                                            R$ {{ number_format($column['valor_total'], 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Cards Container --}}
                            <div class="flex-1 flex flex-col gap-3 p-3 min-h-[400px] overflow-y-auto"
                                data-workflow-status="{{ $column['workflow_status'] }}"
                                wire:key="column-{{ $column['id'] }}">
                                @forelse ($column['orcamentos'] as $orcamento)
                                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition cursor-move group"
                                        wire:key="orcamento-{{ $orcamento->id }}" {{-- draggable="true" --}}
                                        data-orcamento-id="{{ $orcamento->id }}">

                                        {{-- Card Header --}}
                                        <div class="p-3 border-b border-zinc-100 dark:border-zinc-700">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1">
                                                    <a href="/orcamentos/{{ $orcamento->id }}" class="hover:underline">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span
                                                                class="text-xs font-mono font-semibold text-zinc-900 dark:text-white">
                                                                #{{ $orcamento->id }}

                                                                @if ($orcamento->versao > 1)
                                                                    <span
                                                                        class="text-xs
                                                                    px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-700
                                                                    text-zinc-600 dark:text-zinc-400 rounded">
                                                                        v{{ $orcamento->versao }}
                                                                    </span>
                                                                @endif
                                                                @if ($orcamento->complemento === 'Sim')
                                                                    <span
                                                                        class="text-xs px-1.5 py-0.5 bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 rounded">
                                                                        Complemento
                                                                    </span>
                                                                @endif
                                                        </div>
                                                        <h4
                                                            class="font-semibold text-sm text-zinc-900 dark:text-white line-clamp-2">
                                                            {{ $orcamento->obra ?: 'Sem título de obra' }}
                                                        </h4>
                                                    </a>
                                                </div>

                                                {{-- Status Badge 
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full whitespace-nowrap
                                                    {{ $orcamento->status === 'Aprovado' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : '' }}
                                                    {{ $orcamento->status === 'Pendente' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                                    {{ $orcamento->status === 'Aprovar desconto' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300' : '' }}
                                                    {{ $orcamento->status === 'Finalizado' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : '' }}
                                                    {{ $orcamento->status === 'Cancelado' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : '' }}
                                                    {{ $orcamento->status === 'Rejeitado' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : '' }}
                                                    {{ $orcamento->status === 'Expirado' ? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                                                "> --}}
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full whitespace-nowrap
                                                     bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">{{ $orcamento->transportes[0]->nome ?? '' }}</span>

                                            </div>
                                        </div>

                                        {{-- Card Body --}}
                                        <div class="p-3 space-y-2">
                                            {{-- Cliente --}}
                                            @if ($orcamento->cliente)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <svg class="w-4 h-4 text-zinc-400 flex-shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <span class="text-zinc-700 dark:text-zinc-300 truncate">
                                                        {{ $orcamento->cliente->nome }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Endereço/Cidade --}}
                                            @if ($orcamento->endereco)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <svg class="w-4 h-4 text-zinc-400 flex-shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <span class="text-zinc-700 dark:text-zinc-300 truncate">
                                                        {{ $orcamento->endereco->cidade }}{{ $orcamento->endereco->estado ? '/' . $orcamento->endereco->estado : '' }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Vendedor --}}
                                            @if ($orcamento->vendedor)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <svg class="w-4 h-4 text-zinc-400 flex-shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                    <span class="text-zinc-700 dark:text-zinc-300 truncate">
                                                        Vendedor: {{ $orcamento->vendedor->name }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Valor Total --}}
                                            @if ($orcamento->valor_total_itens)
                                                <div
                                                    class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-700">
                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        Valor Total itens:
                                                    </span>
                                                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                                        R$
                                                        {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Card Footer --}}
                                        <div
                                            class="px-3 py-2 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-100 dark:border-zinc-700 rounded-b-lg">
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-3 text-zinc-500 dark:text-zinc-400">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ $orcamento->created_at->format('d/m/Y') }}
                                                    </span>

                                                    @if ($orcamento->validade)
                                                        @php
                                                            $validade = \Carbon\Carbon::parse($orcamento->validade);
                                                            if (
                                                                $orcamento->status == 'Aprovado' ||
                                                                $orcamento->status == 'Finalizado'
                                                            ) {
                                                                $isExpired = false;
                                                            } else {
                                                                $isExpired = $validade->isPast();
                                                            }
                                                        @endphp
                                                        <span
                                                            class="flex items-center gap-1 {{ $isExpired ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-amber-600 dark:text-amber-400' }}">
                                                            <svg class="w-3.5 h-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Val: {{ $validade->format('d/m/Y') }}
                                                            @if ($isExpired)
                                                                <span class="text-[10px] uppercase">(Expirado)</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Quick Actions (visible on hover) --}}
                                                <div
                                                    class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    {{-- <button
                                                        class="p-1 text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400"
                                                        title="Ver detalhes">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        class="p-1 text-zinc-400 hover:text-green-600 dark:hover:text-green-400"
                                                        title="Editar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                                        <svg class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-3" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-sm text-zinc-400 dark:text-zinc-500">
                                            Nenhum orçamento
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Drag and Drop Script --}}
    @script
        <script>
            let draggedElement = null;
            let sourceColumn = null;

            // Drag start
            document.addEventListener('dragstart', (e) => {
                if (e.target.hasAttribute('draggable')) {
                    draggedElement = e.target;
                    sourceColumn = e.target.closest('[data-workflow-status]');
                    e.target.classList.add('opacity-50', 'scale-95');
                    e.dataTransfer.effectAllowed = 'move';
                }
            });

            // Drag end
            document.addEventListener('dragend', (e) => {
                if (e.target.hasAttribute('draggable')) {
                    e.target.classList.remove('opacity-50', 'scale-95');
                    draggedElement = null;
                    sourceColumn = null;

                    // Remove hover effects from all columns
                    document.querySelectorAll('[data-workflow-status]').forEach(col => {
                        col.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
                    });
                }
            });

            // Drag over
            document.addEventListener('dragover', (e) => {
                e.preventDefault();
                const column = e.target.closest('[data-workflow-status]');

                if (column && draggedElement) {
                    e.dataTransfer.dropEffect = 'move';

                    // Add visual feedback
                    document.querySelectorAll('[data-workflow-status]').forEach(col => {
                        col.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
                    });

                    if (column !== sourceColumn) {
                        column.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
                    }
                }
            });

            // Drop
            document.addEventListener('drop', (e) => {
                e.preventDefault();
                const targetColumn = e.target.closest('[data-workflow-status]');

                if (targetColumn && draggedElement && targetColumn !== sourceColumn) {
                    const orcamentoId = draggedElement.getAttribute('data-orcamento-id');
                    const newWorkflowStatus = targetColumn.getAttribute('data-workflow-status');

                    // Call Livewire method
                    $wire.updateWorkflowStatus(orcamentoId, newWorkflowStatus);
                }

                // Remove hover effects
                document.querySelectorAll('[data-workflow-status]').forEach(col => {
                    col.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
                });
            });

            // Listen for Livewire events
            Livewire.on('showNotification', (data) => {
                // You can integrate with your notification system here
                console.log(data[0].message);
            });
        </script>
    @endscript
</div>
