<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Clientes
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome fantasia, razão social, tratamento, CNPJ, limite ou desconto..." />
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
                    </th>
                    <th class="px-6 py-3 text-left">
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('nome_fantasia')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Nome Brcom
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('nome_fantasia')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Nome Fantasia
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
                        <button wire:click="sortBy('cnpj')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            CNPJ
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('limite')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Limite
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('desconto')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Desconto
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($clientes as $c)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('orcamentos.criar', $c->id ?? null) }}"
                                class="text-secondary-600 text-sm font-medium" class="hover:underline">
                                Criar Orçamento
                            </a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('consulta_preco.criar_cotacao', $c->id ?? null) }}"
                                class="text-secondary-600 text-sm font-medium" class="hover:underline">
                                Fazer cotação
                            </a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="/clientes/{{ $c->id }}" class="hover:underline">{{ $c->nome }}</a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a href="/clientes/{{ $c->id }}"
                                class="hover:underline">{{ $c->nome_fantasia }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a href="/clientes/{{ $c->id }}"
                                class="hover:underline">{{ $c->razao_social }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200"><a href="/clientes/{{ $c->id }}"
                                class="hover:underline">{{ $c->tratamento ?? '-' }}</a></td>
                        <td class="px-6 py-4 font-mono text-zinc-800 dark:text-zinc-200"><a
                                href="/clientes/{{ $c->id }}"
                                class="hover:underline">{{ $c->cnpj_formatado }}</a></td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->limite ?? '-' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->desconto ?? '-' }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <a href="{{ route('clientes.edit', $c->id) }}">
                                <x-button size="sm" variant="secondary">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    Editar
                                </x-button>
                            </a>

                            <form action="{{ route('clientes.destroy', $c->id) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
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
                            Nenhum cliente encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($clientes->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $clientes->links() }}
            </div>
        </div>
    @endif
</div>
