<x-layouts.app title="Detalhes da Falta">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Falta: {{ $falta->numero_falta }}
                    </h2>
                    <span class="px-2 py-1 text-xs font-bold bg-amber-100 text-amber-800 rounded dark:bg-amber-900 dark:text-amber-200">
                        Pendente
                    </span>
                </div>
                <a href="{{ route('faltas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Voltar para lista</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-neutral-700 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Informações Gerais</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Data de Registro:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $falta->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-50 dark:border-neutral-700 pt-2">
                            <dt class="text-sm text-gray-500">Cliente:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $falta->cliente?->nome ?? $falta->nome_cliente ?? 'Não informado' }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-50 dark:border-neutral-700 pt-2">
                            <dt class="text-sm text-gray-500">Vendedor:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $falta->vendedor?->user->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-50 dark:border-neutral-700 pt-2">
                            <dt class="text-sm text-gray-500">Registrado por:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $falta->user->name }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-neutral-700 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Observações</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-300 italic">
                        {{ $falta->observacao ?: 'Nenhuma observação registrada.' }}
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Itens em Falta</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU / Cód.</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qtd</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">V. Unitário</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Estimado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @foreach($falta->itens as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $item->produto?->sku ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $item->descricao_produto }}</td>
                                <td class="px-4 py-3 text-sm text-center font-medium">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-indigo-600 dark:text-indigo-400">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100 uppercase">Soma Total Estimada:</td>
                                <td class="px-4 py-3 text-right text-lg font-extrabold text-indigo-600 dark:text-indigo-400">R$ {{ number_format($falta->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-neutral-700 flex gap-3">
                <x-button type="button" onclick="window.print()" variant="ghost" class="text-xs">
                    <x-heroicon-o-printer class="w-4 h-4 mr-1" /> Imprimir Documento
                </x-button>
            </div>
        </div>
    </div>
</x-layouts.app>
