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
                    @foreach ([
                        'id' => 'Código',
                        'status' => 'Status',
                        'cor' => 'Cor',
                        'nome' => 'Nome',
                        'descricao' => 'Descrição',
                        'fornecedor_id' => 'Fornecedor',
                        'ncm' => 'NCM',
                        'preco_venda' => 'Preço venda',
                        'preco_custo' => 'Preço custo',
                        'estoque_atual' => 'Quantidade atual',
                        'estoque_minimo' => 'Estoque mínimo',
                    ] as $field => $label)
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('{{ $field }}')"
                                class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                {{ $label }}
                            </button>
                        </th>
                    @endforeach
                    <th class="px-6 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($produtos as $c)
                   <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}" 
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition" class="hover:underline">
                                {{ $c->id }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}" 
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition" class="hover:underline">
                                {{ $c->status }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @if ($c->cor)
                                <span class="inline-flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                                    <span class="w-5 h-5 border border-zinc-300 dark:border-zinc-600 rounded"
                                        style="background-color: {{ $c->cor->codigo_hex }}"></span>
                                    {{ $c->cor->nome }}
                                </span>
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">Sem cor</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}" class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                {{ $c->nome }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}" class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                {{ $c->descricao }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @if ($c->fornecedor)
                                <a href="/fornecedores/{{ $c->fornecedor->id }}"  class="hover:underline"
                                class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                    {{ $c->fornecedor->nome_fantasia }}
                                </a>
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">Sem fornecedor</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}"  class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                {{ $c->ncm }}
                            </a>
                        </td>
                        <!-- Preço venda -->
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}"  class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                R$ {{ number_format($c->preco_venda, 2, ',', '.') }}
                            </a>
                        </td>
                        <!-- Preço custo -->
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}"  class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                R$ {{ number_format($c->preco_custo, 2, ',', '.') }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}"  class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                {{ $c->estoque_atual }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="/produtos/{{ $c->id }}"  class="hover:underline"
                            class="text-zinc-800 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-zinc-50 font-medium transition">
                                {{ $c->estoque_minimo }}
                            </a>
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            <a href="{{ route('produtos.edit', $c->id) }}">
                                <x-button size="sm" variant="secondary">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    Editar
                                </x-button>
                            </a>
                            <form action="{{ route('produtos.destroy', $c->id) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
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
                        <td colspan="11" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
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
