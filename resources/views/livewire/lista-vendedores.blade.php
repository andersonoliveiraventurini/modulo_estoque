<div>
    <!-- Container -->
    <div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

        <!-- Cabeçalho -->
        <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                Listagem de vendedores
            </h2>
            <div class="flex items-end gap-4">
                <div class="flex flex-col flex-[2]">
                    <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Pesquisar
                    </label>
                    <x-input id="search" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nome ou desconto..." />
                </div>
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
                            <button wire:click="sortBy('nome')"
                                class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                Nome
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('externo')"
                                class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                Tipo
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('desconto')"
                                class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                Desconto (%)
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($vendedores as $v)
                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                            <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                                <a href="/usuarios/{{ $v->user->id }}" class="hover:underline">{{ $v->user->name }}</a>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $tipo = match((int) $v->externo) {
                                        0 => ['label' => 'Interno',    'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                                        1 => ['label' => 'Externo',    'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'],
                                        2 => ['label' => 'Assistente', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'],
                                        default => ['label' => '-',    'class' => 'bg-zinc-100 text-zinc-500'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipo['class'] }}">
                                    {{ $tipo['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-zinc-800 dark:text-zinc-200">
                                {{ $v->desconto }}%
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('vendedores.edit', $v->id) }}"
                                        class="text-zinc-500 hover:text-primary-600 dark:hover:text-primary-400 transition"
                                        title="Editar">
                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="confirmarDelete({{ $v->id }}, '{{ addslashes($v->user->name) }}')"
                                        class="text-zinc-500 hover:text-red-600 dark:hover:text-red-400 transition"
                                        title="Excluir">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                Nenhum vendedor encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if ($vendedores->hasPages())
            <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $vendedores->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de confirmação de exclusão (Livewire-friendly) -->
    @if ($confirmandoDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-md mx-4 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Excluir vendedor</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Esta ação não pode ser desfeita.</p>
                    </div>
                </div>

                <p class="text-sm text-zinc-700 dark:text-zinc-300 mb-6">
                    Tem certeza que deseja excluir o vendedor
                    <strong class="text-zinc-900 dark:text-zinc-100">{{ $nomeParaDeletar }}</strong>?
                </p>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelarDelete"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="deletar"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition">
                        Sim, excluir
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>