<x-layouts.app :title="__('Listagem de usuários')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Administradores <flux:badge color="lime" inset="top bottom">Quantidade</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os administradores.</flux:text>
                </div>
            </div>

            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Vendedores <flux:badge color="lime" inset="top bottom">Quantidade</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os vendedores.</flux:text>
                </div>
            </div>
            <div
                class="relative h-40 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center text-center">
                <div>
                    <flux:heading>
                        Usuários <flux:badge color="lime" inset="top bottom">Cadastrados</flux:badge>
                    </flux:heading>
                    <flux:text class="mt-2">Todos os usuários.</flux:text>
                </div>
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
           <livewire:lista-cores />
        </div>        
    </div>
</x-layouts.app>
