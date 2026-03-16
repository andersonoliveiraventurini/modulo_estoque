<x-layouts.app :title="'Relatório de Vencimento'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Data de Vencimento de Produtos</flux:heading>
            <flux:subheading>Monitore a validade dos produtos em estoque por lote.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.vencimento_produtos') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:input name="produto_nome" label="Descrição" value="{{ request('produto_nome') }}" placeholder="Nome do produto..." />
            <flux:input name="sku" label="Cód. Produto (SKU)" value="{{ request('sku') }}" placeholder="SKU..." />
            
            <flux:select name="fornecedor_id" label="Fornecedor">
                <option value="">Todos</option>
                @foreach($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}" {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                        {{ $fornecedor->nome_fantasia }}
                    </option>
                @endforeach
            </flux:select>

            <flux:input type="date" name="data_inicio" label="Início Vencimento" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Vencimento" value="{{ request('data_fim') }}" />

            <div class="md:col-span-3 lg:col-span-5 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.vencimento_produtos') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Tabela --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Produto</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">SKU</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">Fornecedor</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Data Vencimento</th>
                        <th class="px-6 py-4 font-semibold text-neutral-900 dark:text-white text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($vencimentos as $item)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</span>
                            </td>
                            <td class="px-6 py-4 text-neutral-500">{{ $item->produto->sku }}</td>
                            <td class="px-6 py-4 text-neutral-500">{{ $item->fornecedor->nome_fantasia ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $item->data_vencimento->isPast() ? 'text-red-600 font-bold' : ($item->data_vencimento->diffInDays(now()) < 30 ? 'text-orange-500 font-bold' : 'text-neutral-600') }}">
                                    {{ $item->data_vencimento->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->data_vencimento->isPast())
                                    <flux:badge color="red" variant="solid">Vencido</flux:badge>
                                @elseif($item->data_vencimento->diffInDays(now()) < 30)
                                    <flux:badge color="orange" variant="solid">Próximo ao Vencimento</flux:badge>
                                @else
                                    <flux:badge color="lime" variant="solid">Válido</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-neutral-500">Nenhum produto com vencimento encontrado para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($vencimentos->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                    {{ $vencimentos->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
