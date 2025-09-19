<div>
    <!-- Campo de busca -->
    <div class="flex items-center gap-2 mb-4">
       <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome, quantidade ..." />

<!--         <x-button variant="primary" wire:click="buscar">
            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
            Buscar
        </x-button>-->

        <select wire:model="perPage"
            class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="5">5 por página</option>
            <option value="10">10 por página</option>
            <option value="25">25 por página</option>
            <option value="50">50 por página</option>
        </select>
    </div>

    <!-- Tabela de produtos -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Código</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Cor</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Nome</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Descrição</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Fornecedor</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Preço Venda</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($produtos as $produto)
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                        <td class="px-4 py-2 text-sm">{{ $produto->id }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if ($produto->cor)
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-5 h-5 border border-zinc-300 dark:border-zinc-600 rounded"
                                        style="background-color: {{ $produto->cor->codigo_hex }}"></span>
                                    {{ $produto->cor->nome }}
                                </span>
                            @else
                                Sem cor
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $produto->nome }}</td>
                        <td class="px-4 py-2 text-sm">{{ $produto->descricao }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $produto->fornecedor?->nome_fantasia ?? 'Sem fornecedor' }}
                        </td>
                        <td class="px-4 py-2 text-sm">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-center">
                            <x-button variant="primary"
                                onclick="adicionarProduto('{{ $produto->id }}', '{{ $produto->nome }}', '{{ $produto->preco_venda }}')">
                                <x-heroicon-o-plus class="w-4 h-4" />
                                Selecionar
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="mt-4">
        {{ $produtos->links() }}
    </div>
</div>
