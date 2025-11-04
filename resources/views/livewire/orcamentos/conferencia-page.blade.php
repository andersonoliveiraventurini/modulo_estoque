<div class="mx-auto max-w-7xl px-4 py-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Conferência do Orçamento #{{ $orcamento->id }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Workflow: {{ $orcamento->workflow_status ?? '—' }}
        </p>
    </div>

    @if (session('success'))
        <div
            class="mb-3 rounded border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-2 text-emerald-800 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (!$conferencia)
        <div class="rounded-lg border border-dashed p-6 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
            <p class="text-gray-700 dark:text-gray-200 mb-4">
                Nenhuma conferência em andamento.
            </p>
            @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                <button wire:click="iniciarConferencia"
                    class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">
                    Iniciar Conferência
                </button>
            @endif
        </div>
    @else
        <div class="rounded-lg border p-4 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Conferência #{{ $conferencia->id }} • Status: {{ $conferencia->status }}
                </div>
                @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                    <button wire:click="concluir"
                        class="inline-flex items-center px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white">
                        Concluir Conferência
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Produto</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Separada</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Conferida</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Status</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach ($conferencia->itens as $it)
                            @php $p = $it->produto; @endphp
                            <tr wire:key="conf-row-{{ $it->id }}">
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ $p->nome }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $p->sku ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ rtrim(rtrim(number_format($it->qty_separada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col gap-2">
                                        <input type="number" step="0.001" min="0"
                                            wire:model.lazy="inputs.{{ $it->id }}.qty"
                                            class="w-28 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />

                                        <input type="text" placeholder="Motivo da divergência (se houver)"
                                            wire:model.lazy="inputs.{{ $it->id }}.motivo"
                                            class="w-80 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />

                                        <div class="flex items-center gap-2">
                                            @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                                                <button wire:click="salvarItem({{ $it->id }})"
                                                    class="px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">
                                                    Salvar
                                                </button>
                                            @endif
                                            @if ($it->status === 'divergente')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">
                                                    Divergente
                                                    ({{ rtrim(rtrim(number_format($it->divergencia, 3, ',', '.'), '0'), ',') }})
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                    OK
                                                </span>
                                            @endif
                                        </div>

                                        @if ($it->conferido_por_id)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Conferido por #{{ $it->conferido_por_id }} em
                                                {{ optional($it->conferido_em)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endif
</div>
