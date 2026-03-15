<x-layouts.app :title="__('Histórico Financeiro')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Histórico Financeiro: {{ $cliente->nome }}</flux:heading>
                <flux:text>{{ $cliente->documento }} | {{ $cliente->email }}</flux:text>
            </div>
            <flux:button variant="outline" icon="arrow-left" href="{{ route('faturamento.index') }}">Voltar</flux:button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                <flux:heading size="sm" class="mb-2">Total Recebido</flux:heading>
                <div class="text-3xl font-bold text-lime-600 dark:text-lime-400">R$ {{ number_format($stats['total_pago'], 2, ',', '.') }}</div>
            </div>
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                <flux:heading size="sm" class="mb-2">A Receber</flux:heading>
                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">R$ {{ number_format($stats['total_pendente'], 2, ',', '.') }}</div>
            </div>
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm ring-2 ring-red-500/20">
                <flux:heading size="sm" class="mb-2 text-red-600">Total Vencido</flux:heading>
                <div class="text-3xl font-bold text-red-600">R$ {{ number_format($stats['total_vencido'], 2, ',', '.') }}</div>
            </div>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Ref</flux:table.column>
                    <flux:table.column>Vencimento</flux:table.column>
                    <flux:table.column>Parcela</flux:table.column>
                    <flux:table.column>Valor</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Origem</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($faturas as $fatura)
                        <flux:table.row :key="$fatura->id">
                            <flux:table.cell>#{{ $fatura->id }}</flux:table.cell>
                            <flux:table.cell>{{ $fatura->data_vencimento->format('d/m/Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $fatura->numero_parcela }} / {{ $fatura->total_parcelas }}</flux:table.cell>
                            <flux:table.cell class="font-bold">R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($fatura->status) {
                                        'pago' => 'lime',
                                        'vencido' => 'red',
                                        'parcial' => 'orange',
                                        default => 'indigo'
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ ucfirst($fatura->status) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($fatura->orcamento_id)
                                    Orc #{{ $fatura->orcamento_id }}
                                @else
                                    Ped #{{ $fatura->pedido_id }}
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8 text-neutral-500">
                                Sem histórico financeiro registrado.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts.app>
