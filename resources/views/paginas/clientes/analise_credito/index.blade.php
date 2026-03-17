<x-layouts.app :title="__('Relatório de Análise de Crédito')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
             <div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                        Análises de Crédito
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left">Cliente</th>
                                <th class="px-6 py-3 text-left">Limite Boleto</th>
                                <th class="px-6 py-3 text-left">Limite Carteira</th>
                                <th class="px-6 py-3 text-left">Data</th>
                                <th class="px-6 py-3 text-left">Observações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($analises as $a)
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                    <td class="px-6 py-4 font-bold text-zinc-900 dark:text-zinc-50">
                                        {{ $a->cliente->nome ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">R$ {{ number_format($a->limite_boleto, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4">R$ {{ number_format($a->limite_carteira, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4">{{ $a->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-xs text-zinc-500">{{ $a->observacoes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                        Nenhuma análise encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
             </div>
        </div>
    </div>
</x-layouts.app>
