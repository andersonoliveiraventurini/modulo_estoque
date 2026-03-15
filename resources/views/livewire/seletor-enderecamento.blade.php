<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Armazém</label>
        <select wire:model.live="armazem_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">Selecione o Armazém</option>
            @foreach($armazens as $az)
                <option value="{{ $az->id }}">{{ $az->nome }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Corredor</label>
        <select wire:model.live="corredor_id" {{ empty($corredores) ? 'disabled' : '' }} class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ empty($corredores) ? 'opacity-50 cursor-not-allowed' : '' }}">
            <option value="">Selecione o Corredor</option>
            @foreach($corredores as $cr)
                <option value="{{ $cr->id }}">{{ $cr->nome }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posição</label>
        <select wire:model.live="posicao_id" {{ empty($posicoes) ? 'disabled' : '' }} class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ empty($posicoes) ? 'opacity-50 cursor-not-allowed' : '' }}">
            <option value="">Selecione a Posição</option>
            @foreach($posicoes as $ps)
                <option value="{{ $ps->id }}">{{ $ps->nome }}</option>
            @endforeach
        </select>
    </div>
</div>
