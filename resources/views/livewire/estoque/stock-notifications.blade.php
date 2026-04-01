<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-zinc-100">Alertas e Notificações de Estoque</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-400">Alertas em tempo real sobre saldo no HUB e reposições necessárias.</p>
        </div>
        <flux:button wire:click="markAllAsRead" variant="ghost" icon="check-circle">Marcar todas como lidas</flux:button>
    </div>

    <div class="space-y-4">
        @forelse($alerts as $alert)
            <div @class([
                'p-4 rounded-xl border transition-all duration-200 flex items-start gap-4',
                'bg-white dark:bg-zinc-900 border-gray-100 dark:border-zinc-800 shadow-sm' => $alert->lida,
                'bg-rose-50 dark:bg-rose-950/20 border-rose-100 dark:border-rose-900 shadow-md ring-1 ring-rose-200 dark:ring-rose-800' => !$alert->lida && $alert->tipo === 'hub_zero',
                'bg-amber-50 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900 shadow-md ring-1 ring-amber-200 dark:ring-amber-800' => !$alert->lida && $alert->tipo === 'replenishment_needed',
                'bg-blue-50 dark:bg-blue-950/20 border-blue-100 dark:border-blue-900 shadow-md ring-1 ring-blue-200 dark:ring-blue-800' => !$alert->lida && $alert->tipo === 'pending_approval',
            ])>
                <div @class([
                    'p-2 rounded-full',
                    'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-400' => $alert->tipo === 'hub_zero',
                    'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400' => $alert->tipo === 'replenishment_needed',
                    'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' => $alert->tipo === 'pending_approval',
                ])>
                    <flux:icon icon="{{ $alert->tipo === 'hub_zero' ? 'exclamation-circle' : ($alert->tipo === 'replenishment_needed' ? 'arrow-path' : 'clock') }}" class="w-5 h-5" />
                </div>

                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <h4 @class([
                            'font-semibold',
                            'text-zinc-900 dark:text-white' => $alert->lida,
                            'text-rose-900 dark:text-rose-300' => !$alert->lida && $alert->tipo === 'hub_zero',
                            'text-amber-900 dark:text-amber-300' => !$alert->lida && $alert->tipo === 'replenishment_needed',
                            'text-blue-900 dark:text-blue-300' => !$alert->lida && $alert->tipo === 'pending_approval',
                        ])>
                            {{ $alert->tipo === 'hub_zero' ? 'Estoque HUB Zerado' : ($alert->tipo === 'replenishment_needed' ? 'Reposição Necessária' : 'Aprovação Pendente') }}
                        </h4>
                        <span class="text-xs text-zinc-500">{{ $alert->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $alert->mensagem }}</p>
                    
                    @if($alert->orcamento_id)
                        <div class="mt-2">
                            <flux:button href="{{ route('orcamentos.show', $alert->orcamento_id) }}" size="xs" variant="ghost">Ver Orçamento #{{ $alert->orcamento_id }}</flux:button>
                        </div>
                    @endif
                </div>

                @if(!$alert->lida)
                    <button wire:click="markAsRead({{ $alert->id }})" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                        <flux:icon icon="check" class="w-5 h-5" />
                    </button>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-zinc-500 border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-xl">
                <flux:icon icon="bell-slash" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                <p>Nenhum alerta pendente no momento.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $alerts->links() }}
        </div>
    </div>
</div>
