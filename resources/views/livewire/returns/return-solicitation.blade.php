<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                Solicitar Devolução {{ $pedidoId ? "- Pedido #$pedidoId" : "" }}
            </h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                @if($pedidoId)
                    Selecione os itens e quantidades que deseja devolver do pedido selecionado.
                @else
                    Selecione um pedido aprovado para iniciar o processo de devolução.
                @endif
            </p>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl dark:bg-red-900/20 dark:border-red-800/50 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl dark:bg-green-900/20 dark:border-green-800/50 dark:text-green-400">
            {{ session('message') }}
        </div>
    @endif

    @if($pedidoId)
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-100 dark:border-zinc-700 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-800/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                        <flux:icon icon="shopping-bag" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-neutral-900 dark:text-neutral-100">Itens do Pedido #{{ $pedidoId }}</h3>
                        <p class="text-xs text-neutral-500">{{ $pedido->cliente->nome }}</p>
                    </div>
                </div>
                <flux:button href="{{ route('devolucoes.solicitar_index') }}" variant="ghost" size="sm">Trocar Pedido</flux:button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                    <thead class="bg-neutral-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold text-neutral-500 uppercase tracking-wider w-16">Sel.</th>
                            <th class="px-4 py-3 text-left font-bold text-neutral-500 uppercase tracking-wider">Produto</th>
                            <th class="px-4 py-3 text-right font-bold text-neutral-500 uppercase tracking-wider">Qtd. Orig.</th>
                            <th class="px-4 py-3 text-center font-bold text-neutral-500 uppercase tracking-wider">Qtd. Devolver</th>
                            <th class="px-4 py-3 text-right font-bold text-neutral-500 uppercase tracking-wider">Valor Unit.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($items as $item)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50 @if($selectedItems[$item['id']]['selected']) bg-primary-50/30 dark:bg-primary-900/10 @endif">
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model.live="selectedItems.{{ $item['id'] }}.selected" class="rounded border-zinc-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-zinc-800 dark:border-zinc-600">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900 dark:text-white">{{ $item['nome'] }}</div>
                                    <div class="text-[10px] text-neutral-500 uppercase tracking-tight">SKU: {{ $item['sku'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-neutral-600 dark:text-neutral-400">
                                    {{ number_format($item['quantidade'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" step="1" max="{{ $item['quantidade'] }}" 
                                           wire:model.defer="selectedItems.{{ $item['id'] }}.quantity" 
                                           class="w-24 px-2 py-1 text-center rounded-lg border-zinc-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-zinc-900 dark:border-zinc-600 border @if(!$selectedItems[$item['id']]['selected']) opacity-40 @endif"
                                           @if(!$selectedItems[$item['id']]['selected']) disabled @endif>
                                </td>
                                <td class="px-4 py-3 text-right text-neutral-600 dark:text-neutral-400 font-medium">
                                    R$ {{ number_format($item['valor_unitario'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-zinc-50/50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button href="{{ route('devolucoes.solicitar_index') }}" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="submit" variant="primary">Enviar Solicitação</flux:button>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-100 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="font-bold text-neutral-900 dark:text-neutral-100">Selecione um Pedido Aprovado</h3>
                    <div class="w-full sm:max-w-xs relative">
                        <flux:input wire:model.live.debounce.300ms="orderSearch" placeholder="Buscar por #Pedido ou Cliente..." icon="magnifying-glass" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                    <thead class="bg-neutral-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold text-neutral-500 uppercase tracking-wider"># Pedido</th>
                            <th class="px-4 py-3 text-left font-bold text-neutral-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-right font-bold text-neutral-500 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-right font-bold text-neutral-500 uppercase tracking-wider">Valor Total</th>
                            <th class="px-4 py-3 text-center font-bold text-neutral-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50 cursor-pointer" wire:click="selectPedido({{ $order->id }})">
                                <td class="px-4 py-3 font-bold text-primary-600 dark:text-primary-400">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-neutral-900 dark:text-white font-medium">{{ $order->cliente->nome }}</td>
                                <td class="px-4 py-3 text-right text-neutral-500">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right text-neutral-900 dark:text-neutral-100 font-bold">R$ {{ number_format($order->valor_total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <flux:button variant="ghost" size="sm" icon="arrow-right">Selecionar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-neutral-500">
                                    <flux:icon icon="exclamation-circle" class="w-10 h-10 mx-auto mb-2 opacity-20" />
                                    <p>Nenhum pedido aprovado encontrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recentOrders instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentOrders->hasPages())
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50">
                    {{ $recentOrders->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
