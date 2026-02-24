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
    // Já existentes
    'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400 focus:ring-blue-300',
    'secondary' => 'bg-blue-500 hover:bg-blue-600 text-white dark:bg-blue-600 dark:hover:bg-blue-500 focus:ring-blue-500',
    'danger'    => 'bg-red-500 hover:bg-red-600 text-white dark:bg-red-600 dark:hover:bg-red-500 focus:ring-red-500',

    // Novas opções
    'success'   => 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-400 focus:ring-green-300',
    'warning'   => 'bg-yellow-500 text-white hover:bg-yellow-600 dark:bg-yellow-400 dark:hover:bg-yellow-300 focus:ring-yellow-300',
    'info'      => 'bg-cyan-500 text-white hover:bg-cyan-600 dark:bg-cyan-400 dark:hover:bg-cyan-300 focus:ring-cyan-300',
    'dark'      => 'bg-gray-800 text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 focus:ring-gray-500',
    'light'     => 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 focus:ring-gray-300',
    'purple'    => 'bg-purple-600 text-white hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-400 focus:ring-purple-300',
    'pink'      => 'bg-pink-500 text-white hover:bg-pink-600 dark:bg-pink-400 dark:hover:bg-pink-300 focus:ring-pink-300',
    'indigo'    => 'bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 focus:ring-indigo-300',
    'orange'    => 'bg-orange-500 text-white hover:bg-orange-600 dark:bg-orange-400 dark:hover:bg-orange-300 focus:ring-orange-300',
    'teal'      => 'bg-teal-600 text-white hover:bg-teal-700 dark:bg-teal-500 dark:hover:bg-teal-400 focus:ring-teal-300',

    // Variantes "outline" (transparentes com borda)
    'outline-primary' => 'border border-blue-600 text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900 focus:ring-blue-300',
    'outline-danger'  => 'border border-red-500 text-red-500 hover:bg-red-50 dark:border-red-400 dark:text-red-400 dark:hover:bg-red-900 focus:ring-red-300',
    'outline-success' => 'border border-green-600 text-green-600 hover:bg-green-50 dark:border-green-400 dark:text-green-400 dark:hover:bg-green-900 focus:ring-green-300',

    // Variantes "ghost" (sem fundo, só muda ao hover)
    'ghost'         => 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 focus:ring-gray-300',
    'ghost-danger'  => 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900 focus:ring-red-300',
    'ghost-success' => 'text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900 focus:ring-green-300',
];
@endphp

<button {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
    {{ $slot }}
</button>
