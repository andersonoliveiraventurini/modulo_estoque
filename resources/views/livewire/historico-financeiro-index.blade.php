<x-slot name="header">
    Histórico Financeiro de Clientes
</x-slot>

<div class="p-6">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Histórico Financeiro</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Selecione um cliente para acessar o histórico de compras, créditos e descontos.
            </p>
        </div>
        <div class="w-full sm:max-w-xs">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar cliente por nome / CPF / CNPJ..."
                class="w-full rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm shadow-sm placeholder:text-neutral-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-neutral-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-neutral-500"
            />
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                        wire:click="sortBy('nome')"
                    >
                        Cliente
                        @if($sortField === 'nome')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">CPF / CNPJ</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Limite Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Disponível</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-neutral-500">Acesso Rápido</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse($clientes as $cliente)
                    <tr class="group hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('clientes.show', $cliente->id) }}"
                                class="font-semibold text-neutral-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                wire:navigate
                            >
                                {{ $cliente->nome }}
                            </a>
                            @if($cliente->nome_fantasia && $cliente->nome_fantasia !== '*')
                                <p class="text-xs text-neutral-500">{{ $cliente->nome_fantasia }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                            {{ $cliente->cpf ?: $cliente->cnpj ?: '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                            R$ {{ number_format($cliente->limite_total, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium {{ $cliente->limite_disponivel > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            R$ {{ number_format($cliente->limite_disponivel, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a
                                    href="{{ route('clientes.show', $cliente->id) }}#financeiro"
                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                                    title="Histórico Financeiro"
                                    wire:navigate
                                >
                                    💰 Financeiro
                                </a>
                                <a
                                    href="{{ route('clientes.show', $cliente->id) }}#creditos-historico"
                                    class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50"
                                    title="Créditos do Cliente"
                                    wire:navigate
                                >
                                    ✅ Créditos
                                </a>
                                <a
                                    href="{{ route('clientes.show', $cliente->id) }}#descontos"
                                    class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700 hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300 dark:hover:bg-orange-900/50"
                                    title="Histórico de Descontos"
                                    wire:navigate
                                >
                                    🏷️ Descontos
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-neutral-500">
                            Nenhum cliente encontrado para "{{ $search }}".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($clientes->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-neutral-700 dark:bg-zinc-800">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>
</div>
