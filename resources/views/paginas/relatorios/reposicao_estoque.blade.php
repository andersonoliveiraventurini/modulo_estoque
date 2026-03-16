<x-layouts.app :title="'Relatório de Reposição'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatório da rotina Reposição de Estoque</flux:heading>
            <flux:subheading>Histórico de movimentações de reposição aprovadas.</flux:subheading>
        </div>

        {{-- Filtros --}}
        <form action="{{ route('relatorios.reposicao_estoque') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-sm">
            <flux:select name="fornecedor_id" label="Fornecedor">
                <option value="">Todos</option>
                @foreach($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}" {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                        {{ $fornecedor->nome_fantasia }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select name="repositor_id" label="Repositor">
                <option value="">Todos</option>
                @foreach($usuarios as $user)
                    <option value="{{ $user->id }}" {{ request('repositor_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select name="supervisor_id" label="Responsável">
                <option value="">Todos</option>
                @foreach($usuarios as $user)
                    <option value="{{ $user->id }}" {{ request('supervisor_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </flux:select>

            <flux:input type="date" name="data_inicio" label="Início Período" value="{{ request('data_inicio') }}" />
            <flux:input type="date" name="data_fim" label="Fim Período" value="{{ request('data_fim') }}" />

            <div class="md:col-span-3 lg:col-span-5 flex justify-end gap-2 mt-2">
                <flux:button type="submit" variant="filled" color="indigo">Filtrar</flux:button>
                <flux:button variant="ghost" href="{{ route('relatorios.reposicao_estoque') }}">Limpar</flux:button>
            </div>
        </form>

        {{-- Lista de Movimentações --}}
        <div class="space-y-4">
            @forelse($movimentacoes as $mov)
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-neutral-50 dark:bg-neutral-900 px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <flux:heading size="sm" class="font-bold">#{{ $mov->id }} - {{ $mov->data_movimentacao->format('d/m/Y') }}</flux:heading>
                            <flux:badge color="lime" variant="solid" size="sm">Reposição</flux:badge>
                        </div>
                        <div class="text-xs text-neutral-500">
                            <strong>Repositor:</strong> {{ $mov->usuario->name }} | 
                            <strong>Responsável:</strong> {{ $mov->supervisor->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-neutral-50/50 dark:bg-neutral-900/50 text-xs uppercase text-neutral-600 dark:text-neutral-400">
                                <tr>
                                    <th class="px-6 py-2">Produto</th>
                                    <th class="px-6 py-2 text-center">Quantidade</th>
                                    <th class="px-6 py-2">Fornecedor</th>
                                    <th class="px-6 py-2">Observação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                @foreach($mov->itens as $item)
                                    <tr>
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-neutral-900 dark:text-white">{{ $item->produto->nome }}</div>
                                            <div class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-center font-bold text-indigo-600">{{ $item->quantidade }}</td>
                                        <td class="px-6 py-3 text-neutral-500">{{ $item->fornecedor->nome_fantasia ?? 'N/A' }}</td>
                                        <td class="px-6 py-3 text-xs text-neutral-400 italic">{{ $item->observacao ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl p-10 text-center text-neutral-500 shadow-sm">
                    Nenhuma movimentação de reposição encontrada para os filtros selecionados.
                </div>
            @endforelse
            
            @if($movimentacoes->hasPages())
                <div class="mt-6">
                    {{ $movimentacoes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
