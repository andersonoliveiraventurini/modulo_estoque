<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Descontos
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por motivo, valor, tipo, cliente..." />
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

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('motivo')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Motivo
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('tipo')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Tipo
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('valor')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Valor
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('porcentagem')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Porcentagem
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('cliente_id')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cliente
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Orçamento
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Pedido
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('created_at')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Data
                        </button>
                    </th>
                    <!-- <th class="px-6 py-3 text-left">Ações</th>-->
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($descontos as $d)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $d->motivo }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $d->tipo === 'fixo' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                {{ ucfirst($d->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            R$ {{ number_format($d->valor, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $d->porcentagem ? number_format($d->porcentagem, 2, ',', '.') . '%' : '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            @if($d->cliente)
                                <a href="/clientes/{{ $d->cliente->id }}" class="text-secondary-600 " class="hover:underline">
                                    {{ $d->cliente->nome_fantasia ?? $d->cliente->nome }}
                                </a>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            @if($d->orcamento_id)
                                <a href="/orcamentos/{{ $d->orcamento_id }}" class="text-secondary-600 " class="hover:underline">
                                    #{{ $d->orcamento_id }}
                                </a>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            @if($d->pedido_id)
                                <a href="/pedidos/{{ $d->pedido_id }}" class="text-secondary-600 "  class="hover:underline">
                                    #{{ $d->pedido_id }}
                                </a>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200 text-xs">
                            {{ $d->created_at->format('d/m/Y H:i') }}
                        </td>
                        <!-- <td class="px-6 py-4 flex gap-2">
                            <a href="{ { route('descontos.edit', $d->id) }}">
                                <x-button size="sm" variant="secondary">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    Editar
                                </x-button>
                            </a>

                            <form action="{ { route('descontos.destroy', $d->id) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir este desconto?');">
                                @ csrf
                                @ method('DELETE')
                                <x-button size="sm" variant="danger">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                    Excluir
                                </x-button>
                            </form>
                        </td>-->
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum desconto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($descontos->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $descontos->links() }}
            </div>
        </div>
    @endif
</div>