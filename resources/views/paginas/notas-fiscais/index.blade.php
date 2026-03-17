<x-layouts.app :title="__('Notas Fiscais')">
    <div class="flex w-full flex-1 flex-col gap-4">
        <flux:heading size="xl" level="1">Notas Fiscais</flux:heading>

        <flux:card>
            <div class="flex flex-col items-center justify-center p-8 text-center">
                <flux:icon.document-text class="size-12 text-zinc-400 mb-4" />
                <flux:heading size="lg" class="mb-2">Ainda não tem notas geradas</flux:heading>
                <flux:subheading>As notas fiscais geradas no sistema aparecerão aqui.</flux:subheading>
            </div>
        </flux:card>
    </div>
</x-layouts.app>
