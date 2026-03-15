<x-layouts.app :title="__('Contas a Receber')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Contas a Receber</flux:heading>
            <div class="flex gap-2">
                <flux:button variant="outline" icon="exclamation-triangle" color="red" href="{{ route('faturamento.inadimplencia') }}">
                    Ver Inadimplência
                </flux:button>
            </div>
        </div>

        <flux:card>
            <form action="{{ route('faturamento.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select name="status" label="Filtrar por Status">
                    <option value="">Todos</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="parcial" {{ request('status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="vencido" {{ request('status') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </flux:select>
                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" class="w-full">Filtrar</flux:button>
                </div>
            </form>
        </flux:card>

        <flux:card class="p-0 overflow-hidden">
            <flux:table :paginate="$faturas">
                <flux:table.columns>
                    <flux:table.column>Fatura</flux:table.column>
                    <flux:table.column>Vencimento</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Origem</flux:table.column>
                    <flux:table.column>Parcela</flux:table.column>
                    <flux:table.column>Valor Total</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Ações</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($faturas as $fatura)
                        <flux:table.row :key="$fatura->id">
                            <flux:table.cell class="font-medium">#{{ $fatura->id }}</flux:table.cell>
                            
                            <flux:table.cell>
                                <span class="{{ $fatura->status == 'vencido' ? 'text-red-600 font-bold' : '' }}">
                                    {{ $fatura->data_vencimento->format('d/m/Y') }}
                                </span>
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                <a href="{{ route('faturamento.cliente', $fatura->cliente_id) }}" class="text-accent hover:underline">
                                    {{ $fatura->cliente->nome }}
                                </a>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($fatura->orcamento_id)
                                    <a href="{{ route('orcamentos.gerenciar', $fatura->orcamento_id) }}" class="text-xs text-neutral-500 hover:underline">
                                        Orc #{{ $fatura->orcamento_id }}
                                    </a>
                                @else
                                    <span class="text-xs text-neutral-500">Ped #{{ $fatura->pedido_id }}</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $fatura->numero_parcela }} / {{ $fatura->total_parcelas }}
                            </flux:table.cell>

                            <flux:table.cell class="font-bold text-neutral-900 dark:text-white">
                                R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @php
                                    $color = match($fatura->status) {
                                        'pago' => 'lime',
                                        'vencido' => 'red',
                                        'parcial' => 'orange',
                                        'cancelado' => 'zinc',
                                        default => 'indigo'
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm" variant="solid">{{ ucfirst($fatura->status) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button variant="ghost" size="sm" icon="eye" href="#" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-12 text-neutral-500">
                                Nenhuma fatura encontrada.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts.app>
