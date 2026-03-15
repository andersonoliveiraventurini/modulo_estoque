<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
                {{ __('Relatório de Inadimplência') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:faturas.relatorio-inadimplencia />
        </div>
    </div>
</x-app-layout>
