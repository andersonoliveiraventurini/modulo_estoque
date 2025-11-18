<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Consulta de preços
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome, quantidade ..." />
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
                        <button wire:click="sortBy('ususario_id')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Solicitado por
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('descricao')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Descrição
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('cor')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Cor
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('preco_venda')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Preço venda
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('preco_custo')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Preço custo
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('responsavel_id')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Orçado por
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('observacao')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Observação
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($precos as $p)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a
                                href="/users/{{ $p->usuario_id }}">{{ $p->usuario?->name }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a
                                href="/consulta_preco/{{ $p->id }}">{{ $p->descricao }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $p->cor }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">R$
                            {{ number_format($p->preco_venda, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">R$
                            {{ number_format($p->preco_custo, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a
                                href="/users/{{ $p->comprador_id }}">{{ $p->comprador?->name }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $p->observacao }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <a href="{{ route('consulta_preco.edit', $p->id) }}">
                                <x-button size="sm" variant="secondary">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    Editar
                                </x-button>
                            </a>

                            <form action="{{ route('consulta_preco.destroy', $p->id) }}" method="POST"
                                onsubmit="return confirm('Deseja excluir esta consulta de preço?');">
                                @csrf
                                @method('DELETE')
                                <x-button size="sm" variant="danger">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                    Excluir
                                </x-button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($precos->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $precos->links() }}
            </div>
        </div>
    @endif
</div>
