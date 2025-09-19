@props([
    'variant' => 'secondary',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center gap-1.5 font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
    ];

    $variants = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400 focus:ring-blue-300',
        'secondary' => 'bg-blue-500 hover:bg-blue-600 text-white dark:bg-blue-600 dark:hover:bg-blue-500 focus:ring-blue-500',
        'danger' => 'bg-red-500 hover:bg-red-600 text-white dark:bg-red-600 dark:hover:bg-red-500 focus:ring-red-500',
    ];
@endphp

<button {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
    {{ $slot }}
</button>
