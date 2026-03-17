<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de produtos
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome, quantidade ..." />
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

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Produto
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status / Cor</span>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fornecedor / PN</span>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('preco_venda')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Valores (Venda/Custo)
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('estoque_atual')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Estoque
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($produtos as $c)
                   <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                        <td class="px-3 py-4">
                            <div class="flex flex-col max-w-[250px]">
                                <a href="/produtos/{{ $c->id }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline">
                                    {{ $c->nome }}
                                </a>
                                <span class="text-[10px] text-zinc-400 font-mono uppercase truncate">{{ $c->id }} | {{ $c->descricao }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase w-fit {{ in_array(strtolower($c->status), ['ativo', 'active', '1', 'sim']) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $c->status }}
                                </span>
                                @if ($c->cor)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] text-zinc-600 dark:text-zinc-400">
                                        <span class="w-3 h-3 border border-zinc-300 dark:border-zinc-600 rounded-full"
                                            style="background-color: {{ $c->cor->codigo_hex }}"></span>
                                        {{ $c->cor->nome }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[150px]" title="{{ $c->fornecedor->nome_fantasia ?? 'Sem fornecedor' }}">
                                    {{ $c->fornecedor->nome_fantasia ?? '-' }}
                                </span>
                                <span class="text-[10px] text-zinc-400 font-mono">{{ $c->part_number ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold text-zinc-900 dark:text-zinc-50">Venda: R$ {{ number_format($c->preco_venda, 2, ',', '.') }}</span>
                                <span class="text-[10px] text-zinc-400">Custo: R$ {{ number_format($c->preco_custo, 2, ',', '.') }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4 text-xs">
                            <div class="flex flex-col">
                                <span class="font-bold {{ $c->estoque_atual <= $c->estoque_minimo ? 'text-red-600' : 'text-zinc-900 dark:text-zinc-50' }}">
                                    Atual: {{ $c->estoque_atual }}
                                </span>
                                <span class="text-[10px] text-zinc-400 font-medium">Mínimo: {{ $c->estoque_minimo }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('produtos.edit', $c->id) }}" title="Editar" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </a>
                                <form action="{{ route('produtos.destroy', $c->id) }}" method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Excluir" class="p-1.5 text-red-400 fallback-red-700 hover:bg-red-50 rounded-lg transition">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($produtos->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $produtos->links() }}
            </div>
        </div>
    @endif
</div>
