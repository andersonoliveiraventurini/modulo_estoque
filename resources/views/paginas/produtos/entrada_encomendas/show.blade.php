<x-layouts.app :title="__('Entrada #' . $entradaEncomenda->id)">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">Entrada de Encomenda</p>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">#{{ $entradaEncomenda->id }}</h2>
                    <p class="text-sm text-zinc-500 mt-1">
                        Cotação: <a href="{{ route('consulta_preco.show_grupo', $entradaEncomenda->grupo_id) }}"
                                    class="font-medium text-blue-600 hover:underline">#{{ $entradaEncomenda->grupo_id }}</a>
                        &nbsp;·&nbsp;
                        Cliente: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->cliente->nome_fantasia ?? '—' }}</span>
                    </p>
                    <p class="text-sm text-zinc-500">
                        Recebido por: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->recebedor->name }}</span>
                        em <span class="font-medium">{{ $entradaEncomenda->data_recebimento->format('d/m/Y') }}</span>
                    </p>
                    @if ($entradaEncomenda->destinatario)
                        <p class="text-sm text-zinc-500">
                            Entregue para: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->destinatario->name }}</span>
                            @if ($entradaEncomenda->data_entrega)
                                em <span class="font-medium">{{ $entradaEncomenda->data_entrega->format('d/m/Y') }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-3">
                    @php
                        $statusMap = [
                            'Recebido parcialmente' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                            'Recebido completo'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                            'Entregue'              => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusMap[$entradaEncomenda->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                        {{ $entradaEncomenda->status }}
                    </span>

                    {{-- Botão só aparece quando ainda há itens parciais --}}
                    @if ($entradaEncomenda->status === 'Recebido parcialmente')
                        <a href="{{ route('entrada_encomendas.complementar', $entradaEncomenda->id) }}">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                                Registrar Entrada Complementar
                            </x-button>
                        </a>
                    @endif
                </div>
            </div>

            @if ($entradaEncomenda->observacao)
                <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-sm text-zinc-600 dark:text-zinc-400">
                    <strong>Observação:</strong> {{ $entradaEncomenda->observacao }}
                </div>
            @endif
        </div>

        {{-- Itens --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Itens desta Entrada</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-right">Qtd Solicitada</th>
                        <th class="px-4 py-3 text-right">Qtd Recebida</th>
                        <th class="px-4 py-3 text-right">Pendente nesta entrada</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Observação</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($entradaEncomenda->itens as $item)
                        @php $pendente = $item->quantidadePendente(); @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition">
                            <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $item->consultaPreco->descricao }}
                                @if ($item->consultaPreco->cor)
                                    <span class="text-zinc-400 text-xs"> · {{ $item->consultaPreco->cor->nome }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-zinc-600">{{ number_format($item->quantidade_solicitada, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-zinc-700 dark:text-zinc-300">{{ number_format($item->quantidade_recebida, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right {{ $pendente > 0 ? 'text-amber-600 font-semibold' : 'text-zinc-400' }}">
                                {{ $pendente > 0 ? number_format($pendente, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->recebido_completo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <x-heroicon-o-check-circle class="w-3 h-3" /> Completo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        <x-heroicon-o-clock class="w-3 h-3" /> Parcial
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 text-xs italic">{{ $item->observacao ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('entrada_encomendas.aprovadas') }}">
                <x-button size="sm" variant="primary">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Voltar ao Painel
                </x-button>
            </a>
            <a href="{{ route('entrada_encomendas.index') }}">
                <x-button size="sm" variant="secondary">Ver todas as entradas</x-button>
            </a>
        </div>
    </div>
</x-layouts.app>