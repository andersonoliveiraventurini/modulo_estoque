<x-layouts.app title="Insumos - Bloco K">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('blocok.index') }}">Bloco K</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Insumos</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Gerenciamento de Insumos</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Configure os insumos utilizados na produção para o Bloco K.</p>
            </div>
        </div>


        <div class="grid grid-cols-1 gap-6">
            <livewire:blocok.insumos-manager />
        </div>
    </div>
</x-layouts.app>
