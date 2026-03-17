<div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
    <flux:heading size="lg" class="mb-4">Estoque por Endereço Físico</flux:heading>

    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Armazém</flux:table.column>
                <flux:table.column>Corredor</flux:table.column>
                <flux:table.column>Posição</flux:table.column>
                <flux:table.column align="end">Saldo Total</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($estoquePorEndereco as $item)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $item->armazem ?? 'N/A' }}</flux:table.cell>
                        <flux:table.cell>{{ $item->corredor ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $item->posicao ?? '-' }}</flux:cell>
                        <flux:table.cell align="end">
                            <flux:badge color="indigo" variant="subtle" size="sm">
                                {{ number_format($item->saldo, 2, ',', '.') }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-8 text-neutral-500">
                            Nenhuma movimentação para endereços físicos encontrada.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
