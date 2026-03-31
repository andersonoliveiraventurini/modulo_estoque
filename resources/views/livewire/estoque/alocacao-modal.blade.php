<div x-data="{ 
    show: @entangle('show'), 
    totalItem: @entangle('totalQuantity'),
    get totalAllocated() {
        let sum = 0;
        document.querySelectorAll('.allocation-qty').forEach(el => {
            sum += parseFloat(el.value || 0);
        });
        return sum;
    }
}" x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4" x-cloak>
    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-full max-w-3xl shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Alocação de Estoque</h3>
            <button @click="show = false" class="text-gray-400 hover:text-red-600">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
        </div>

        <div class="mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-3 rounded-lg flex justify-between items-center">
            <div>
                <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">{{ $productName }}</p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400">Total do Item: <span class="font-bold">{{ number_format($totalQuantity, 3, ',', '.') }}</span></p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold" :class="totalAllocated == totalItem ? 'text-green-600' : 'text-red-600'">
                    Alocado: <span x-text="totalAllocated.toFixed(3).replace('.', ',')"></span>
                </p>
                <p class="text-xs" :class="totalAllocated == totalItem ? 'text-green-500' : 'text-red-500'">
                    Faltando: <span x-text="(totalItem - totalAllocated).toFixed(3).replace('.', ',')"></span>
                </p>
            </div>
        </div>

        <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
            @foreach($allocations as $index => $allocation)
                <div class="flex items-center gap-4 bg-gray-50 dark:bg-neutral-800 p-3 rounded-lg border border-gray-200 dark:border-neutral-700">
                    <div class="flex-1">
                        <p class="text-xs font-bold uppercase text-gray-500">Endereço</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $allocation['posicao_nome'] }}</p>
                    </div>
                    <div class="w-32">
                        <label class="text-xs font-bold uppercase text-gray-500">Quantidade</label>
                        <input type="number" step="0.001" 
                            wire:model.defer="allocations.{{ $index }}.quantity"
                            class="allocation-qty w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded p-1 text-sm"
                            @input="totalAllocated = parseFloat($event.target.value) || 0"
                        >
                    </div>
                    <button wire:click="removeAllocation({{ $index }})" class="text-red-500 hover:text-red-700 mt-4">
                        <x-heroicon-o-trash class="w-5 h-5" />
                    </button>
                </div>
            @endforeach
            
            @if(empty($allocations))
                <div class="text-center py-8 text-gray-400">
                    <x-heroicon-o-map-pin class="w-12 h-12 mx-auto mb-2 opacity-20" />
                    <p>Nenhuma alocação definida. Adicione um endereço abaixo.</p>
                </div>
            @endif
        </div>

        <div class="mt-6 border-t pt-4 border-gray-200 dark:border-neutral-700">
            <div class="flex gap-2">
                <select id="posicao-selector" class="flex-1 border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded p-2 text-sm">
                    <option value="">Selecione um Endereço para Adicionar</option>
                    @foreach($availablePositions as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->armazem->nome }} - {{ $pos->corredor->nome }} - {{ $pos->nome }}</option>
                    @endforeach
                </select>
                <button onclick="addPosicao()" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 text-sm">
                    Adicionar
                </button>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button wire:click="confirm" 
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="totalAllocated != totalItem">
                Confirmar Alocações
            </button>
            <button @click="show = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-800">
                Cancelar
            </button>
        </div>
    </div>

    <script>
        function addPosicao() {
            const select = document.getElementById('posicao-selector');
            if (select.value) {
                @this.addAllocation(select.value);
                select.value = '';
            }
        }
    </script>
</div>
