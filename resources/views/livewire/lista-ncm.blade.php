<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de NCM
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por código, descrição..." />
            </div>
            <!-- Itens por página -->
            <div class="flex flex-col w-32">
                <label for="perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Itens por página
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
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        Código</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        Descrição</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        Ato Legal</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        Vigência</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-300">
                        Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($ncms as $ncm)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200 font-mono">
                            <a href="{{ route('ncm.show', $ncm->id) }}" class="hover:underline">
                                {{ $ncm->codigo }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <div class="max-w-md truncate" title="{{ $ncm->descricao }}">
                                <a href="{{ route('ncm.show', $ncm->id) }}" class="hover:underline">
                                    {{ $ncm->descricao }}
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            @if ($ncm->ato_legal || $ncm->numero || $ncm->ano)
                                <a href="{{ route('ncm.show', $ncm->id) }}" class="hover:underline">
                                    <span class="text-xs">
                                        {{ $ncm->ato_legal }}
                                        @if ($ncm->numero)
                                            Nº {{ $ncm->numero }}
                                        @endif
                                        @if ($ncm->ano)
                                            / {{ $ncm->ano }}
                                        @endif
                                    </span>
                                </a>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            @if ($ncm->data_inicio || $ncm->data_fim)
                                <div class="text-xs space-y-1">
                                    <a href="{{ route('ncm.show', $ncm->id) }}" class="hover:underline">
                                        @if ($ncm->data_inicio)
                                            <div>Início:
                                                {{ \Carbon\Carbon::parse($ncm->data_inicio)->format('d/m/Y') }}</div>
                                        @endif
                                        @if ($ncm->data_fim)
                                            <div>Fim: {{ \Carbon\Carbon::parse($ncm->data_fim)->format('d/m/Y') }}
                                            </div>
                                        @endif
                                </div>
                                </a>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            <a href="{{ route('ncm.edit', $ncm->id) }}">
                                <x-button size="sm" variant="secondary">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    Editar
                                </x-button>
                            </a>

                            <form action="{{ route('ncm.destroy', $ncm->id) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir este NCM?');">
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
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum NCM encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($ncms->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            {{ $ncms->links() }}
        </div>
    @endif
</div>
