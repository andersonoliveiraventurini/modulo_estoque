<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Bloco 1: Pesquisa de Produto Resultante --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.arrow-down-tray class="size-5 text-indigo-600" />
            <flux:heading size="lg">1. Pesquisar Produto Resultante</flux:heading>
        </div>
        
        <livewire:lista-produto-orcamento 
            :showStock="true" 
            :showDiscount="false" 
            :showPrice="false" 
            context="blocok.descartes-manager"
            purpose="resultante"
            key="search-resultante"
        />
    </div>

    {{-- Bloco 2: Pesquisa de Produto Descartado --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.trash class="size-5 text-indigo-600" />
            <flux:heading size="lg">2. Pesquisar Produto Descartado</flux:heading>
        </div>
        
        <livewire:lista-produto-orcamento 
            :showStock="true" 
            :showDiscount="false" 
            :showPrice="false" 
            context="blocok.descartes-manager"
            purpose="descartado"
            key="search-descartado"
        />
    </div>

    {{-- Bloco 3: Formulário de Registro --}}
    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.plus-circle class="size-5 text-indigo-600" />
            <flux:heading size="lg">3. Registrar Descarte / Produção</flux:heading>
        </div>

        <form wire:submit="add" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Produto Resultante Selecionado</flux:label>
                    <flux:input wire:model="search_produto" readonly placeholder="Selecione na lista 1 acima..." />
                    @error('produto_id') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:label>Produto Descartado Selecionado</flux:label>
                    <flux:input wire:model="search_descartado" readonly placeholder="Selecione na lista 2 acima..." />
                    @error('produto_descartado_id') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <flux:label>Quantidade Descartada</flux:label>
                    <flux:input type="number" step="0.01" wire:model="quantidade_descarte" placeholder="0.00" />
                    @error('quantidade_descarte') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:label>Unidade</flux:label>
                    <flux:input wire:model="unidade_medida_descarte" />
                    @error('unidade_medida_descarte') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                <flux:button type="submit" variant="filled" color="indigo">
                    Registrar Descarte
                </flux:button>
            </div>
        </form>
    </div>

    {{-- Bloco 4: Listagem de Registrados --}}
    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
            <h2 class="font-bold text-neutral-800 dark:text-white uppercase text-xs tracking-widest">Descartes Registrados no Período</h2>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Resultante</flux:table.column>
                <flux:table.column>Descartado</flux:table.column>
                <flux:table.column>Qtd.</flux:table.column>
                <flux:table.column align="end">Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($descartes as $item)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="text-sm">
                                <span class="font-bold block">{{ $item->produto?->sku }}</span>
                                <span class="text-neutral-500">{{ $item->produto?->nome }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-sm">
                                <span class="font-bold block text-red-600">{{ $item->produtoDescartado?->sku }}</span>
                                <span class="text-neutral-500">{{ $item->produtoDescartado?->nome }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ number_format($item->quantidade_descarte, 3, ',', '.') }} {{ $item->unidade_medida_descarte }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button size="sm" variant="ghost" icon="trash" color="red" wire:click="remove({{ $item->id }})" wire:confirm="Tem certeza?" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-8 text-neutral-400 italic">Nenhum descarte registrado ainda.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $descartes->links() }}
        </div>
    </div>
</div>
