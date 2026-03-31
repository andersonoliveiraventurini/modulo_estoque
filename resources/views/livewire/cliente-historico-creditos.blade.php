<div>
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Créditos do Cliente (Devoluções, Trocos, Bonificações)</h3>
        
        <div class="flex items-center gap-3 bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-lg border border-blue-100 dark:border-blue-800">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Saldo Total Disponível:</span>
            <span class="text-xl font-bold text-blue-800 dark:text-blue-200">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</span>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($creditos as $credito)
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
                <!-- Header do Crédito -->
                <div class="flex flex-col border-b border-neutral-200 bg-neutral-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-700 dark:bg-zinc-800">
                    <div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $credito->tipo_descricao }}
                        </span>
                        <span class="ml-2 text-sm text-neutral-500 dark:text-neutral-400">
                            Gerado em {{ $credito->created_at->format('d/m/Y H:i') }}
                        </span>
                        @if($credito->origem_tipo && $credito->origem_id)
                            <span class="ml-2 text-sm text-neutral-500 dark:text-neutral-400">
                                (Ref: {{ ucfirst($credito->origem_tipo) }} #{{ $credito->origem_id }})
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-center gap-4 sm:mt-0">
                        <div class="text-sm">
                            <span class="text-neutral-500">Valor Original:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">R$ {{ number_format($credito->valor_original, 2, ',', '.') }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-neutral-500">Disponível:</span>
                            <span class="font-medium text-green-600 dark:text-green-400">R$ {{ number_format($credito->valor_disponivel, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detalhes e Movimentações -->
                <div class="p-4">
                    <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-300">
                        <strong>Motivo:</strong> {{ $credito->motivo_origem ?? 'Não informado' }}
                        <br>
                        <strong>Criado por:</strong> {{ $credito->usuarioCriacao->name ?? 'Sistema' }}
                    </p>

                    @if($credito->movimentacoes->count() > 0)
                        <h4 class="mb-2 text-sm font-semibold text-neutral-900 dark:text-white">Histórico de Uso (Abatimentos)</h4>
                        <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-700">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                                <thead class="bg-neutral-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-neutral-500">Data</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-neutral-500">Tipo</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-neutral-500">Valor Retirado</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-neutral-500">Ação / Referência</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    @foreach($credito->movimentacoes as $mov)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-3 py-2 text-sm text-neutral-900 dark:text-neutral-300">
                                                {{ $mov->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2 text-sm">
                                                @php
                                                    $isEntrada = in_array($mov->tipo_movimentacao, ['entrada', 'estorno', 'geracao_troco']);
                                                @endphp
                                                @if($isEntrada)
                                                    <span class="text-green-600 font-medium">{{ $mov->tipo_movimentacao_descricao }}</span>
                                                @else
                                                    <span class="text-red-600 font-medium">{{ $mov->tipo_movimentacao_descricao }}</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2 text-sm font-medium {{ $mov->tipo_movimentacao === 'entrada' || $mov->tipo_movimentacao === 'estorno' || $mov->tipo_movimentacao === 'geracao_troco' ? 'text-green-600' : 'text-red-600' }}">
                                                R$ {{ number_format($mov->valor_movimentado, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-neutral-600 dark:text-neutral-400">
                                                {{ $mov->motivo }}
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @if($mov->referencia_tipo === 'pagamento' && $mov->referencia_id)
                                                        <a href="{{ route('pagamentos.show', $mov->referencia_id) }}" class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10 hover:bg-indigo-100 transition-colors">Pagamento #{{ $mov->referencia_id }}</a>
                                                        @if($mov->pagamento?->orcamento_id)
                                                            <a href="{{ route('orcamentos.show', $mov->pagamento->orcamento_id) }}" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 hover:bg-blue-100 transition-colors">Orçamento #{{ $mov->pagamento->orcamento_id }}</a>
                                                        @endif
                                                    @elseif(($mov->referencia_tipo === 'orcamento' || $mov->referencia_tipo === 'pagamento') && $mov->referencia_id == 1)
                                                        {{-- Caso especial para corrigir visualmente o erro de ID legado --}}
                                                        <a href="{{ route('pagamentos.show', 1) }}" class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10 hover:bg-indigo-100 transition-colors">Pagamento #1</a>
                                                        <a href="{{ route('orcamentos.show', 29) }}" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 hover:bg-blue-100 transition-colors">Orçamento #29</a>
                                                    @elseif($mov->referencia_tipo === 'orcamento' && $mov->referencia_id)
                                                        <a href="{{ route('orcamentos.show', $mov->referencia_id) }}" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 hover:bg-blue-100 transition-colors">Orçamento #{{ $mov->referencia_id }}</a>
                                                    @elseif($mov->referencia_tipo === 'venda' && $mov->referencia_id)
                                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">Venda #{{ $mov->referencia_id }}</span>
                                                    @elseif($mov->referencia_id)
                                                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">{{ ucfirst($mov->referencia_tipo) }} #{{ $mov->referencia_id }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-xs text-neutral-400">Por: {{ $mov->usuario->name ?? 'Sistema' }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-neutral-300 p-4 text-center text-sm text-neutral-500 dark:border-neutral-700">
                            Nenhuma movimentação ou uso registrado para este crédito ainda.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
                <p class="text-neutral-500">Nenhum crédito cadastrado para este cliente até o momento.</p>
            </div>
        @endforelse
    </div>

    @if($creditos->hasPages())
        <div class="mt-4">
            {{ $creditos->links() }}
        </div>
    @endif
</div>
