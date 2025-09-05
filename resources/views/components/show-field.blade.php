@props(['label', 'value' => '-', 'class' => ''])

<div {{ $attributes->merge(['class' => "p-4 border rounded-xl dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-800 $class"]) }}>
    <p class="text-xs text-neutral-500">{{ $label }}</p>
    <p class="text-base font-medium text-neutral-900 dark:text-neutral-100">
        {{ $value ?: '-' }}
    </p>
</div>
