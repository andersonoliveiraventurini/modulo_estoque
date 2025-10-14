<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <x-flash-messages />
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
