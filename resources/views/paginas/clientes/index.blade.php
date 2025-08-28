<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Clientes <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os clientes.</flux:text>
                </div>
            </div>

            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Produtos <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os produtos.</flux:text>
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
           Teste dashboard - https://manus.im/app/HAQvKgM56tDASoN5cP0jFX 
           
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
