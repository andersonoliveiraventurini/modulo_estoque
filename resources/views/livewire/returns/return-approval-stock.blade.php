<div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Conferência de Devoluções - Chefe de Estoque</h2>
        <p class="text-sm text-neutral-500">Valide os itens recebidos fisicamente para gerar o crédito ao cliente.</p>
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
                    <div class="text-right">
                        <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">Aprovado por Vendas: {{ $return->salesSupervisor->name ?? 'Supervisor' }}</span>
                    </div>
                </div>
                <div class="p-4">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-neutral-500 border-b dark:border-neutral-700">
                                <th class="text-left py-2">Produto</th>
                                <th class="text-right py-2">Qtd. Solicitada</th>
                                <th class="text-right py-2">Qtd. Recebida (OK)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $item)
                                <tr class="border-b last:border-0 dark:border-neutral-700">
                                    <td class="py-2 text-neutral-900 dark:text-white">{{ $item->product->nome }}</td>
                                    <td class="py-2 text-right text-neutral-500 italic">{{ number_format($item->quantity_requested, 2, ',', '.') }}</td>
                                    <td class="py-2 text-right">
                                        <input type="number" step="0.01" max="{{ $item->quantity_requested }}" 
                                               wire:model.defer="approvedQuantities.{{ $item->id }}" 
                                               class="w-20 text-right rounded border-neutral-300 dark:bg-zinc-800 dark:border-neutral-700">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-end gap-3">
                        <button onclick="let reason = prompt('Motivo da recusa:'); if(reason) @this.refuse({{ $return->id }}, reason)" class="px-3 py-1.5 text-xs font-bold text-red-600 border border-red-600 rounded-md hover:bg-red-50">Recusar Itens</button>
                        <button wire:click="approve({{ $return->id }})" class="px-3 py-1.5 text-xs font-bold text-white bg-primary-600 rounded-md hover:bg-primary-700">Validar e Gerar Crédito</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-neutral-500">
                Não há devoluções aguardando conferência física no estoque.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $pendingReturns->links() }}
    </div>
</div>
