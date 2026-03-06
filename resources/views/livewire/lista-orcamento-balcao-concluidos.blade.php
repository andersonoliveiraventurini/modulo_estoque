<!-- Container  class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700"  -->
<div>

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de orçamentos - Pagos - Balcão
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar  ..." />
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

            <!-- Cliente -->
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="cidade" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Cidade
                </label>
                <x-input id="cidade" wire:model.live.debounce.300ms="cidade" placeholder="Nome da cidade..." />
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
                    <x-heroicon-o-x-mark class="w-2 h-2" />
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
                        <button wire:click="sortBy('obra')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Obra
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('cliente')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cliente
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('status')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status Pedido
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Valor Pago
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('workflow_status')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status separação
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('vendedor_id')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Vendedor
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        PDF
                    </th>
                    <th class="px-6 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($orcamentos as $o)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a
                                href="{{ route('orcamentos.show', $o) }}"
                                class="hover:underline">{{ $o->obra }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->cliente->nome }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->status }}</td>
                        <td class="px-6 py-4">
                            @if ($o->pagamento)
                                <a href="{{ route('pagamentos.show', $o->pagamento->id) }}"
                                   class="inline-flex items-center gap-1.5 font-semibold text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 hover:underline transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    R$ {{ number_format($o->pagamento->valor_pago, 2, ',', '.') }}
                                </a>
                                @if ($o->pagamento->formas->isNotEmpty())
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($o->pagamento->formas as $forma)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                                {{ $forma->condicaoPagamento->nome ?? '—' }}
                                                <span class="ml-1 text-zinc-400 dark:text-zinc-500">
                                                    R$ {{ number_format($forma->valor, 2, ',', '.') }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->workflow_status }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->vendedor->name }}</td>
                        <td class="px-6 py-4">
                            @if ($o->pdf_path)
                                <a href="{{ asset('storage/' . $o->pdf_path) }}" target="_blank">
                                    <x-button size="sm" variant="primary">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                        PDF
                                    </x-button>
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            @if ($o->pagamento)
                                <a href="{{ route('pagamentos.show', $o->pagamento->id) }}">
                                    <x-button size="sm" variant="secondary">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        Ver pagamento
                                    </x-button>
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Sem pagamento</span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum orçamento encontrado.
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
