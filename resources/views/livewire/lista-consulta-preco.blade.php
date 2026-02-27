<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Consulta Encomendas
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
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Solicitado por</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Itens</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Validade</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Criado em</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ações</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse($grupos as $grupo)
                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                    <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                        <a href="{{ route('usuarios.show', $grupo->usuario_id) }}" class="hover:underline">
                            {{ $grupo->usuario?->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                        <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}" class="hover:underline font-medium">
                            {{ $grupo->itens->count() }} item(s)
                            <span class="block text-xs text-zinc-400 font-normal">
                        {{ $grupo->itens->pluck('descricao')->implode(', ') }}
                    </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                        {{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusMap = [
                                'Pendente'              => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                                'Aguardando fornecedor' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                'Disponível'            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                'Aprovado'              => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                'Expirado'              => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                                'Cancelado'             => 'bg-zinc-100 text-zinc-600',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusMap[$grupo->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                    {{ $grupo->status }}
                </span>
                    </td>
                    <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 text-xs">
                        @if ($grupo->validade)
                            <span class="{{ $grupo->estaExpirado() ? 'text-red-500 font-semibold' : '' }}">
                        {{ $grupo->validade->format('d/m/Y H:i') }}
                    </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 text-xs">
                        {{ $grupo->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 flex gap-2 flex-wrap">
                        <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}">
                            <x-button size="sm" variant="secondary">
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Ver
                            </x-button>
                        </a>

                        @if (in_array($grupo->status, ['Pendente', 'Aguardando fornecedor']) && $grupo->itens->count() > 0)
                            <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}">
                                <x-button size="sm" variant="primary">
                                    <x-heroicon-o-currency-dollar class="w-4 h-4" />
                                    Preencher Preços
                                </x-button>
                            </a>
                        @endif

                        <form action="{{ route('consulta_preco.destroy_grupo', $grupo->id) }}" method="POST"
                              onsubmit="return confirm('Excluir esta cotação e todos os itens?');">
                            @csrf
                            @method('DELETE')
                            <x-button size="sm" variant="danger" type="submit">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Excluir
                            </x-button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        Nenhuma cotação encontrada.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($grupos->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $grupos->links() }}
            </div>
        </div>
    @endif
</div>
