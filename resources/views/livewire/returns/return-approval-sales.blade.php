<div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Aprovação de Devoluções - Supervisor de Vendas</h2>
        <p class="text-sm text-neutral-500">Revise as solicitações de devolução pendentes de faturamento.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="space-y-6">
        @forelse($pendingReturns as $return)
            <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden">
                <div class="bg-neutral-50 dark:bg-zinc-800 px-4 py-3 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-neutral-900 dark:text-white">Pedido #{{ $return->order_id }}</span>
                        <span class="ml-2 text-sm text-neutral-500">{{ $return->customer->razao_social }}</span>
                    </div>
                    <span class="text-xs text-neutral-500">Solicitado em {{ $return->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="p-4">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-neutral-500 border-b dark:border-neutral-700">
                                <th class="text-left py-2">Produto</th>
                                <th class="text-right py-2">Qtd. Solicitada</th>
                                <th class="text-right py-2">Valor Unit.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                                <tr class="border-b last:border-0 dark:border-neutral-700">
                                    <td class="py-2 text-neutral-900 dark:text-white">{{ $item->product->nome }}</td>
                                    <td class="py-2 text-right text-neutral-600 dark:text-neutral-400 font-medium">{{ number_format($item->quantity_requested, 2, ',', '.') }}</td>
                                    <td class="py-2 text-right text-neutral-600 dark:text-neutral-400">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-end gap-3">
                        @if($selectedReturnId === $return->id)
                            <div class="flex-1">
                                <input type="text" wire:model.defer="refusalReason" placeholder="Motivo da recusa..." class="w-full text-sm rounded-md border-neutral-300 dark:bg-zinc-800 dark:border-neutral-700">
                                @error('refusalReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <button wire:click="refuse({{ $return->id }})" class="px-3 py-1.5 text-xs font-bold text-white bg-red-600 rounded-md hover:bg-red-700">Confirmar Recusa</button>
                            <button wire:click="$set('selectedReturnId', null)" class="px-3 py-1.5 text-xs font-bold text-neutral-600 bg-neutral-100 rounded-md">Cancelar</button>
                        @else
                            <button wire:click="$set('selectedReturnId', {{ $return->id }})" class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-600 rounded-md hover:bg-red-50">Recusar</button>
                            <button wire:click="approve({{ $return->id }})" class="px-3 py-1.5 text-xs font-bold text-white bg-green-600 rounded-md hover:bg-green-700">Aprovar Devolução</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-neutral-500">
                Não há devoluções pendentes de aprovação de vendas.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $pendingReturns->links() }}
    </div>
</div>
