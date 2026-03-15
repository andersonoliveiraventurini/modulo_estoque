<x-layouts.app title="Pedido de Compra #{{ $pedidoCompra->id }}">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-indigo-500" />
                        Pedido de Compra #{{ $pedidoCompra->id }}
                    </h2>
                    <div class="flex gap-3">
                        @if(in_array($pedidoCompra->status, ['aguardando', 'parcialmente_recebido']))
                            <form action="{{ route('pedido-compras.conferencia.iniciar', $pedidoCompra->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 font-bold shadow-sm">Iniciar Recebimento</button>
                            </form>
                        @elseif($pedidoCompra->status == 'em_conferencia')
                            <a href="{{ route('pedido-compras.conferencia.show', $pedidoCompra->id) }}" class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-md hover:bg-yellow-600 font-bold shadow-sm">Continuar Conferência</a>
                        @endif

                        <a href="{{ route('pedido_compras.edit', $pedidoCompra->id) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Editar</a>
                        <form action="{{ route('pedido_compras.destroy', $pedidoCompra->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este pedido? O preço de custo dos produtos será revertido ao valor anterior.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">Deletar</button>
                        </form>
                        <a href="{{ route('pedido_compras.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">&larr; Voltar</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-4 text-green-700 bg-green-100 p-3 rounded text-sm">{{ session('success') }}</div>
                @endif

                {{-- Informações Gerais --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Fornecedor</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($pedidoCompra->fornecedor)->nome_fantasia }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nº Pedido</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $pedidoCompra->numero_pedido ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Data do Pedido</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $pedidoCompra->data_pedido->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Previsão Entrega</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($pedidoCompra->previsao_entrega)?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                        <p class="font-semibold capitalize text-gray-900 dark:text-gray-100">{{ str_replace('_', ' ', $pedidoCompra->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Condição Pgto</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ optional($pedidoCompra->condicaoPagamento)->nome ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Forma Pgto / Parcelas</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $pedidoCompra->forma_pagamento_descricao ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                        <p class="font-bold text-lg text-gray-900 dark:text-gray-100">R$ {{ number_format($pedidoCompra->valor_total ?? 0, 2, ',', '.') }}</p>
                    </div>
                    @if($pedidoCompra->arquivo_pedido)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Arquivo do Pedido</p>
                        <a href="{{ Storage::url($pedidoCompra->arquivo_pedido) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 underline">
                            <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Baixar Arquivo
                        </a>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Criado por</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ optional($pedidoCompra->usuario)->name ?? 'Sistema' }}</p>
                    </div>
                    @if($pedidoCompra->editor_usuario_id)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Última edição por</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ optional($pedidoCompra->editor)->name }} 
                            <span class="text-xs font-normal text-gray-500">em {{ $pedidoCompra->editado_em->format('d/m/Y H:i') }}</span>
                        </p>
                    </div>
                    @endif
                    @if($pedidoCompra->observacao)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Observações</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $pedidoCompra->observacao }}</p>
                    </div>
                    @endif
                </div>

                {{-- Itens --}}
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 pt-4 border-t border-gray-200 dark:border-neutral-700">Itens do Pedido</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qtd.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Vl. Unit.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Vl. Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Obs.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                            @foreach($pedidoCompra->itens as $item)
                            <tr>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ optional($item->produto)->nome ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $item->descricao_livre ?: '-' }}</td>
                                <td class="px-4 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right text-gray-900 dark:text-gray-100">R$ {{ number_format($item->valor_unitario ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($item->valor_total ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $item->observacao ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
