<x-layouts.app :title="'Relatório de Saída'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório de Saída de Produtos</flux:heading>
            <flux:subheading>Histórico de todas as saídas de estoque (vendas, descartes, transferências).</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.saida_produtos') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:input type="date" name="data_inicio" label="Início Período" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Período" value="{{ request('data_fim') }}" />

            <div class="md:col-span-2 lg:col-span-3 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.saida_produtos') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Lista de Saídas --}}
        <div class="space-y-4">
            @forelse($saidas as $saida)
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-neutral-50 dark:bg-neutral-900 px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center text-sm">
                        <div class="flex items-center gap-4">
                            <flux:heading size="sm" class="font-bold">Saída #{{ $saida->id }}</flux:heading>
                            <span class="text-neutral-500">{{ $saida->data_movimentacao->format('d/m/Y') }}</span>
                            <flux:badge color="orange" variant="solid" size="sm">{{ strtoupper($saida->tipo) }}</flux:badge>
                        </div>
                        <div class="text-neutral-500 text-xs">
                            <strong>Usuário:</strong> {{ $saida->usuario->name ?? 'N/A' }} | 
                            <strong>NF/Romaneio:</strong> {{ $saida->nota_fiscal_fornecedor ?? 'N/A' }} / {{ $saida->romaneiro ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-neutral-50/50 dark:bg-neutral-900/50 text-xs uppercase text-neutral-600 dark:text-neutral-400">
                                <tr>
                                    <th class="px-6 py-2">Produto</th>
                                    <th class="px-6 py-2 text-center">Quantidade</th>
                                    <th class="px-6 py-2">Observação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach($saida->itens as $item)
                                    <tr>
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</div>
                                            <div class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-center font-bold text-red-600">{{ $item->quantidade }}</td>
                                        <td class="px-6 py-3 text-neutral-500 text-xs italic">{{ $item->observacao ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl p-10 text-center text-neutral-500 shadow-sm">
                    Nenhuma saída encontrada para os filtros selecionados.
                </div>
            @endforelse
            
            @if($saidas->hasPages())
                <div class="mt-6">
                    {{ $saidas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
