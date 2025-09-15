<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Clientes bloqueados
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome fantasia, razão social, tratamento ou CNPJ..." />
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
                    <th class="px-6 py-3 text-left">Nome Fantasia</th>
                    <th class="px-6 py-3 text-left">CNPJ</th>
                    <th class="px-6 py-3 text-left">Data Bloqueio</th>
                    <th class="px-6 py-3 text-left">Motivo</th>
                    <th class="px-6 py-3 text-left">Bloqueado por</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($clientes as $c)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <td class="px-6 py-4">
                            <a href="/clientes/{{ $c->id }}" class="text-zinc-800 dark:text-zinc-200">
                                {{ $c->nome_fantasia ?? '-' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">{{ $c->cnpj ?? '-' }}</td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ optional($c->ultimoBloqueio?->created_at)->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->ultimoBloqueio?->motivo ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->ultimoBloqueio?->user?->name ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum cliente bloqueado encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($clientes->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $clientes->links() }}
            </div>
        </div>
    @endif
</div>
