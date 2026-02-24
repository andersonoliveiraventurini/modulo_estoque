{{-- resources/views/components/info-row.blade.php --}}
@props([
    'label'     => '',
    'value'     => '---',
    'highlight' => false,
])

<div class="flex items-baseline gap-1.5 min-w-0">
    <span class="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider flex-shrink-0">
        {{ $label }}:
    </span>
    <span @class([
        'text-sm min-w-0 truncate',
        'text-neutral-800 dark:text-neutral-200 font-medium' => !$highlight,
        'text-blue-600 dark:text-blue-400 font-semibold'     => $highlight,
    ])>
        {{ $value ?: '---' }}
    </span>
</div>