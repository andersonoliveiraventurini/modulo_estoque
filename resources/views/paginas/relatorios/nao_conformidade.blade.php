<x-layouts.app :title="'Inconsistências de Recebimento'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório de Não Conformidade</flux:heading>
            <flux:subheading>Divergências reportadas durante o recebimento de mercadorias.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.nao_conformidade') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:input type="date" name="data_inicio" label="Início Período" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Período" value="{{ request('data_fim') }}" />

            <div class="md:col-span-2 lg:col-span-3 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.nao_conformidade') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Tabela --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Data</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Produto</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Esperado</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Recebido</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Diferença</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Observação</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($inconsistencias as $item)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors">
                            <td class="px-6 py-4 text-neutral-500">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</div>
                                <div class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-neutral-900 dark:text-white">{{ $item->quantidade_esperada }}</td>
                            <td class="px-6 py-4 text-center font-bold text-red-600">{{ $item->quantidade_recebida }}</td>
                            <td class="px-6 py-4 text-center font-bold text-red-600">{{ $item->quantidade_recebida - $item->quantidade_esperada }}</td>
                            <td class="px-6 py-4 text-xs text-neutral-500 max-w-xs truncate">{{ $item->observacao }}</td>
                            <td class="px-6 py-4">
                                <flux:button size="xs" variant="ghost" href="{{ route('inconsistencias.show', $item->id) }}">Ver Detalhes</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-neutral-500">Nenhuma inconsistência encontrada para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($inconsistencias->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                    {{ $inconsistencias->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
