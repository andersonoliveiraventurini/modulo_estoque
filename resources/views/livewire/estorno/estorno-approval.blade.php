<div>
    <flux:heading size="xl" level="1" class="mb-6">Aprovação de Estornos Pendentes</flux:heading>

    @if (session('success'))
        <div class="mb-4 text-green-600 dark:text-green-300 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4 rounded text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->has('geral'))
        <div class="mb-4 text-red-600 dark:text-red-300 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 rounded text-sm">{{ $errors->first('geral') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($pendentes as $estorno)
            <div class="bg-white p-4 rounded border dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="text-sm text-zinc-500 mb-1">
                        Iniciado por <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $estorno->solicitante->name }}</span>
                        em {{ $estorno->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        R$ {{ number_format($estorno->valor, 2, ',', '.') }}
                    </div>
                    <div class="text-sm mt-1">
                        Forma esperada: <span class="uppercase font-semibold text-sky-600">{{ $estorno->forma_estorno }}</span> {{ $estorno->forma_estorno_detalhe ? "({$estorno->forma_estorno_detalhe})" : '' }}
                    </div>
                    <div class="text-sm mt-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded">
                        <strong>Motivo:</strong> {{ $estorno->motivo }}
                    </div>
                </div>

                <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
                    <flux:button variant="danger" wire:click="selecionarEstorno({{ $estorno->id }}, 'reject')">
                        Rejeitar
                    </flux:button>
                    <flux:button variant="primary" wire:click="selecionarEstorno({{ $estorno->id }}, 'approve')">
                        Aprovar
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="text-center p-8 bg-zinc-50 dark:bg-zinc-900 rounded border border-zinc-200 dark:border-zinc-700 text-zinc-500">
                Nenhum estorno pendente de aprovação (ou você é o solicitante de todos os que estão pendentes).
            </div>
        @endforelse
    </div>

    <!-- Modal de Interação (Rejeitar ou Aprovar e incluir obs) -->
    <flux:modal wire:model.live="showModal" variant="flyout" @close="$wire.cancelar()">
        @if($estornoEmAtendimento)
            <flux:heading size="lg">Confirmar {{ $acaoSelecionada === 'approve' ? 'Aprovação' : 'Rejeição' }}</flux:heading>
            <flux:subheading class="mb-4">Adicione uma observação caso aplicável. A observação é obrigatória para rejeições.</flux:subheading>

            <form wire:submit="processarAcao">
                <flux:textarea wire:model="observacao" label="Observação do Supervisor" rows="3" />
                
                <div class="mt-6 flex justify-end gap-2">
                    <flux:button wire:click="cancelar" variant="ghost">Cancelar</flux:button>
                    <flux:button type="submit" variant="{{ $acaoSelecionada === 'approve' ? 'primary' : 'danger' }}">
                        Confirmar
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
