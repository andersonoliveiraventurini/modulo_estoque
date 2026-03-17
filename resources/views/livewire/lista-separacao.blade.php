<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Separação de Orçamentos
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar..." />
            </div>

            <!-- Itens por página -->
            <div class="flex flex-col w-28">
                <label for="perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Itens por página:
                </label>
                <x-select id="perPage" wire:model.live="perPage">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-select>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex items-end gap-4"
        style="padding-bottom: 1rem; padding-top: 0.5rem; padding-left: 1.5rem; padding-right: 1.5rem;">
        <div class="flex items-end gap-4 flex-wrap">

            <!-- Cliente -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="cliente" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Cliente
                </label>
                <x-input id="cliente" wire:model.live.debounce.300ms="cliente" placeholder="Nome do cliente..." />
            </div>

            <!-- Vendedor -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="vendedor" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Vendedor
                </label>
                <x-input id="vendedor" wire:model.live.debounce.300ms="vendedor" placeholder="Nome do vendedor..." />
            </div>

            <!-- Roteiro -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="roteiro" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Rota/Roteiro
                </label>
                <x-input id="roteiro" wire:model.live.debounce.300ms="roteiro" placeholder="Ex: Rota Sul, Zona Norte..." />
            </div>

            <!-- Status Workflow -->
            <div class="flex flex-col w-48">
                <label for="workflowStatus" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Status
                </label>
                <x-select id="workflowStatus" wire:model.live="workflowStatus">
                    <option value="">Todos</option>
                    <option value="aguardando_separacao">Aguardando Separação</option>
                    <option value="em_separacao">Em Separação</option>
                </x-select>
            </div>

            <!-- Data início -->
            <div class="flex flex-col w-40">
                <label for="dataInicio" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Data início
                </label>
                <x-input type="date" id="dataInicio" wire:model.live="dataInicio" />
            </div>

            <!-- Data fim -->
            <div class="flex flex-col w-40">
                <label for="dataFim" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Data fim
                </label>
                <x-input type="date" id="dataFim" wire:model.live="dataFim" />
            </div>

            <!-- Botão de limpar filtros -->
            <div class="flex flex-col">
                <label class="text-sm text-transparent select-none mb-1">.</label>
                <x-button wire:click="limparFiltros" variant="secondary">
                    <x-heroicon-o-x-mark class="w-2 h-2" />
                    Limpar filtros
                </x-button>
            </div>
        </div>
    </div>

    <!-- Mensagem de sucesso -->
    @if (session()->has('message'))
        <div
            class="mx-6 mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('obra')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Obra / Rota
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('cliente_id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cliente
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('workflow_status')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('vendedor_id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Vendedor
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">Docs</th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($orcamentos as $o)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <a href="{{ route('orcamentos.show', $o) }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline">
                                    {{ $o->obra }}
                                </a>
                                <div class="flex items-center gap-1 text-[10px] text-zinc-400 font-medium">
                                    <x-heroicon-o-truck class="w-3 h-3" />
                                    <span>{{ $o->endereco->roteiro ?? 'Sem rota' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate max-w-[200px] inline-block">
                                {{ $o->cliente->nome }}
                            </span>
                        </td>
                        <td class="px-3 py-4">
                            @if ($o->workflow_status === 'aguardando_separacao')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase whitespace-nowrap bg-amber-100 text-amber-700">
                                    Aguardando
                                </span>
                            @elseif($o->workflow_status === 'em_separacao')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase whitespace-nowrap bg-blue-100 text-blue-700">
                                    Em Separação
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $o->vendedor->name }}
                        </td>
                        <td class="px-3 py-4">
                            @if ($o->pdf_path)
                                <a href="{{ asset('storage/' . $o->pdf_path) }}" target="_blank" title="Ver PDF" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                </a>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('orcamentos.show', $o->id) }}" title="Ver detalhes" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>

                                @php
                                    $isAguardando = $o->workflow_status === 'aguardando_separacao';
                                @endphp
                                <a href="{{ route('orcamentos.separacao.show', $o->id) }}" 
                                   title="{{ $isAguardando ? 'Iniciar Separação' : 'Acompanhar Separação' }}" 
                                   class="p-1.5 {{ $isAguardando ? 'text-emerald-500 hover:bg-emerald-50' : 'text-blue-500 hover:bg-blue-50' }} rounded-lg transition">
                                    <x-heroicon-o-play class="w-5 h-5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhum orçamento encontrado para separação.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($orcamentos->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $orcamentos->links() }}
            </div>
        </div>
    @endif
</div>
