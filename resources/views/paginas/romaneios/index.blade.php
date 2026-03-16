<x-layouts.app title="Romaneios (Entregas)">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <x-heading size="xl">Romaneios (Entregas)</x-heading>
                <x-subheading>Gerencie as cargas e rotas de entrega dos pedidos concluídos.</x-subheading>
            </div>
            <x-button href="{{ route('romaneios.create') }}" variant="primary" icon="plus" class="shadow-lg shadow-indigo-100 dark:shadow-none hover:scale-105 transition-transform">
                Novo Romaneio
            </x-button>
        </div>

        <div class="relative overflow-hidden rounded-xl bg-indigo-50 dark:bg-zinc-800/50 border border-indigo-100 dark:border-zinc-700 p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Controle de Frota e Entregas</h4>
                    <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                        Esta tela exibe apenas orçamentos destinados a <strong>Entrega Própria ou Transportadora</strong>. 
                        Pedidos do tipo <em>Retirada</em> ou <em>Balcão</em> não aparecem aqui para romaneio.
                    </p>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <x-heroicon-o-truck class="w-24 h-24" />
            </div>
        </div>

        <x-card class="p-4">
            <form method="GET" action="{{ route('romaneios.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <x-select name="status" label="Status">
                    <option value="">Todos</option>
                    <option value="aberto" {{ request('status') == 'aberto' ? 'selected' : '' }}>Aberto</option>
                    <option value="em_transito" {{ request('status') == 'em_transito' ? 'selected' : '' }}>Em Trânsito</option>
                    <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </x-select>

                <x-input type="date" name="data_entrega" label="Data de Entrega" value="{{ request('data_entrega') }}" />

                <div class="md:col-span-2 flex gap-2">
                    <x-button type="submit" variant="secondary" icon="magnifying-glass">Filtrar</x-button>
                    <x-button href="{{ route('romaneios.index') }}" variant="ghost">Limpar</x-button>
                </div>
            </form>
        </x-card>

        <x-table>
            <x-slot name="columns">
                <x-table.column>Descrição</x-table.column>
                <x-table.column>Motorista / Veículo</x-table.column>
                <x-table.column>Data Entrega</x-table.column>
                <x-table.column>Qtd. Pedidos</x-table.column>
                <x-table.column>Status</x-table.column>
                <x-table.column align="right">Ações</x-table.column>
            </x-slot>

            @forelse ($romaneios as $romaneio)
                <x-table.row>
                    <x-table.cell class="font-medium">
                        <a href="{{ route('romaneios.show', $romaneio) }}" class="text-indigo-600 hover:underline">
                            {{ $romaneio->descricao }}
                        </a>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="text-sm font-medium text-zinc-900">{{ $romaneio->motorista ?: '—' }}</div>
                        <div class="text-xs text-zinc-500">{{ $romaneio->veiculo ?: '—' }}</div>
                    </x-table.cell>
                    <x-table.cell>{{ $romaneio->data_entrega?->format('d/m/Y') }}</x-table.cell>
                    <x-table.cell>
                        <x-badge variant="neutral">{{ $romaneio->batches_count }}</x-badge>
                    </x-table.cell>
                    <x-table.cell>
                        @php
                            $variant = match($romaneio->status) {
                                'aberto' => 'warning',
                                'em_transito' => 'primary',
                                'concluido' => 'success',
                                'cancelado' => 'danger',
                                default => 'neutral'
                            };
                            $labels = [
                                'aberto' => 'Aberto',
                                'em_transito' => 'Em Trânsito',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado'
                            ];
                        @endphp
                        <x-badge :variant="$variant">{{ $labels[$romaneio->status] ?? $romaneio->status }}</x-badge>
                    </x-table.cell>
                    <x-table.cell align="right">
                        <x-button href="{{ route('romaneios.show', $romaneio) }}" variant="ghost" size="sm">Gerenciar</x-button>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.row>
                    <x-table.cell colspan="6" class="text-center py-8 text-zinc-500">
                        Nenhum romaneio encontrado.
                    </x-table.cell>
                </x-table.row>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $romaneios->links() }}
        </div>
    </div>
</x-layouts.app>
