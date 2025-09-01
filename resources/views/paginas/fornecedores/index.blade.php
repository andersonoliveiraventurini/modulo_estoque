<x-layouts.app :title="__('Dashboard')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <livewire:lista-fornecedor />
        </div>
    </div>
</x-layouts.app>
