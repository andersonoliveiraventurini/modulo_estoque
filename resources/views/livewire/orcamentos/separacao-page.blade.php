<div class="mx-auto max-w-7xl px-4 py-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Separação do Orçamento #{{ $orcamento->id }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Cliente: {{ optional($orcamento->cliente)->nome }} • Workflow: {{ $orcamento->workflow_status ?? '—' }}
        </p>
    </div>

    @if (session('success'))
        <div
            class="mb-3 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @error('*')
        <div
            class="mb-3 rounded border border-rose-300 bg-rose-50 px-3 py-2 text-rose-800 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-200">
            {{ $message }}
        </div>
    @enderror

    @if (!$batch)
        <div class="rounded-lg border border-dashed p-6 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
            <p class="text-gray-700 dark:text-gray-200 mb-4">
                Nenhum lote de separação em andamento.
            </p>
            <button wire:click="iniciarSeparacao"
                class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">
                Iniciar Separação
            </button>
        </div>
    @else
        <div class="rounded-lg border p-4 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
            {{-- ... dentro do <div class="rounded-lg border ..."> --}}

            <div class="flex items-center justify-between mb-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Lote #{{ $batch->id }} • Status: {{ $batch->status }} • Início:
                    {{ optional($batch->started_at)->format('d/m/Y H:i') }}
                </div>
                <div class="flex flex-col items-end">
                    <button wire:click="concluirLote"
                        class="inline-flex items-center px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-black text-sm">
                        Concluir Lote
                    </button>
                    {{-- ADICIONAR ESTE BLOCO DE ERRO --}}
                    @error('batch')
                        <div class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- O resto da sua tabela continua aqui... --}}

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Produto</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Solicitada</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Separada</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Localização</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Estoque</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach ($batch->items as $it)
                            @php
                                $p = $it->produto;
                                $reservado = (float) \App\Models\EstoqueReserva::where('produto_id', $p->id)
                                    ->where('status', 'ativa')
                                    ->sum('quantidade');
                                $dispo = (float) ($p->estoque_atual ?? 0) - $reservado;
                                $risco = $dispo - (float) $it->qty_solicitada < (float) ($p->estoque_minimo ?? 0);
                            @endphp
                            <tr wire:key="row-{{ $it->id }}">
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ $p->nome }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $p->sku ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ rtrim(rtrim(number_format($it->qty_solicitada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col gap-2">
                                        {{-- CORREÇÃO AQUI: wire:model.lazy para wire:model.defer --}}
                                        <div class="flex flex-wrap items-center gap-2">
                                            <input type="number" step="1" min="0"
                                                wire:model.defer="inputs.{{ $it->id }}.qty"
                                                class="w-28 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />

                                            <input type="text" placeholder="Motivo (se não separar)"
                                                wire:model.defer="inputs.{{ $it->id }}.motivo"
                                                class="w-64 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />

                                            <label
                                                class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                                                <input type="checkbox"
                                                    wire:model.defer="inputs.{{ $it->id }}.inconsistencia"
                                                    class="rounded border-gray-300 dark:border-gray-600">
                                                Inconsistência
                                            </label>

                                            <input type="text" placeholder="Obs inconsistência"
                                                wire:model.defer="inputs.{{ $it->id }}.obs"
                                                class="w-56 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />

                                            <button wire:click="salvarItem({{ $it->id }})"
                                                class="px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-black text-sm">
                                                Salvar
                                            </button>
                                        </div>

                                        @if ($it->separado_por_id)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Separado por #{{ $it->separado_por_id }} em
                                                {{ optional($it->separado_em)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                        @if ($it->motivo_nao_separado)
                                            <div class="text-xs text-amber-600 dark:text-amber-400">
                                                Motivo: {{ $it->motivo_nao_separado }}
                                            </div>
                                        @endif
                                        @if ($it->inconsistencia_reportada)
                                            <div class="text-xs text-rose-600 dark:text-rose-400">
                                                Inconsistência: {{ $it->inconsistencia_obs }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $it->localizacao ?? '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs
                                        {{ $risco ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' }}">
                                        Disp: {{ rtrim(rtrim(number_format($dispo, 3, ',', '.'), '0'), ',') }} • Mín:
                                        {{ $p->estoque_minimo ?? 0 }}
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Status item: {{ $it->status }}
                                    </div>
                                </td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endif
</div>
