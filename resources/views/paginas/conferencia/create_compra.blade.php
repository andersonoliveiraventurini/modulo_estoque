<x-layouts.app :title="'Conferência Pedido #{{ $pedido->numero_pedido ?: $pedido->id }}'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <livewire:compras.conferencia-compra :pedido="$pedido" />
        </div>
    </div>
</x-layouts.app>
