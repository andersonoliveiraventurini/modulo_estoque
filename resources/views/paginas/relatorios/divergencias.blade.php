<x-layouts.app title="Divergências Logísticas">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-orange-500" />
                    Relatório de Divergências Logísticas
                </h2>
                <div class="text-sm text-gray-500">
                    Total de {{ count($divergencias) }} ocorrências
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Data</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Tipo</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Orçamento</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Produto</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Esperado</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Realizado</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Diferença</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Motivo / Mensagem</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Responsável</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($divergencias as $div)
                            @php
                                $diff = $div->qtd_real - $div->qtd_esperada;
                            @endphp
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $div->data->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $div->tipo == 'Separação' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                                        {{ $div->tipo }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('orcamentos.show', $div->orcamento_id) }}" class="underline">
                                        #{{ $div->orcamento_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ $div->produto_nome }}</span><br>
                                    <small class="text-gray-500 text-xs">{{ $div->produto_sku }}</small>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-300 font-bold text-center">
                                    {{ number_format($div->qtd_esperada, 0) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-center {{ $div->qtd_real < $div->qtd_esperada ? 'text-red-500 font-bold' : 'text-green-500 font-bold' }}">
                                    {{ number_format($div->qtd_real, 0) }}
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @if($diff > 0)
                                        <span class="text-green-600 dark:text-green-400 font-bold flex items-center gap-1">
                                            <x-heroicon-o-plus-circle class="w-4 h-4" />
                                            +{{ $diff }} (A mais)
                                        </span>
                                    @elseif($diff < 0)
                                        <span class="text-red-600 dark:text-red-400 font-bold flex items-center gap-1">
                                            <x-heroicon-o-minus-circle class="w-4 h-4" />
                                            {{ $diff }} (A menos)
                                        </span>
                                    @else
                                        <span class="text-gray-500">Ok</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs">
                                    {{ $div->motivo }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $div->responsavel }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Nenhuma divergência registrada no período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
