<x-layouts.app :title="'Relatório de Recebimento'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório de Recebimento de Produtos</flux:heading>
            <flux:subheading>Produtos recebidos de fornecedores via pedidos de compra.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.recebimento_produtos') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:select name="fornecedor_id" label="Fornecedor">
                <option value="">Todos</option>
                @foreach($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}" {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                        {{ $fornecedor->nome_fantasia }}
                    </option>
                @endforeach
            </flux:select>

            <flux:input name="nf" label="Nr. Nota Fiscal" value="{{ request('nf') }}" placeholder="000.000..." />
            <flux:input name="romaneio" label="Nr. Romaneio" value="{{ request('romaneio') }}" placeholder="Romaneio..." />

            <flux:input type="date" name="data_inicio" label="Início Período" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Período" value="{{ request('data_fim') }}" />

            <div class="md:col-span-3 lg:col-span-4 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.recebimento_produtos') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Lista de Recebimentos --}}
        <div class="space-y-4">
            @forelse ($recebimentos as $recebimento)
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-neutral-50 dark:bg-neutral-900 px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center text-sm">
                        <div class="flex items-center gap-4">
                            <flux:heading size="sm" class="font-bold">NF: {{ $recebimento->nota_fiscal_fornecedor ?? 'S/N' }}</flux:heading>
                            <flux:badge color="zinc" variant="outline" size="sm">ID #{{ $recebimento->id }}</flux:badge>
                            <span class="text-neutral-500">{{ $recebimento->data_movimentacao->format('d/m/Y') }}</span>
                        </div>
                        <div class="text-neutral-500">
                            <strong>Fornecedor:</strong> {{ $recebimento->fornecedor->nome_fantasia ?? 'N/A' }} | 
                            <strong>Encomenda:</strong> #{{ $recebimento->pedidoCompra->id ?? '-' }}
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-neutral-50/50 dark:bg-neutral-900/50 text-xs uppercase text-neutral-600 dark:text-neutral-400">
                                <tr>
                                    <th class="px-6 py-2">Produto</th>
                                    <th class="px-6 py-2 text-center">Quantidade</th>
                                    <th class="px-6 py-2">Responsável</th>
                                    <th class="px-6 py-2">Romaneio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach($recebimento->itens as $item)
                                    <tr>
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</div>
                                            <div class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-center font-bold text-emerald-600">{{ $item->quantidade }}</td>
                                        <td class="px-6 py-3 text-neutral-500 text-xs">{{ $recebimento->usuario->name }}</td>
                                        <td class="px-6 py-3 text-neutral-500 text-xs">{{ $recebimento->romaneiro ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl p-10 text-center text-neutral-500 shadow-sm">
                    Nenhum recebimento encontrado para os filtros selecionados.
                </div>
            @endforelse
            
            @if($recebimentos->hasPages())
                <div class="mt-6">
                    {{ $recebimentos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
