<x-layouts.app :title="__('Pagamentos')">
    <div class="flex w-full flex-1 flex-col gap-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl" level="1">Pagamentos</flux:heading>
        </div>

        <flux:card>
            <form action="{{ route('pagamentos.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <flux:input name="search" placeholder="Buscar por ID, Cliente..." value="{{ request('search') }}" icon="magnifying-glass" />
                </div>
                <div class="w-full md:w-48">
                    <flux:select name="status" placeholder="Status">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="pago" :selected="request('status') === 'pago'">Pago</flux:select.option>
                        <flux:select.option value="estornado" :selected="request('status') === 'estornado'">Estornado</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex gap-2">
                    <flux:input type="date" name="data_inicio" value="{{ request('data_inicio') }}" />
                    <flux:input type="date" name="data_fim" value="{{ request('data_fim') }}" />
                </div>
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">Filtrar</flux:button>
                    <flux:button href="{{ route('pagamentos.index') }}" variant="ghost">Limpar</flux:button>
                </div>
            </form>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>ID</flux:table.column>
                    <flux:table.column>Beneficiário</flux:table.column>
                    <flux:table.column>Valor</flux:table.column>
                    <flux:table.column>Data</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="right">Ações</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($pagamentos as $pagamento)
                        @php
                            $cliente = $pagamento->orcamento?->cliente ?? $pagamento->pedido?->cliente;
                        @endphp
                        <flux:table.row>
                            <flux:table.cell>#{{ $pagamento->id }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium text-neutral-900 dark:text-white">
                                    {{ $cliente?->razao_social ?? $cliente?->nome ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-neutral-500">
                                    @if($pagamento->orcamento_id) Orçamento #{{ $pagamento->orcamento_id }} @endif
                                    @if($pagamento->pedido_id) Pedido #{{ $pagamento->pedido_id }} @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="font-bold">R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>{{ $pagamento->data_pagamento->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell>
                                @if($pagamento->estornado)
                                    <flux:badge color="red" size="sm">Estornado</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm">Pago</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="right">
                                <flux:button variant="ghost" size="sm" icon="eye" href="{{ route('pagamentos.show', $pagamento->id) }}" wire:navigate />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8 text-neutral-500">
                                Nenhum pagamento encontrado.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $pagamentos->links() }}
            </div>
        </flux:card>
    </div>
</x-layouts.app>