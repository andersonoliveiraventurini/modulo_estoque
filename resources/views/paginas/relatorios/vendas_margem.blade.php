<x-layouts.app :title="'Vendas e Margem'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório de Produtos Vendas Margem</flux:heading>
            <flux:subheading>Análise detalhada de vendas e lucratividade por produto.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.vendas_margem') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm items-end">
            <div class="md:col-span-2">
                <flux:select name="produto_id" label="Produto" required>
                    <option value="">Selecione um produto...</option>
                    @foreach($produtos as $p)
                        <option value="{{ $p->id }}" {{ request('produto_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->sku }} - {{ $p->nome }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input type="date" name="data_inicio" label="Início" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim" value="{{ request('data_fim') }}" />

            <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Gerar Relatório</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.vendas_margem') }}">Limpar</flux:button>
            </div>
        </form>

        @if($produto)
            {{-- Resumo do Produto --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
                    <flux:heading size="sm" class="mb-2">Total Vendido (Qtd)</flux:heading>
                    <div class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $vendas->sum('quantidade') }}</div>
                </div>
                <div class="p-6 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
                    <flux:heading size="sm" class="mb-2">Valor Total Bruto</flux:heading>
                    <div class="text-3xl font-bold text-emerald-600">R$ {{ number_format($vendas->sum(fn($v) => $v->quantidade * $v->valor_unitario), 2, ',', '.') }}</div>
                </div>
                <div class="p-6 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
                    <flux:heading size="sm" class="mb-2">Desconto Médio (%)</flux:heading>
                    <div class="text-3xl font-bold text-orange-500">{{ $vendas->avg('desconto') > 0 ? number_format($vendas->avg('desconto'), 2, ',', '.') : '0,00' }}%</div>
                </div>
            </div>

            {{-- Tabela de Vendas --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Data</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Orçamento</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Cliente</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Qtd.</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-right">Preço Unit.</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Desconto</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-right">Total Item</th>
                            <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-right">Vendedor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($vendas as $venda)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors">
                                <td class="px-6 py-4 text-neutral-500">{{ $venda->orcamento->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">#{{ $venda->orcamento->id }}</td>
                                <td class="px-6 py-4 text-neutral-500">{{ $venda->orcamento->cliente->nome ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center">{{ $venda->quantidade }}</td>
                                <td class="px-6 py-4 text-right">R$ {{ number_format($venda->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center font-medium text-orange-500">{{ number_format($venda->desconto, 2, ',', '.') }}%</td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">R$ {{ number_format($venda->valor_com_desconto, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-xs text-neutral-500">{{ $venda->orcamento->vendedor->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-neutral-500">Nenhuma venda encontrada para este produto no período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-10 text-center text-indigo-700 dark:text-indigo-300 shadow-sm">
                Selecione um produto e o período para gerar o relatório de vendas e margem.
            </div>
        @endif
    </div>
</x-layouts.app>
