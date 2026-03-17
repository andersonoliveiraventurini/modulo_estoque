<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Fornecedores
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome fantasia, razão social, tratamento ou CNPJ..." />
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
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('id')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Fornecedor
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('tratamento')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Tratamento
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('cnpj')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            CNPJ
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($fornecedores as $f)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-3 py-4">
                            <div class="flex flex-col max-w-[300px]">
                                <a href="/fornecedores/{{ $f->id }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline">
                                    {{ $f->nome_fantasia }}
                                </a>
                                <span class="text-[10px] text-zinc-400 uppercase truncate" title="{{ $f->razao_social }}">
                                    {{ $f->id }} | {{ $f->razao_social }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-4 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $f->tratamento ?? '-' }}
                        </td>
                        <td class="px-3 py-4 font-mono text-[11px] text-zinc-600 dark:text-zinc-400">
                            {{ $f->cnpj_formatado }}
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('fornecedores.edit', $f->id) }}" title="Editar" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </a>

                                <form action="{{ route('fornecedores.destroy', $f->id) }}" method="POST"
                                    onsubmit="return confirm('Deseja excluir este fornecedor?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Excluir" class="p-1.5 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhum fornecedor encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($fornecedores->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $fornecedores->links() }}
            </div>
        </div>
    @endif
</div>
