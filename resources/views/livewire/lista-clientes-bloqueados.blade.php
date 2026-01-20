<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Clientes Bloqueados
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar..." />
            </div>

            <!-- Itens por página (largura fixa) -->
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

    <!-- Filtros Específicos -->
    <div class="flex items-end gap-4"
        style="padding-bottom: 1rem; padding-top: 0.5rem; padding-left: 1.5rem; padding-right: 1.5rem;">
        <div class="flex items-end gap-4 flex-wrap">

            <!-- Nome Fantasia -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="nomeFantasia" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Nome Fantasia
                </label>
                <x-input id="nomeFantasia" wire:model.live.debounce.300ms="nomeFantasia" placeholder="Nome fantasia..." />
            </div>

            <!-- Razão Social -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="razaoSocial" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Razão Social
                </label>
                <x-input id="razaoSocial" wire:model.live.debounce.300ms="razaoSocial" placeholder="Razão social..." />
            </div>

            <!-- CNPJ -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="cnpj" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    CNPJ
                </label>
                <x-input id="cnpj" wire:model.live.debounce.300ms="cnpj" placeholder="CNPJ..." />
            </div>

            <!-- Tratamento -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="tratamento" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Tratamento
                </label>
                <x-input id="tratamento" wire:model.live.debounce.300ms="tratamento" placeholder="Tratamento..." />
            </div>

            <!-- Vendedor -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="vendedor" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Vendedor
                </label>
                <x-input id="vendedor" wire:model.live.debounce.300ms="vendedor" placeholder="Nome do vendedor..." />
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
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    Limpar filtros
                </x-button>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('nome_fantasia')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Nome Fantasia
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('cnpj')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            CNPJ
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('razao_social')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Razão Social
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('tratamento')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Tratamento
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('vendedor_id')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Vendedor
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('bloqueios.created_at')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Data Bloqueio
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Motivo
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Bloqueado por
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($clientes as $c)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4">
                            <a href="{{ route('clientes.show', $c->id) }}" class="text-zinc-800 dark:text-zinc-200 hover:underline">
                                {{ $c->nome_fantasia ?? '-' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->cnpj ?? '-' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->razao_social ?? 'Não cadastrado' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->tratamento ?? 'Não cadastrado' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->vendedor?->name ?? 'Não cadastrado' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ optional($c->ultimoBloqueio?->created_at)->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->ultimoBloqueio?->motivo ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->ultimoBloqueio?->user?->name ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum cliente bloqueado encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($clientes->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $clientes->links() }}
            </div>
        </div>
    @endif
</div>