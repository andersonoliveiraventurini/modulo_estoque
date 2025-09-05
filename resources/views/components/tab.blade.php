@props(['name', 'label'])

<div x-show="active === '{{ $name }}'" x-cloak data-tab-name="{{ $name }}" data-tab-label="{{ $label }}">
    {{ $slot }}
</div>