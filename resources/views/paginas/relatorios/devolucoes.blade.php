<x-layouts.app :title="'Relatório de Devoluções'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório de Devoluções</flux:heading>
            <flux:subheading>Produtos devolvidos ao estoque por clientes ou rotinas específicas.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.devolucoes') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:input type="date" name="data_inicio" label="Início Período" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Período" value="{{ request('data_fim') }}" />

            <div class="md:col-span-2 lg:col-span-3 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.devolucoes') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Tabela --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Data</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Produto</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Qtd.</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Fornecedor</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-right">Responsável</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($devolucoes as $mov)
                        @foreach($mov->itens as $item)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors">
                                <td class="px-6 py-4 text-neutral-500">{{ $mov->data_movimentacao->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</div>
                                    <div class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-orange-600">{{ $item->quantidade }}</td>
                                <td class="px-6 py-4 text-neutral-500 text-xs">{{ $item->fornecedor->nome_fantasia ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-right text-neutral-500 text-xs">{{ $mov->usuario->name }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-neutral-500">Nenhuma devolução encontrada para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($devolucoes->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                    {{ $devolucoes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
