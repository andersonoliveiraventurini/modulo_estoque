<x-layouts.app :title="__('Histórico de Vendas')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Histórico de Vendas</flux:heading>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <flux:table :paginate="$vendas">
                <flux:table.columns>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Data</flux:table.column>
                    <flux:table.column>Orçamento</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Vendedor</flux:table.column>
                    <flux:table.column>Valor Total</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Ações</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($vendas as $venda)
                        <flux:table.row :key="$venda->id">
                            <flux:table.cell class="font-medium">#{{ $venda->id }}</flux:table.cell>
                            
                            <flux:table.cell>
                                {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                <a href="{{ route('orcamentos.gerenciar', $venda->orcamento_id) }}" class="text-accent hover:underline">
                                    #{{ $venda->orcamento_id }}
                                </a>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-xs truncate">
                                {{ $venda->cliente?->nome ?? '---' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $venda->vendedor?->name ?? '---' }}
                            </flux:table.cell>

                            <flux:table.cell class="font-bold text-neutral-900 dark:text-white">
                                R$ {{ number_format($venda->valor_total, 2, ',', '.') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @php
                                    $color = match($venda->status) {
                                        'Efetuada', 'Paga' => 'lime',
                                        'Cancelada' => 'red',
                                        default => 'zinc'
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm" variant="solid">{{ $venda->status }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('vendas.show', $venda->id) }}" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-12 text-neutral-500">
                                Nenhuma venda registrada no momento.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts.app>
