<x-layouts.app :title="__('Entradas de Encomendas')">
    <div class="flex flex-col gap-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Entradas de Encomendas</h1>
                <p class="text-sm text-zinc-500 mt-1">Histórico de todos os recebimentos registrados.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('entrada_encomendas.aprovadas') }}">
                    <x-button variant="secondary">
                        <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                        Painel de Compras
                    </x-button>
                </a>
                <a href="{{ route('entrada_encomendas.create') }}">
                    <x-button variant="primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Nova Entrada
                    </x-button>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Cotação</th>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-left">Recebido por</th>
                            <th class="px-4 py-3 text-left">Entregue para</th>
                            <th class="px-4 py-3 text-center">Data Recebimento</th>
                            <th class="px-4 py-3 text-center">Data Entrega</th>
                            <th class="px-4 py-3 text-center">Itens recebidos</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($entradas as $entrada)
                        @php
                            $statusMap = [
                                'Recebido parcialmente' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                'Recebido completo'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                'Entregue'              => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                            ];

                            // Resumo de itens: X/Y recebidos
                            $totalItens     = $entrada->itens->count();
                            $itensCompletos = $entrada->itens->where('recebido_completo', true)->count();
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition">
                            <td class="px-4 py-3 text-zinc-400 text-xs">{{ $entrada->id }}</td>

                            <td class="px-4 py-3">
                                <a href="{{ route('consulta_preco.show_grupo', $entrada->grupo_id) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                    #{{ $entrada->grupo_id }}
                                </a>
                            </td>

                            {{-- ✅ Cliente visível --}}
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300 font-medium">
                                {{ $entrada->cliente->nome_fantasia ?? $entrada->cliente->nome ?? '—' }}
                            </td>

                            {{-- ✅ Recebedor visível --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold">
                                        {{ strtoupper(substr($entrada->recebedor->name, 0, 1)) }}
                                    </span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $entrada->recebedor->name }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                                {{ $entrada->destinatario?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-center text-zinc-500 text-xs">
                                {{ $entrada->data_recebimento->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 text-center text-zinc-500 text-xs">
                                {{ $entrada->data_entrega?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- ✅ Itens recebidos X/Y --}}
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-semibold {{ $itensCompletos === $totalItens ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $itensCompletos }}/{{ $totalItens }}
                                </span>
                                <span class="text-xs text-zinc-400 block">completos</span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusMap[$entrada->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $entrada->status }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('entrada_encomendas.show', $entrada->id) }}">
                                    <x-button size="sm" variant="secondary">
                                        <x-heroicon-o-eye class="w-3.5 h-3.5" /> Ver
                                    </x-button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-zinc-400">
                                Nenhuma entrada registrada ainda.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($entradas->hasPages())
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
                    {{ $entradas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
