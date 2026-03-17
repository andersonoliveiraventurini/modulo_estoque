<div>
    <!-- Resumo de Limites -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-xl border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Limite Total (Boleto + Carteira)</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">
                R$ {{ number_format($cliente->limite_total, 2, ',', '.') }}
            </p>
        </div>
        <div class="p-4 rounded-xl border-orange-200 bg-orange-50 shadow-sm dark:border-orange-900/40 dark:bg-orange-900/10">
            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Boletos/Pedidos em Aberto</p>
            <p class="mt-2 text-2xl font-bold text-orange-700 dark:text-orange-300">
                R$ {{ number_format($cliente->limite_utilizado, 2, ',', '.') }}
            </p>
        </div>
        <div class="p-4 rounded-xl border-green-200 bg-green-50 shadow-sm dark:border-green-900/40 dark:bg-green-900/10">
            <p class="text-sm font-medium text-green-600 dark:text-green-400">Limite Disponível</p>
            <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                R$ {{ number_format($cliente->limite_disponivel, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Boletos em Aberto -->
    <div class="mb-8">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Boletos em Aberto (Próximos Vencimentos)</h3>
        </div>
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Ref. (Orç/ Ped)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Valor Pago / Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Vencimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($faturasAberto as $fatura)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-300">
                                @if($fatura->orcamento_id) Orç #{{ $fatura->orcamento_id }} @endif
                                @if($fatura->pedido_id) Ped #{{ $fatura->pedido_id }} @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-orange-600 dark:text-orange-400">
                                R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm {{ $fatura->isAtrasada() ? 'font-bold text-red-600 dark:text-red-400' : 'text-neutral-900 dark:text-neutral-300' }}">
                                {{ \Carbon\Carbon::parse($fatura->data_vencimento)->format('d/m/Y') }}
                                @if($fatura->isAtrasada()) <span class="ml-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold uppercase leading-5 text-red-800 dark:bg-red-900 dark:text-red-200">Atrasado</span> @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold uppercase text-neutral-500 dark:text-neutral-400">
                                {{ $fatura->status }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-neutral-500">Nenhum boleto ou fatura em aberto.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($faturasAberto->hasPages())
                <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                    {{ $faturasAberto->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Histórico de Pagamentos -->
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Movimentação: Pedidos Pagos</h3>
    </div>
    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Referência</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Valor Pago</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Formato / Meio de Pgt.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse($pagamentos as $pag)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-300">
                            {{ \Carbon\Carbon::parse($pag->data_pagamento)->format('d/m/Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-300">
                            @if($pag->orcamento_id) Orç #{{ $pag->orcamento_id }} @endif
                            @if($pag->pedido_id) Ped #{{ $pag->pedido_id }} @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-green-600 dark:text-green-400">
                            R$ {{ number_format($pag->valor_pago, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-xs text-neutral-600 dark:text-neutral-400">
                            <div class="flex flex-wrap gap-1">
                                @if($pag->formas->count() > 0)
                                    @foreach($pag->formas as $forma)
                                        <span class="inline-flex items-center rounded-md bg-neutral-100 px-2 py-1 font-medium text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                                            {{ $forma->condicaoPagamento->nome ?? 'Outro' }} (R$ {{ number_format($forma->valor, 2, ',', '.') }})
                                        </span>
                                    @endforeach
                                @elseif($pag->metodos->count() > 0)
                                    @foreach($pag->metodos as $metodo)
                                        <span class="inline-flex items-center rounded-md bg-neutral-100 px-2 py-1 font-medium text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                                            {{ $metodo->metodo->nome ?? 'Outro' }} (R$ {{ number_format($metodo->valor, 2, ',', '.') }})
                                        </span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-neutral-500">Nenhum registro de pagamento pago foi encontrado para este cliente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($pagamentos->hasPages())
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $pagamentos->links() }}
            </div>
        @endif
    </div>
</div>
