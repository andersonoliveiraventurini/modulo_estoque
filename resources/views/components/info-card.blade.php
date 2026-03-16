@props(['label', 'value', 'class' => ''])

<div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col justify-center">
    <dt class="text-sm font-medium text-neutral-500 dark:text-neutral-400">{{ $label }}</dt>
    <dd class="mt-1 text-2xl tracking-tight {{ $class }}">{{ $value }}</dd>
</div>
