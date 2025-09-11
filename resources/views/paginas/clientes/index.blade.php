<x-layouts.app :title="__('Listagem clientes')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Clientes <flux:badge color="lime" inset="top bottom">Quantidade</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os clientes.</flux:text>
                </div>
            </div>

            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Bloqueados <flux:badge color="lime" inset="top bottom">Quantidade</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os clientes bloqueados.</flux:text>
                </div>
            </div>
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Vendas <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos as vendas.</flux:text>
                </div>
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
           <livewire:lista-cliente />
        </div>        
    </div>
</x-layouts.app>
