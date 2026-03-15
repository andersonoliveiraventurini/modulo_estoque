<x-layouts.app :title="__('Inadimplência')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Relatório de Inadimplência</flux:heading>
            <flux:badge color="red" size="lg" variant="solid">Total Vencido: R$ {{ number_format($totalVencido, 2, ',', '.') }}</flux:badge>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fatura</flux:table.column>
                    <flux:table.column>Vencimento</flux:table.column>
                    <flux:table.column>Dias em Atraso</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Valor</flux:table.column>
                    <flux:table.column align="end">Ações</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($faturas as $fatura)
                        <flux:table.row :key="$fatura->id">
                            <flux:table.cell class="font-medium text-red-600">#{{ $fatura->id }}</flux:table.cell>
                            
                            <flux:table.cell class="font-bold">
                                {{ $fatura->data_vencimento->format('d/m/Y') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge color="red" variant="ghost">
                                    {{ $fatura->data_vencimento->diffInDays(now()) }} dias
                                </flux:badge>
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $fatura->cliente->nome }}</span>
                                    <span class="text-xs text-neutral-500">{{ $fatura->cliente->documento ?? 'Sem documento' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="font-bold text-red-600">
                                R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button variant="ghost" size="sm" icon="phone" title="Contatar Cliente" />
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('faturamento.cliente', $fatura->cliente_id) }}" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-12 text-neutral-500">
                                <flux:icon.check-circle class="mx-auto size-12 text-lime-500 mb-2 opacity-50" />
                                Nenhuma fatura vencida no momento. Excelente!
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts.app>
