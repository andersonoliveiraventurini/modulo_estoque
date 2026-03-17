<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Bloco 1: Pesquisa de Produtos --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.magnifying-glass class="size-5 text-indigo-600" />
            <flux:heading size="lg">1. Pesquisar Insumo</flux:heading>
        </div>
        
        <livewire:lista-produto-orcamento 
            :showStock="true" 
            :showDiscount="false" 
            :showPrice="false" 
            context="blocok.insumos-manager" 
        />
    </div>

    {{-- Bloco 2: Formulário de Registro --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.plus-circle class="size-5 text-indigo-600" />
            <flux:heading size="lg">2. Registrar Quantidade</flux:heading>
        </div>

        <form wire:submit="add" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <flux:label>Produto Selecionado</flux:label>
                <flux:input wire:model="search_produto" readonly placeholder="Selecione um produto na lista acima..." />
                @error('produto_id') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <flux:label>Quantidade</flux:label>
                <flux:input type="number" step="0.01" wire:model="quantidade" placeholder="0.00" />
                @error('quantidade') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2">
                <div class="flex-1">
                    <flux:label>Unidade</flux:label>
                    <flux:input wire:model="unidade_medida" />
                </div>
                <flux:button type="submit" variant="filled" color="indigo" class="mt-auto">
                    Registrar
                </flux:button>
            </div>
        </form>
    </div>

    {{-- Bloco 3: Listagem de Registrados --}}
    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
            <h2 class="font-bold text-neutral-800 dark:text-white uppercase text-xs tracking-widest">Insumos Registrados no Período</h2>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Produto</flux:table.column>
                <flux:table.column>Quantidade</flux:table.column>
                <flux:table.column align="end">Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($insumos as $item)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="text-sm">
                                <span class="font-bold block">{{ $item->produto?->sku }}</span>
                                <span class="text-neutral-500">{{ $item->produto?->nome }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ number_format($item->quantidade, 3, ',', '.') }} {{ $item->unidade_medida }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button size="sm" variant="ghost" icon="trash" color="red" wire:click="remove({{ $item->id }})" wire:confirm="Tem certeza?" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center py-8 text-neutral-400 italic">Nenhum insumo registrado ainda.</flux:cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $insumos->links() }}
        </div>
    </div>
</div>
