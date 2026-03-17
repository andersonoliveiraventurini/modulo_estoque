<x-layouts.app title="Descartes - Bloco K">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('blocok.index') }}">Bloco K</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Descartes</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Gerenciamento de Descartes</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Configure os produtos resultantes e descartados para o Bloco K.</p>
            </div>
        </div>


        <div class="grid grid-cols-1 gap-6">
            <livewire:blocok.descartes-manager />
        </div>
    </div>
</x-layouts.app>
