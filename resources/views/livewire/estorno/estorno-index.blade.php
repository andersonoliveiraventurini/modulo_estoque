<div>
    <flux:heading size="xl" level="1">Gestão de Estornos</flux:heading>
    
    <div class="mt-4 mb-6 flex flex-col md:flex-row gap-4 mb-4">
        <flux:select wire:model.live="status" placeholder="Todos os Status" class="w-full md:w-64">
            <flux:select.option value="pendente">Pendente</flux:select.option>
            <flux:select.option value="aprovado">Aprovado</flux:select.option>
            <flux:select.option value="rejeitado">Rejeitado</flux:select.option>
            <flux:select.option value="concluido">Concluído</flux:select.option>
        </flux:select>
        
        <flux:input wire:model.live.debounce.500ms="solicitante" placeholder="Buscar por Solicitante" icon="magnifying-glass" class="w-full md:w-64" />
    </div>

    @if (session('success'))
        <div class="mb-4 text-green-600 dark:text-green-300 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4 rounded text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->has('geral'))
        <div class="mb-4 text-red-600 dark:text-red-300 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 rounded text-sm">{{ $errors->first('geral') }}</div>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Solicitante</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Valor</flux:table.column>
            <flux:table.column>Forma</flux:table.column>
            <flux:table.column>Data</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($estornos as $estorno)
            <flux:table.row>
                <flux:table.cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        {{ $estorno->solicitante->name }}
                        @if($estorno->isPendente() && $estorno->solicitante_id === auth()->id())
                            <flux:badge size="sm" color="zinc">Você</flux:badge>
                        @endif
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    @if($estorno->isPendente())
                        <flux:badge color="yellow">Pendente</flux:badge>
                    @elseif($estorno->isAprovado())
                        <flux:badge color="lime">Aprovado</flux:badge>
                    @elseif($estorno->isRejeitado())
                        <flux:badge color="red">Rejeitado</flux:badge>
                    @elseif($estorno->isConcluido())
                        <flux:badge color="blue">Concluído</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="font-semibold">
                    R$ {{ number_format($estorno->valor, 2, ',', '.') }}
                </flux:table.cell>

                <flux:table.cell class="uppercase text-xs" tooltip="{{ $estorno->forma_estorno_detalhe }}">
                    {{ $estorno->forma_estorno }}
                </flux:table.cell>

                <flux:table.cell class="text-xs text-zinc-500 whitespace-nowrap">
                    {{ $estorno->created_at->format('d/m/Y H:i') }}
                </flux:table.cell>

                <flux:table.cell>
                    @if($estorno->isAprovado() && auth()->user()->can('conclude', $estorno))
                        <flux:button size="sm" variant="primary" wire:click="concluir({{ $estorno->id }})">
                            Concluir
                        </flux:button>
                    @elseif($estorno->isPendente() && auth()->user()->can('approve', $estorno))
                        <flux:button size="sm" variant="subtle" class="text-red-600 dark:text-red-400" href="{{ route('estornos.approval') }}">
                            Julgar (Aprovar/Rejeitar)
                        </flux:button>
                    @endif
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $estornos->links() }}
    </div>
</div>
