{{-- DENTRO DE resources/views/vendor/flux/components/navlist/item.blade.php --}}

@props([
    'current' => false, // A prop que controla se o item está ativo
    'icon' => null,
    'icon-trailing' => null,
])

@php
    // Lógica de classes base para todos os itens
    $baseClasses = 'flex items-center gap-3 rounded-md p-2 text-sm font-medium transition-colors duration-200';

    // Classes para o item INATIVO
    $inactiveClasses = 'text-zinc-500 hover:bg-zinc-200 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200';

    // >>> ESTA É A LINHA CRÍTICA QUE PRECISAMOS MUDAR <<<
    // Classes para o item ATIVO (current)
    $activeClasses = 'bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400';
@endphp

{{-- O elemento 'a' (link) que usa essas classes --}}
<a {{ $attributes->class([$baseClasses, $current ? $activeClasses : $inactiveClasses]) }}>
    
    @if ($icon)
        <flux:icon :name="$icon" class="size-5" />
    @endif

    <span class="flex-1 truncate">{{ $slot }}</span>


</a>