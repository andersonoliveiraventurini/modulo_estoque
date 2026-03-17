<div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Solicitar Devolução - Pedido #{{ $pedidoId }}</h2>
        <p class="text-sm text-neutral-500">Selecione os itens e quantidades que deseja devolver.</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-neutral-500">Selecionar</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-neutral-500">Produto</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-neutral-500">Qtd. Original</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-neutral-500">Qtd. Devolver</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-neutral-500">Preço Unit.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($items as $item)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectedItems.{{ $item['id'] }}.selected" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-neutral-900 dark:text-white">{{ $item['nome'] }}</div>
                            <div class="text-xs text-neutral-500">SKU: {{ $item['sku'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                            {{ number_format($item['quantidade'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" max="{{ $item['quantidade'] }}" 
                                   wire:model.defer="selectedItems.{{ $item['id'] }}.quantity" 
                                   class="w-24 rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-zinc-800 dark:border-neutral-700 border"
                                   @if(!$selectedItems[$item['id']]['selected']) disabled @endif>
                        </td>
                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                            R$ {{ number_format($item['valor_unitario'], 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end gap-4 mt-6">
        <a href="{{ route('historico.financeiro') }}" class="px-4 py-2 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-md shadow-sm hover:bg-neutral-50">
            Cancelar
        </a>
        <button wire:click="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Enviar Solicitação
        </button>
    </div>
</div>
