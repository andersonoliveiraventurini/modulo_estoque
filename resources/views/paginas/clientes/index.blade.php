<x-layouts.app :title="__('Listagem clientes')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <livewire:lista-cliente />
        </div>
    </div>
</x-layouts.app>
