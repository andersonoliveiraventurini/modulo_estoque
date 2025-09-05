@props(['default'])

@php
    // Coletar informações das tabs dos filhos
    $tabs = [];
    $slotContent = $slot->toHtml();
    
    // Extrair informações das tabs usando regex
    preg_match_all('/data-tab-name="([^"]+)" data-tab-label="([^"]+)"/', $slotContent, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $tabs[] = [
            'name' => $match[1],
            'label' => $match[2]
        ];
    }
@endphp

<div x-data="{ active: '{{ $default }}' }" class="w-full">
    <!-- Botões das abas -->
    <div class="border-b border-zinc-200 dark:border-zinc-700 mb-4 flex space-x-2">
        @foreach($tabs as $tab)
            <button 
                @click="active = '{{ $tab['name'] }}'" 
                :class="active === '{{ $tab['name'] }}' 
                    ? 'border-primary-600 text-primary-600' 
                    : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300'"
                class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm"
                type="button"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <!-- Conteúdo das abas -->
    <div>
        {{ $slot }}
    </div>
</div>