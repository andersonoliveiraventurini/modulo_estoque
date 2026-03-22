<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Confirmação de Retirada de Encomendas</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Registre a retirada física de produtos encomendados para atualizar o estoque definitivo.</p>
        </div>
        <div class="w-64">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por ID, Cliente ou CNPJ..." 
                icon="magnifying-glass"
            />
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <flux:table>
            <flux:columns>
                <flux:column>ID</flux:column>
                <flux:column>Cliente</flux:column>
                <flux:column>Vendedor</flux:column>
                <flux:column>Data Pagamento</flux:column>
                <flux:column>Itens</flux:column>
                <flux:column align="right">Ações</flux:column>
            </flux:columns>

            <flux:rows>
                @forelse ($encomendas as $enc)
                    <flux:row>
                        <flux:cell class="font-medium">#{{ $enc->id }}</flux:cell>
                        <flux:cell>
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $enc->cliente->nome }}</div>
                            <div class="text-xs text-zinc-500">{{ $enc->cliente->cnpj_formatado }}</div>
                        </flux:cell>
                        <flux:cell>{{ $enc->vendedor->name }}</flux:cell>
                        <flux:cell>{{ $enc->data_pagamento?->format('d/m/Y H:i') ?? 'N/A' }}</flux:cell>
                        <flux:cell>
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $enc->itens->count() }} itens
                            </span>
                        </flux:cell>
                        <flux:cell align="right">
                            <flux:button 
                                wire:click="confirmarRetirada({{ $enc->id }})" 
                                variant="primary" 
                                size="sm" 
                                icon="check"
                            >
                                Confirmar Retirada
                            </flux:button>
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="6" class="py-10 text-center text-zinc-500">
                            Nenhuma encomenda pronta para retirada encontrada.
                        </flux:cell>
                    </flux:row>
                @endforelse
            </flux:rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $encomendas->links() }}
        </div>
    </div>

    {{-- Modal de Confirmação --}}
    <flux:modal wire:model="confirmingId" class="max-w-md">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Confirmar Retirada</h3>
                <p class="mt-2 text-sm text-zinc-500">
                    Você está confirmando que o cliente está retirando fisicamente os produtos do Orçamento #{{ $confirmingId }}.
                </p>
                <div class="mt-4 rounded-lg bg-orange-50 p-3 text-xs text-orange-800 dark:bg-orange-950/30 dark:text-orange-300">
                    <p class="font-bold uppercase tracking-wider">Atenção:</p>
                    <p class="mt-1">Esta ação realizará a baixa definitiva do estoque dos produtos.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="processarRetirada" variant="primary">Confirmar e Baixar Estoque</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
