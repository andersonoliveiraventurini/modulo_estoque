<x-layouts.app :title="__('Orçamentos Conferidos')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Orçamentos Conferidos (Prontos p/ Financeiro)</flux:heading>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Orçamento</flux:table.column>
                    <flux:table.column>Enviado em</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Vendedor</flux:table.column>
                    <flux:table.column>Valor Total</flux:table.column>
                    <flux:table.column align="end">Ações</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($orcamentos as $orcamento)
                        <flux:table.row :key="$orcamento->id">
                            <flux:table.cell class="font-medium">#{{ $orcamento->id }}</flux:table.cell>
                            
                            <flux:table.cell>
                                {{ $orcamento->enviado_financeiro_em->format('d/m/Y H:i') }}
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                {{ $orcamento->cliente->nome }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $orcamento->vendedor->name ?? 'N/A' }}
                            </flux:table.cell>

                            <flux:table.cell class="font-bold">
                                R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('orcamentos.gerenciar', $orcamento->id) }}" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-12 text-neutral-500">
                                Nenhum orçamento conferido aguardando o financeiro.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts.app>
