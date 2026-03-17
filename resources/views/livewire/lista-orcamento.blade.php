<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de orçamentos
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
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Nº / Obra
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('cliente')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cliente
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('status')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('vendedor_id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Vendedor
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">Documentos</th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @if($orcamentos->isNotEmpty())
                    @foreach ($orcamentos as $o)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <a href="{{ route('orcamentos.show', $o) }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline">
                                    #{{ $o->id }}
                                </a>
                                @if($o->obra)
                                    <span class="text-[10px] text-zinc-400 uppercase truncate max-w-[150px]" title="{{ $o->obra }}">
                                        {{ $o->obra }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <a href="{{ route('clientes.show', $o->cliente) }}" class="text-xs font-bold text-zinc-800 dark:text-zinc-200 hover:underline truncate max-w-[200px]">
                                    {{ $o->cliente->nome }}
                                </a>
                                <span class="text-[10px] text-zinc-400 font-mono">{{ $o->cliente->numero_brcom }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            @php
                                $statusColor = match($o->status) {
                                    'Pago', 'Concluído' => 'bg-green-100 text-green-700',
                                    'Pendente' => 'bg-amber-100 text-amber-700',
                                    'Aprovar desconto', 'Aprovar pagamento' => 'bg-blue-100 text-blue-700',
                                    'Cancelado' => 'bg-red-100 text-red-700',
                                    default => 'bg-zinc-100 text-zinc-700'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase whitespace-nowrap {{ $statusColor }}">
                                {{ $o->status }}
                            </span>
                        </td>
                        <td class="px-3 py-4 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $o->vendedor->name }}
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                @if ($o->pdf_path && !in_array($o->status, ['Aprovar desconto', 'Aprovar pagamento']))
                                    <a href="{{ asset('storage/' . $o->pdf_path) }}" target="_blank" title="Baixar PDF" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                                        <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                    </a>
                                @endif
                                @if ($o->xml_path)
                                    <x-heroicon-o-code-bracket-square title="XML Disponível" class="w-5 h-5 text-zinc-400" />
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                @if (in_array($o->status, ['Pendente', 'Aprovar desconto']))
                                    <a href="{{ route('orcamentos.edit', $o->id) }}" title="Editar" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    </a>
                                @endif
                                
                                @if (!$o->encomenda)
                                    <form action="{{ route('orcamentos.duplicar', $o->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja duplicar este orçamento?');" class="inline">
                                        @csrf
                                        <button type="submit" title="Duplicar" class="p-1.5 text-blue-400 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition">
                                            <x-heroicon-o-document-duplicate class="w-5 h-5" />
                                        </button>
                                    </form>
                                @endif

                                @if ($o->status !== 'Pago')
                                    <form action="{{ route('orcamentos.destroy', $o->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja excluir este orçamento?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Excluir" class="p-1.5 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhum orçamento encontrado.
                        </td>
                    </tr>
                @endif
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
