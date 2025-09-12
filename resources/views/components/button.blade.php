@props(['variant' => 'primary'])

@php
$base = "inline-flex items-center gap-1 px-3 py-2 rounded-lg font-medium transition";

$variants = [
    'primary' => "$base bg-primary-600 text-gray-100 hover:bg-primary-700 dark:bg-primary-400 dark:text-gray-900 dark:hover:bg-primary-300",
    'secondary' => "$base bg-gray-200 text-gray-900 hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600",
    'success'   => "$base bg-green-600 text-white hover:bg-green-700 dark:bg-green-400 dark:text-gray-900 dark:hover:bg-green-300",
    'danger'    => "$base bg-red-600 text-white hover:bg-red-700 dark:bg-red-400 dark:text-gray-900 dark:hover:bg-red-300",
];
@endphp

<button {{ $attributes->merge(['class' => $variants[$variant]]) }}>
    {{ $slot }}
</button>
