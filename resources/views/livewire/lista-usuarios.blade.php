<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Usuários
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
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('nome')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Usuário
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">
                        <button wire:click="sortBy('email')" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            E-mail
                        </button>
                    </th>
                    <th class="px-3 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($usuarios as $u)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-3 py-4">
                            <div class="flex flex-col">
                                <a href="/usuarios/{{ $u->id }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline">
                                    {{ $u->name }}
                                </a>
                                @if($u->is_blocked)
                                    <span class="text-[9px] font-bold text-red-500 uppercase">Bloqueado</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-4 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $u->email }}
                        </td>
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('usuarios.editPassword', $u) }}" title="Alterar Senha" class="p-1.5 text-zinc-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <x-heroicon-o-key class="w-5 h-5" />
                                </a>

                                <form action="{{ route('usuarios.toggleBlock', $u) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" title="{{ $u->is_blocked ? 'Desbloquear' : 'Bloquear' }}" 
                                            class="p-1.5 {{ $u->is_blocked ? 'text-emerald-500 hover:bg-emerald-50' : 'text-red-400 hover:text-red-700 hover:bg-red-50' }} rounded-lg transition">
                                        @if($u->is_blocked)
                                            <x-heroicon-o-lock-open class="w-5 h-5" />
                                        @else
                                            <x-heroicon-o-lock-closed class="w-5 h-5" />
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400 font-medium italic">
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($usuarios->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $usuarios->links() }}
            </div>
        </div>
    @endif
</div>
