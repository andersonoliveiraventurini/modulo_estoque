<div class="flex flex-col h-full bg-white dark:bg-neutral-900">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex justify-between items-center bg-gray-50 dark:bg-neutral-800">
        <div>
            <h2 class="text-xl font-bold text-neutral-800 dark:text-white">Conferência de Recebimento - Pedido #{{ $pedido->numero_pedido ?: $pedido->id }}</h2>
            <p class="text-sm text-neutral-500">Fornecedor: {{ $pedido->fornecedor->nome_fantasia }}</p>
        </div>
        @if(!$conferencia)
            <button wire:click="iniciarConferencia" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition">
                Iniciar Conferência Técnica
            </button>
        @else
            <div class="flex gap-3">
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold self-center">EM ANDAMENTO</span>
                <button wire:click="concluir" wire:confirm="Deseja finalizar a conferência? Isso gerará a movimentação de entrada no estoque." class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition">
                    Concluir Recebimento
                </button>
            </div>
        @endif
    </div>

    @if($conferencia)
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        @foreach($conferencia->itens as $item)
        <div class="bg-white dark:bg-neutral-800 rounded-xl border {{ $item->status == 'ok' ? 'border-green-200' : ($item->status == 'divergente' ? 'border-red-200' : 'border-neutral-200') }} p-4 shadow-sm transition-all">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <h4 class="font-bold text-neutral-900 dark:text-white text-lg">{{ $item->produto->nome }}</h4>
                    <p class="text-xs text-neutral-500">SKU: {{ $item->produto->sku }} | Esperado: <span class="font-bold text-indigo-600">{{ $item->qty_separada }}</span></p>
                    
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-neutral-500 mb-1">Qtd Recebida</label>
                            <input type="number" wire:model.blur="inputs.{{ $item->id }}.qty" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white text-sm" placeholder="Ex: 10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-neutral-500 mb-1">Motivo/Obs</label>
                            <input type="text" wire:model.blur="inputs.{{ $item->id }}.motivo" class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white text-sm" placeholder="Ex: Avaria, Falta...">
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-64 border-l border-neutral-100 dark:border-neutral-700 pl-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-neutral-500 uppercase">Fotos da Carga</span>
                        <label class="cursor-pointer text-indigo-600 hover:text-indigo-500">
                            <x-heroicon-o-camera class="w-5 h-5" />
                            <input type="file" wire:model="novasFotos.{{ $item->id }}" multiple class="hidden">
                        </label>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        @foreach($item->fotos as $foto)
                            <div class="relative group">
                                <img src="{{ asset('storage/'.$foto->path) }}" class="w-12 h-12 object-cover rounded border">
                                <button wire:click="removerFoto({{ $foto->id }})" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5 hidden group-hover:block">
                                    <x-heroicon-s-x-mark class="w-3 h-3" />
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button wire:click="salvarItem({{ $item->id }})" class="mt-4 w-full bg-neutral-800 text-white py-2 rounded-lg text-xs font-bold hover:bg-neutral-700 transition">
                        Confirmar Item
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="flex-1 flex items-center justify-center p-12 text-center">
        <div class="max-w-md">
            <x-heroicon-o-clipboard-document-check class="w-16 h-16 text-neutral-300 mx-auto mb-4" />
            <h3 class="text-lg font-bold text-neutral-700 dark:text-neutral-300">Pronto para Recebimento</h3>
            <p class="text-neutral-500 mt-2">Clique no botão superior para iniciar a conferência física dos itens deste pedido.</p>
        </div>
    </div>
    @endif
</div>
