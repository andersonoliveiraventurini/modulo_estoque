<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    {{-- CABEÇALHO DA PÁGINA --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Separação do Orçamento #{{ $orcamento->id }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Cliente: {{ optional($orcamento->cliente)->nome }} • Workflow: <span
                class="font-semibold">{{ $orcamento->workflow_status ?? '—' }}</span>
        </p>
    </div>

    {{-- MENSAGENS DE FEEDBACK --}}
    @if (session('success'))
        <div
            class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            class="mb-4 rounded border border-rose-300 bg-rose-50 px-4 py-3 text-rose-800 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif
    @error('*')
        <div
            class="mb-4 rounded border border-rose-300 bg-rose-50 px-4 py-3 text-rose-800 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-200">
            {{ $message }}
        </div>
    @enderror

    {{-- SEÇÃO 1: LOTE DE SEPARAÇÃO ATIVO --}}
    @if ($batch)
        <div class="rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Lote de Separação Ativo
                            #{{ $batch->id }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status: <span
                                class="font-medium text-indigo-600 dark:text-indigo-400">{{ $batch->status }}</span> •
                            Início: {{ optional($batch->started_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Produto</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Solicitado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                style="min-width: 500px;">Ações de Separação</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estoque</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($batch->items as $it)
                            @php
                                $p = $it->produto;
                                $reservado = (float) \App\Models\EstoqueReserva::where('produto_id', $p->id)
                                    ->where('status', 'ativa')
                                    ->sum('quantidade');
                                $dispo = (float) ($p->estoque_atual ?? 0) - $reservado;
                                $risco = $dispo < (float) ($p->estoque_minimo ?? 0);
                            @endphp
                            <tr wire:key="row-{{ $it->id }}">
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                    <span class="font-medium">{{ $p->nome }}</span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $p->sku ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Local:
                                        {{ $it->localizacao ?? 'Não informado' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                    {{ rtrim(rtrim(number_format($it->qty_solicitada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-wrap items-start gap-2">
                                            <input type="number" step="any" min="0"
                                                wire:model.defer="inputs.{{ $it->id }}.qty"
                                                class="w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                                            <input type="text" placeholder="Motivo (se não separar)"
                                                wire:model.defer="inputs.{{ $it->id }}.motivo"
                                                class="flex-1 min-w-[200px] rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 @error('inputs.' . $it->id . '.motivo') border-red-500 @enderror">

                                            @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                                                <button wire:click="salvarItem({{ $it->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors">Salvar</button>
                                            @endif
                                        </div>
                                        @error('inputs.' . $it->id . '.motivo')
                                            <div class="text-xs text-red-500">{{ $message }}</div>
                                        @enderror

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                            <label
                                                class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                                                <input type="checkbox"
                                                    wire:model.defer="inputs.{{ $it->id }}.inconsistencia"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                                Reportar Inconsistência
                                            </label>
                                            <input type="text" placeholder="Observação da inconsistência"
                                                wire:model.defer="inputs.{{ $it->id }}.obs"
                                                class="flex-1 min-w-[250px] rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>

                                        @if ($it->separado_por_id)
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Salvo por:</span>
                                                {{ optional($it->separadoPor)->name }} em
                                                {{ optional($it->separado_em)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                        @if ($it->motivo_nao_separado)
                                            <div class="text-xs text-amber-600 dark:text-amber-400"><span
                                                    class="font-semibold">Motivo:</span> {{ $it->motivo_nao_separado }}
                                            </div>
                                        @endif
                                        @if ($it->inconsistencia_reportada)
                                            <div class="text-xs text-rose-600 dark:text-rose-400"><span
                                                    class="font-semibold">Inconsistência:</span>
                                                {{ $it->inconsistencia_obs }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $risco ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' }}">
                                        Disponível: {{ rtrim(rtrim(number_format($dispo, 3, ',', '.'), '0'), ',') }}
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Status Item: <span
                                            class="font-semibold">{{ $it->status }}</span></div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    Nenhum item encontrado neste lote de separação.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SEÇÃO DE EMBALAGEM - Antes de concluir o lote --}}
        @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
            <div class="mt-6 rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informações de Embalagem</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Declare como foram armazenados os {{ $batch->items->count() }} itens presentes neste lote:
                    </p>
                    <div class="flex flex-col gap-3 md:flex-row md:items-end">
                        <div class="flex-1">
                            <label for="caixas" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Caixas</label>
                            <input type="number" id="caixas" wire:model="caixas" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div class="flex-1">
                            <label for="sacos" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sacos</label>
                            <input type="number" id="sacos" wire:model="sacos" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div class="flex-1">
                            <label for="sacolas" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sacolas</label>
                            <input type="number" id="sacolas" wire:model="sacolas" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div class="flex-1">
                            <label for="telas" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Telas</label>
                            <input type="number" id="telas" wire:model="telas" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div class="flex-1">
                            <label for="outros" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Outros</label>
                            <input type="text" id="outros" wire:model="outros" placeholder="Ex: Pallets"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <button wire:click="concluirLote" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="concluirLote">Concluir Lote</span>
                            <span wire:loading wire:target="concluirLote">Concluindo...</span>
                        </button>
                    </div>
                    @error('batch')
                        <div class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif

        {{-- SEÇÃO 2: BOTÃO PARA INICIAR SEPARAÇÃO (se não houver lote ativo) --}}
    @else
        <div
            class="rounded-lg border-2 border-dashed p-8 text-center border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nenhum lote de separação em andamento</h3>
            @if (in_array($orcamento->status, ['Aprovado']))
                <p class="text-gray-500 dark:text-gray-400 mt-2 mb-4">
                    Clique no botão abaixo para criar um novo lote e iniciar o processo de separação dos itens deste
                    orçamento.
                </p>
                <button wire:click="iniciarSeparacao" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="iniciarSeparacao">Iniciar Nova Separação</span>
                    <span wire:loading wire:target="iniciarSeparacao">Iniciando...</span>
                </button>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400"><br />
                    A separação só pode ser iniciada quando o orçamento estiver com status
                    <span class="font-semibold">Aprovado</span>.
                </div>
            @endif
        </div>

        {{-- SEÇÃO 3: HISTÓRICO DE LOTES CONCLUÍDOS --}}
        @if ($concludedBatches && $concludedBatches->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Histórico de Lotes Concluídos
                </h3>
                <div class="space-y-4" x-data="{ open: null }">
                    @foreach ($concludedBatches as $cBatch)
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                            <button @click="open = open === {{ $cBatch->id }} ? null : {{ $cBatch->id }}"
                                class="w-full flex justify-between items-center p-4 text-left">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">Lote #{{ $cBatch->id }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Concluído em: {{ optional($cBatch->finished_at)->format('d/m/Y H:i') }} por
                                        {{ optional($cBatch->criadoPor)->name ?? 'N/A' }}
                                    </p>
                                    @if ($cBatch->qtd_caixas || $cBatch->qtd_sacos || $cBatch->qtd_sacolas || $cBatch->outros_embalagem)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Embalagem: 
                                            @if($cBatch->qtd_caixas) {{ $cBatch->qtd_caixas }} caixas @endif
                                            @if($cBatch->qtd_sacos) {{ $cBatch->qtd_sacos }} sacos @endif
                                            @if($cBatch->qtd_sacolas) {{ $cBatch->qtd_sacolas }} sacolas @endif
                                            @if($cBatch->qtd_telas) {{ $cBatch->qtd_telas }} telas @endif
                                            @if($cBatch->outros_embalagem) {{ $cBatch->outros_embalagem }} @endif
                                        </p>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform"
                                    :class="{ 'rotate-180': open === {{ $cBatch->id }} }" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open === {{ $cBatch->id }}" x-collapse
                                class="border-t border-gray-200 dark:border-gray-700 p-4">
                                <ul class="space-y-2">
                                    @foreach ($cBatch->items as $cItem)
                                        <li class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">{{ $cItem->produto->nome }}</span>:
                                            Separado <span
                                                class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_separada, 3, ',', '.'), '0'), ',') }}</span>
                                            de <span
                                                class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_solicitada, 3, ',', '.'), '0'), ',') }}</span>.
                                            @if ($cItem->motivo_nao_separado)
                                                <span class="text-amber-600 dark:text-amber-500 text-xs"> (Motivo:
                                                    {{ $cItem->motivo_nao_separado }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>