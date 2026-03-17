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
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Solicitante / Data</span>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Itens / Resumo</span>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Cliente</span>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</span>
                    </th>
                    <th class="px-3 py-3 text-left">Validade</th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($grupos as $grupo)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <a href="{{ route('usuarios.show', $grupo->usuario_id) }}" class="text-xs font-bold text-zinc-800 dark:text-zinc-200 hover:underline">
                                    {{ $grupo->usuario?->name }}
                                </a>
                                <span class="text-[10px] text-zinc-400 font-medium">{{ $grupo->created_at->format('d/m/Y') }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-4">
                            <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}" class="flex flex-col group max-w-[250px]">
                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:underline">
                                    {{ $grupo->itens->count() }} item(s) solicitado(s)
                                </span>
                                <span class="text-[10px] text-zinc-400 truncate italic">
                                    {{ $grupo->itens->pluck('descricao')->implode(', ') }}
                                </span>
                            </a>
                        </td>
                        <td class="px-3 py-4">
                            <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate max-w-[150px] inline-block" title="{{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome ?? '—' }}">
                                {{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-4">
                            @php
                                $statusStyles = match($grupo->status) {
                                    'Pendente'              => 'bg-amber-100 text-amber-700',
                                    'Aguardando fornecedor' => 'bg-blue-100 text-blue-700',
                                    'Disponível'            => 'bg-emerald-100 text-emerald-800',
                                    'Aprovado'              => 'bg-green-100 text-green-700',
                                    'Expirado'              => 'bg-red-100 text-red-700',
                                    'Cancelado'             => 'bg-zinc-100 text-zinc-600',
                                    default                 => 'bg-zinc-100 text-zinc-700'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase whitespace-nowrap {{ $statusStyles }}">
                                {{ $grupo->status }}
                            </span>
                        </td>
                        <td class="px-3 py-4 text-[11px] font-medium">
                            @if ($grupo->validade)
                                <span class="{{ $grupo->estaExpirado() ? 'text-red-500' : 'text-zinc-600 dark:text-zinc-400' }}">
                                    {{ $grupo->validade->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}" title="Visualizar" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>

                                @if (in_array($grupo->status, ['Pendente', 'Aguardando fornecedor']) && $grupo->itens->count() > 0)
                                    <a href="{{ route('consulta_preco.show_grupo', $grupo->id) }}" title="Preencher Preços" class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded-lg transition">
                                        <x-heroicon-o-currency-dollar class="w-5 h-5" />
                                    </a>
                                @endif

                                <form action="{{ route('consulta_preco.destroy_grupo', $grupo->id) }}" method="POST"
                                      onsubmit="return confirm('Excluir esta cotação e todos os itens?');" class="inline">
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
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhuma cotação encontrada.
                        </td>
                    </tr>
                @endforelse
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
