{{-- resources/views/livewire/lista-orcamento-pagos-balcao.blade.php (ou o nome que você usa) --}}
{{--
    CORREÇÃO: coluna PDF agora aponta para o comprovante do PAGAMENTO,
    não para $o->pdf_path (que é o PDF do orçamento).
--}}

<div>
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de orçamentos - Pagos - Balcão
        </h2>
        <div class="flex items-end gap-4">
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pesquisar</label>
                <x-input id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar ..." />
            </div>
            <div class="flex flex-col w-28">
                <label for="perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Itens por página:</label>
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
    <div class="flex items-end gap-4 px-6 py-4">
        <div class="flex items-end gap-4 flex-wrap">
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="cliente" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cliente</label>
                <x-input id="cliente" wire:model.live.debounce.300ms="cliente" placeholder="Nome do cliente..." />
            </div>
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="cidade" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cidade</label>
                <x-input id="cidade" wire:model.live.debounce.300ms="cidade" placeholder="Nome da cidade..." />
            </div>
            <div class="flex flex-col flex-[2] min-w-[180px]">
                <label for="vendedor" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vendedor</label>
                <x-input id="vendedor" wire:model.live.debounce.300ms="vendedor" placeholder="Nome do vendedor..." />
            </div>
            <div class="flex flex-col w-40">
                <label for="dataInicio" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data início</label>
                <x-input type="date" id="dataInicio" wire:model.live="dataInicio" />
            </div>
            <div class="flex flex-col w-40">
                <label for="dataFim" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data fim</label>
                <x-input type="date" id="dataFim" wire:model.live="dataFim" />
            </div>
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
                        <button wire:click="sortBy('id')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Orçamento ID
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('obra')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Obra
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('cliente')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cliente
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('status')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status Pedido
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Pagamento
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('workflow_status')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status Separação
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('vendedor_id')" class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Vendedor
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Comprovante
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($orcamentos as $o)
                    {{-- $o->pagamento é a relação singular (hasOne ou via eager load) --}}
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">

                        {{-- ID --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('orcamentos.show', $o) }}" class="hover:underline">{{ $o->id }}</a>
                        </td>

                        {{-- Obra --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('orcamentos.show', $o) }}" class="hover:underline font-medium">{{ $o->obra }}</a>
                        </td>

                        {{-- Cliente --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->cliente->nome }}</td>

                        {{-- Status pedido --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->status }}</td>

                        {{-- Pagamento: valor + formas --}}
                        <td class="px-6 py-4">
                            @if($o->pagamento)
                                <a href="{{ route('pagamentos.show', $o->pagamento->id) }}"
                                   class="inline-flex items-center gap-1.5 font-semibold text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 hover:underline transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    R$ {{ number_format($o->pagamento->valor_pago, 2, ',', '.') }}
                                </a>
                                @if($o->pagamento->formas->isNotEmpty())
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($o->pagamento->formas as $forma)
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

                        {{-- Status separação --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->workflow_status }}</td>

                        {{-- Vendedor --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $o->vendedor->name }}</td>

                        {{-- Comprovante PDF do PAGAMENTO (abre inline no navegador) --}}
                        <td class="px-6 py-4">
                            @if($o->pagamento && $o->pagamento->temPdf())
                                <a href="{{ route('pagamentos.comprovante-pdf', $o->pagamento) }}"
                                   target="_blank"
                                   title="Abrir comprovante do pagamento #{{ $o->pagamento->id }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow transition-colors">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    Comprovante
                                </a>
                            @elseif($o->pagamento && !$o->pagamento->temPdf())
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-400 dark:text-zinc-500"
                                      title="PDF não foi gerado para este pagamento">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Sem PDF
                                </span>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Ações --}}
                        <td class="px-6 py-4">
                            @if($o->pagamento)
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
                        <td colspan="9" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum orçamento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if($orcamentos->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>{{ $orcamentos->links() }}</div>
        </div>
    @endif
</div>