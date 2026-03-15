<x-layouts.app :title="__('Detalhes da Movimentação')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                         <x-heroicon-o-eye class="w-5 h-5" /> 
                        Detalhes da Movimentação #{{ $movimentacao->id }}
                    </h2>
                    <div class="flex items-center gap-4">
                        @php
                            $statusClasses = [
                                'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500',
                                'aprovado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-500',
                                'rejeitado' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-500',
                            ];
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-lg {{ $statusClasses[$movimentacao->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ strtoupper($movimentacao->status) }}
                        </span>

                        <div class="flex gap-2">
                            @if($movimentacao->status === 'pendente')
                                @can('aprovar movimentacao')
                                    <form action="{{ route('movimentacao.aprovar', $movimentacao->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm px-4 py-2 bg-emerald-600 text-white rounded-md font-semibold hover:bg-emerald-700 transition flex items-center gap-2">
                                            <x-heroicon-o-check-circle class="w-4 h-4" /> Aprovar
                                        </button>
                                    </form>
                                    <form action="{{ route('movimentacao.rejeitar', $movimentacao->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm px-4 py-2 bg-rose-600 text-white rounded-md font-semibold hover:bg-rose-700 transition flex items-center gap-2">
                                            <x-heroicon-o-x-circle class="w-4 h-4" /> Rejeitar
                                        </button>
                                    </form>
                                @endcan
                            @endif

                            <a href="{{ route('movimentacao.edit', $movimentacao->id) }}" class="text-sm px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                Editar
                            </a>
                            <a href="{{ route('movimentacao.index') }}" class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">
                                <x-heroicon-o-arrow-left class="w-4 h-4 inline" /> Voltar
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Visão Geral Card -->
                    <div class="bg-white dark:bg-neutral-800 p-5 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-4 border-b border-gray-100 dark:border-neutral-700 pb-2">
                            <x-heroicon-o-document-text class="w-5 h-5 text-indigo-500" /> Visão Geral
                        </h3>
                        
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    @if($movimentacao->tipo === 'entrada')
                                        <span class="text-green-600 dark:text-green-400 capitalize">{{ $movimentacao->tipo }}</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400 capitalize">{{ $movimentacao->tipo }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Criação</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movimentacao->created_at->format('d/m/Y H:i:s') }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data da Movimentação</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $movimentacao->data_movimentacao ? $movimentacao->data_movimentacao->format('d/m/Y') : $movimentacao->created_at->format('d/m/Y') }}
                                </dd>
                            </div>
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pedido Vinculado</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $movimentacao->pedido_id ? 'Pedido #' . $movimentacao->pedido_id : 'Nenhum' }}
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nota Fiscal</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movimentacao->nota_fiscal_fornecedor ?: '-' }}</dd>
                            </div>

                            @if($movimentacao->arquivo_nota_fiscal)
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Arquivo NF</dt>
                                <dd class="mt-1">
                                    <a href="{{ Storage::url($movimentacao->arquivo_nota_fiscal) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 underline hover:text-indigo-800">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Ver / Baixar NF
                                    </a>
                                </dd>
                            </div>
                            @endif
                            
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Romaneiro</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $movimentacao->romaneiro ?: '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Autoria Card -->
                    <div class="bg-white dark:bg-neutral-800 p-5 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-4 border-b border-gray-100 dark:border-neutral-700 pb-2">
                            <x-heroicon-o-user-group class="w-5 h-5 text-indigo-500" /> Autoria e Histórico
                        </h3>
                        
                        <dl class="grid grid-cols-1 gap-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Criado Por</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 truncate">
                                    {{ optional($movimentacao->usuario)->name ?: 'Usuário ' . $movimentacao->usuario_id }}
                                </dd>
                            </div>

                             @if($movimentacao->usuario_editou_id)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Edição Por</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 truncate">
                                    {{ optional($movimentacao->usuarioEditou)->name ?: 'Usuário ' . $movimentacao->usuario_editou_id }}
                                    <span class="text-xs text-neutral-400 ml-2">({{ $movimentacao->updated_at->format('d/m/Y H:i:s') }})</span>
                                </dd>
                                @if($movimentacao->resumo_edicao)
                                <dd class="mt-2 text-xs text-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-300 p-2 rounded border border-indigo-100 dark:border-indigo-800">
                                    <x-heroicon-o-information-circle class="w-4 h-4 inline" /> {{ $movimentacao->resumo_edicao }}
                                </dd>
                                @endif
                            </div>
                            @endif

                            @if($movimentacao->supervisor_id)
                            <div class="pt-2 border-t border-gray-100 dark:border-neutral-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Processado Por (Supervisor)</dt>
                                <dd class="mt-1 text-sm font-bold {{ $movimentacao->status === 'aprovado' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <x-heroicon-s-check-badge class="w-4 h-4 inline" /> {{ optional($movimentacao->supervisor)->name }}
                                    @if($movimentacao->aprovado_em)
                                        <span class="text-xs text-neutral-400 ml-2">em {{ $movimentacao->aprovado_em->format('d/m/Y H:i:s') }}</span>
                                    @endif
                                </dd>
                            </div>
                            @endif

                            @if($movimentacao->observacao)
                            <div class="pt-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Observações Extras</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-neutral-900 p-3 rounded-lg mt-2 border border-gray-100 dark:border-neutral-700 min-h-[60px]">
                                    {{ $movimentacao->observacao }}
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Lista de Produtos -->
                <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-8">
                    <h3 class="text-lg font-medium flex items-center gap-2 mb-4 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-archive-box class="w-5 h-5 text-indigo-500" />                             
                        Itens Afetados ({{ $movimentacao->itens->count() }})
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização/Obs.</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quant</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                                @foreach($movimentacao->itens as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ optional($item->produto)->nome ?: 'Produto ' . $item->produto_id }}
                                            <div class="text-xs text-gray-500 font-normal">SKU: {{ optional($item->produto)->sku ?: '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ optional($item->fornecedor)->nome_fantasia ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($item->endereco || $item->corredor || $item->posicao)
                                                <div>Armazém: <span class="text-gray-800 dark:text-gray-200">{{ $item->endereco ?: '-' }}</span></div>
                                                <div class="text-xs">Corredor: {{ $item->corredor ?: '-' }} | Posição: {{ $item->posicao ?: '-' }}</div>
                                            @else
                                                <span class="text-gray-400 italic">Não Informado</span>
                                            @endif
                                            
                                            @if($item->observacao)
                                                <div class="mt-1 text-xs text-indigo-600 dark:text-indigo-400">
                                                    <strong>Obs:</strong> {{ $item->observacao }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-gray-100">
                                            {{ number_format($item->quantidade, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                            {{ $item->valor_unitario ? 'R$ ' . number_format($item->valor_unitario, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                                            {{ $item->valor_total ? 'R$ ' . number_format($item->valor_total, 2, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-neutral-800 border-t-2 border-gray-200 dark:border-neutral-700">
                                <tr>
                                    <th colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Resumo Geral:</th>
                                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($movimentacao->itens->sum('quantidade'), 2, ',', '.') }}</th>
                                    <th></th>
                                    <th class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">R$ {{ number_format($movimentacao->itens->sum('valor_total'), 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
