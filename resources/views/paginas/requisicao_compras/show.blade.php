<x-layouts.app :title="'Detalhes da Requisição #{{ $requisicaoCompra->id }}'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Requisição #{{ $requisicaoCompra->id }}</h1>
            <div class="flex gap-2">
                @if($requisicaoCompra->status == 'aprovada' && !$requisicaoCompra->pedidoCompra()->exists())
                    <form action="{{ route('requisicao_compras.gerar_pedido', $requisicaoCompra->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 shadow-sm transition flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-4 h-4" /> Gerar Pedido de Compra
                        </button>
                    </form>
                @elseif($requisicaoCompra->pedidoCompra()->exists())
                    <a href="{{ route('pedido_compras.show', $requisicaoCompra->pedidoCompra->id) }}" class="rounded-lg bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-200 shadow-sm transition flex items-center gap-2 border border-emerald-200">
                        <x-heroicon-o-check-badge class="w-4 h-4" /> Pedido Gerado: #{{ $requisicaoCompra->pedidoCompra->id }}
                    </a>
                @endif

                @if($requisicaoCompra->status == 'pendente')
                    @can('aprovar requisicao')
                    <form action="{{ route('requisicao_compras.aprovar', $requisicaoCompra->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 shadow-sm transition">
                            Aprovar Nível {{ $requisicaoCompra->nivel_aprovacao }}
                        </button>
                    </form>
                    
                    <button type="button" onclick="abrirModalRejeicao()" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 shadow-sm transition">
                        Rejeitar
                    </button>
                    @endcan
                @endif
                <a href="{{ route('requisicao_compras.index') }}" class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-900">Voltar</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="col-span-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-800 dark:text-white">Itens Solicitados</h2>
                <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                    <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                        <tr>
                            <th class="px-6 py-3">Produto/Descrição</th>
                            <th class="px-6 py-3">ID/SKU</th>
                            <th class="px-6 py-3">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($requisicaoCompra->itens as $item)
                            <tr>
                                <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $item->produto ? $item->produto->nome : $item->descricao_livre }}
                                </td>
                                <td class="px-6 py-4">{{ $item->produto ? $item->produto->sku : '-' }}</td>
                                <td class="px-6 py-4">{{ $item->quantidade }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-700 flex justify-end">
                    <p class="text-lg font-bold text-neutral-800 dark:text-white">
                        Total Estimado: R$ {{ number_format($requisicaoCompra->valor_estimado, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-800 dark:text-white">Informações Gerais</h2>
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500 text-neutral-500">Solicitante</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $requisicaoCompra->solicitante->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500">Data</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $requisicaoCompra->data_requisicao->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500">Status</span>
                        <span class="inline-block rounded-full px-2 py-1 text-xs font-semibold 
                            {{ $requisicaoCompra->status == 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $requisicaoCompra->status == 'aprovada' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $requisicaoCompra->status == 'rejeitada' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($requisicaoCompra->status) }}
                        </span>
                    </div>
                    @if($requisicaoCompra->observacao)
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500">Observações</span>
                        <p class="text-sm text-neutral-900 dark:text-white">{{ $requisicaoCompra->observacao }}</p>
                    </div>
                    @endif
                    @if($requisicaoCompra->aprovador)
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500">Aprovador</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $requisicaoCompra->aprovador->name }} em {{ $requisicaoCompra->aprovado_em->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @endif
                    @if($requisicaoCompra->aprovador)
                    <div>
                        <span class="block text-xs font-semibold uppercase text-neutral-500">Aprovador Final</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $requisicaoCompra->aprovador->name }} em {{ $requisicaoCompra->aprovado_em->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($requisicaoCompra->rejeitadoPor)
                    <div class="bg-red-50 p-2 rounded border border-red-100 dark:bg-red-900/20 dark:border-red-800/50">
                        <span class="block text-xs font-semibold uppercase text-red-600 dark:text-red-400">Rejeitado por</span>
                        <span class="text-sm text-red-800 dark:text-red-300 font-medium">{{ $requisicaoCompra->rejeitadoPor->name }} em {{ $requisicaoCompra->rejeitado_em->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                @if($requisicaoCompra->aprovacoes_json)
                <div class="mt-6 border-t pt-6 border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-sm font-bold text-neutral-800 dark:text-white mb-4 flex items-center gap-2">
                        <x-heroicon-o-check-badge class="w-4 h-4 text-green-500" /> Histórico de Aprovações
                    </h3>
                    <ul class="space-y-4">
                        @foreach($requisicaoCompra->aprovacoes_json as $aprovacao)
                        <li class="flex gap-3 items-start">
                            <div class="bg-green-100 dark:bg-green-900/30 p-1 rounded-full mt-1">
                                <x-heroicon-o-check class="w-3 h-3 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-neutral-700 dark:text-neutral-300">Nível {{ $aprovacao['nivel'] }} - {{ $aprovacao['user_name'] }}</p>
                                <p class="text-[10px] text-neutral-500">{{ \Carbon\Carbon::parse($aprovacao['data'])->format('d/m/Y H:i') }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de Rejeição --}}
    <div id="modal-rejeicao" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-neutral-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-neutral-100 dark:border-neutral-700">
                <h3 class="text-lg font-bold text-neutral-800 dark:text-white">Justificar Rejeição</h3>
                <p class="text-sm text-neutral-500">Informe o motivo para rejeitar esta requisição.</p>
            </div>
            <form action="{{ route('requisicao_compras.rejeitar', $requisicaoCompra->id) }}" method="POST">
                @csrf
                <div class="p-6">
                    <textarea name="motivo" required rows="4" 
                        class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        placeholder="Ex: Produto não está mais em linha, valor acima do mercado..."></textarea>
                </div>
                <div class="p-6 bg-neutral-50 dark:bg-neutral-900/50 flex gap-3 justify-end">
                    <button type="button" onclick="fecharModalRejeicao()" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-500 shadow-lg">Confirmar Rejeição</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function abrirModalRejeicao() {
            document.getElementById('modal-rejeicao').classList.remove('hidden');
            document.getElementById('modal-rejeicao').classList.add('flex');
        }
        function fecharModalRejeicao() {
            document.getElementById('modal-rejeicao').classList.remove('flex');
            document.getElementById('modal-rejeicao').classList.add('hidden');
        }
    </script>
    @endpush
</x-layouts.app>
