<x-layouts.app :title="__('Solicitações de Pagamento')">
    <div class="flex w-full flex-1 flex-col gap-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl" level="1">Liberação de Pagamentos</flux:heading>
            <flux:button variant="outline" icon="check-badge" href="{{ route('solicitacoes-pagamento.aprovadas') }}" wire:navigate>
                Ver Aprovadas
            </flux:button>
        </div>

        <flux:card>
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Data</flux:table.column>
                    <flux:table.column>Orçamento</flux:table.column>
                    <flux:table.column>Cliente</flux:table.column>
                    <flux:table.column>Solicitante</flux:table.column>
                    <flux:table.column>Ação</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($solicitacoes as $solicitacao)
                        <flux:table.row>
                            <flux:table.cell>{{ $solicitacao->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell>#{{ str_pad($solicitacao->orcamento_id, 5, '0', STR_PAD_LEFT) }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-zinc-900 dark:text-white">
                                    {{ $solicitacao->orcamento->cliente->nome ?? 'Cliente não informado' }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $solicitacao->solicitante->name ?? 'Sistema' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="filled" icon="magnifying-glass" href="{{ route('solicitacoes-pagamento.aprovar', $solicitacao->orcamento_id) }}" wire:navigate>
                                    Avaliar
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <flux:icon.check-circle class="size-8 text-green-500" />
                                    <span>Nenhuma solicitação de pagamento pendente.</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $solicitacoes->links() }}
            </div>
        </flux:card>
    </div>
</x-layouts.app>
