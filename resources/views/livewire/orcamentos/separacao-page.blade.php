<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    {{-- CABEÇALHO DA PÁGINA --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Separação do <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Orçamento #{{ $orcamento->id }}</a> - <a href="{{ asset('storage/' . $orcamento->pdf_path) }}" target="_blank" rel="noopener">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                PDF
                            </x-button>
                        </a>
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

    {{-- ═══════════════════════ PROGRESSO GERAL ════════════════════════════ --}}
    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">
            Progresso Geral da Logística
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 font-medium">Produto</th>
                        <th class="pb-2 font-medium text-center">Solicitado</th>
                        <th class="pb-2 font-medium text-center">Separado</th>
                        <th class="pb-2 font-medium text-center">Conferido</th>
                        <th class="pb-2 font-medium text-center">Pendente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($orcamento->itens as $oi)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100 font-medium">
                                {{ $oi->produto->nome }}
                                <div class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $oi->produto->sku }}</div>
                            </td>
                            <td class="py-2 text-center text-gray-600 dark:text-gray-400">
                                {{ rtrim(rtrim(number_format($oi->quantidade, 3, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 text-center font-semibold {{ $oi->quantidade_separada >= $oi->quantidade ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ rtrim(rtrim(number_format($oi->quantidade_separada, 3, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 text-center font-semibold {{ $oi->quantidade_conferida >= $oi->quantidade ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ rtrim(rtrim(number_format($oi->quantidade_conferida, 3, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 text-center font-bold {{ ($oi->quantidade - $oi->quantidade_separada) > 0 ? 'text-rose-500' : 'text-emerald-600' }}">
                                {{ rtrim(rtrim(number_format(max(0, $oi->quantidade - $oi->quantidade_separada), 3, ',', '.'), '0'), ',') }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- Encomendas --}}
                    @php
                        $grupo = \App\Models\ConsultaPrecoGrupo::with(['itens'])->where('orcamento_id', $orcamento->id)->first();
                    @endphp
                    @if($grupo)
                        @foreach($grupo->itens as $eni)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $eni->descricao }}
                                    <div class="text-[10px] text-purple-500 uppercase tracking-tighter">Encomenda</div>
                                </td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400">
                                    {{ rtrim(rtrim(number_format($eni->quantidade, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="py-2 text-center font-semibold {{ $eni->quantidade_separada >= $eni->quantidade ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ rtrim(rtrim(number_format($eni->quantidade_separada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="py-2 text-center font-semibold —">
                                    —
                                </td>
                                <td class="py-2 text-center font-bold {{ ($eni->quantidade - $eni->quantidade_separada) > 0 ? 'text-rose-500' : 'text-emerald-600' }}">
                                    {{ rtrim(rtrim(number_format(max(0, $eni->quantidade - $eni->quantidade_separada), 3, ',', '.'), '0'), ',') }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

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
                            $isEncomenda = $it->is_encomenda;
                            $p           = $isEncomenda ? null : $it->produto;

                            if (!$isEncomenda && $p) {
                                $reservado = (float) \App\Models\EstoqueReserva::where('produto_id', $p->id)
                                    ->where('status', 'ativa')->sum('quantidade');
                                $dispo = (float) ($p->estoque_atual ?? 0) - $reservado;
                                $risco = $dispo < (float) ($p->estoque_minimo ?? 0);
                            }
                        @endphp
                        <tr wire:key="row-{{ $it->id }}">
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                @if ($isEncomenda)
                                    {{-- ✅ Item de encomenda --}}
                                    <span class="font-medium">{{ $it->descricao_encomenda }}</span>
                                    <div class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">
                        Encomenda
                    </span>
                                    </div>
                                    @if ($it->consultaPreco?->fornecedorSelecionado?->fornecedor)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Fornecedor: {{ $it->consultaPreco->fornecedorSelecionado->fornecedor->nome_fantasia }}
                                        </div>
                                    @endif
                                @else
                                    {{-- Item normal --}}
                                    <span class="font-medium">{{ $p->nome }}</span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $p->sku ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Local: {{ $it->localizacao ?? 'Não informado' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                @php
                                    $objItemResource = $it->orcamentoItem ?? $it->consultaPreco;
                                    $solicitadoTotalResource = $objItemResource->quantidade ?? 0;
                                    // quantidade_separada no model só soma lotes CONCLUÍDOS. 
                                    // Como este lote ainda está aberto/em separação, o model ainda não conta o que está sendo feito aqui.
                                    $jaSeparadoAnterior = $objItemResource->quantidade_separada ?? 0;
                                    $separadoAgora = $it->qty_separada;
                                    $totalConfirmadoSep = $jaSeparadoAnterior + $separadoAgora;
                                    $faltaSepararTotal = max(0, $solicitadoTotalResource - $totalConfirmadoSep);
                                @endphp

                                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg space-y-1">
                                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span>Total Solicitado:</span>
                                        <span class="font-bold">{{ rtrim(rtrim(number_format($solicitadoTotalResource, 3, ',', '.'), '0'), ',') }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                                        <span>Já Separado:</span>
                                        <span class="font-semibold">
                                            {{ rtrim(rtrim(number_format($jaSeparadoAnterior, 3, ',', '.'), '0'), ',') }} + 
                                            {{ rtrim(rtrim(number_format($separadoAgora, 3, ',', '.'), '0'), ',') }} = 
                                            {{ rtrim(rtrim(number_format($totalConfirmadoSep, 3, ',', '.'), '0'), ',') }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm {{ $faltaSepararTotal > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-bold border-t border-blue-100 dark:border-blue-800 pt-1">
                                        <span>Falta Separar:</span>
                                        <span>{{ rtrim(rtrim(number_format($faltaSepararTotal, 3, ',', '.'), '0'), ',') }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-400">
                                    Neste lote: <span class="font-bold">{{ rtrim(rtrim(number_format($it->qty_solicitada, 3, ',', '.'), '0'), ',') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                {{-- Ações de separação (igual para ambos os tipos) --}}
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-wrap items-start gap-2">
                                        <input type="number" step="any" min="0"
                                               wire:model="inputs.{{ $it->id }}.qty"
                                               class="w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                                        <input type="text" placeholder="Motivo (se não separar)"
                                               wire:model="inputs.{{ $it->id }}.motivo"
                                               class="flex-1 min-w-[200px] rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                                        <button wire:click="salvarItem({{ $it->id }})"
                                                class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors">
                                            Salvar
                                        </button>
                                    </div>
                                    @if ($it->separado_por_id)
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Salvo por: {{ optional($it->separadoPor)->name }}
                                            em {{ optional($it->separado_em)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                    @if ($it->motivo_nao_separado)
                                        <div class="text-xs text-amber-600 dark:text-amber-400">
                                            Motivo: {{ $it->motivo_nao_separado }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if ($isEncomenda)
                                    {{-- ✅ Encomenda não tem estoque -- exibe status do item --}}
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">
                    Aguardando entrega
                </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Status: <span class="font-semibold">{{ $it->status }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $risco ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' }}">
                    Disponível: {{ rtrim(rtrim(number_format($dispo, 3, ',', '.'), '0'), ',') }}
                </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Status Item: <span class="font-semibold">{{ $it->status }}</span>
                                    </div>
                                @endif
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
        {{-- Liberado para: orçamentos normais Aprovados (dentro da validade) OU encomendas com status Pago --}}
        @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']) || ($orcamento->encomenda != null && $orcamento->status === 'Pago'))
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
            @if (in_array($orcamento->status, ['Aprovado']) || ($orcamento->encomenda != null && $orcamento->status === 'Pago'))
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
                    <span class="font-semibold">Aprovado</span>
                    @if ($orcamento->encomenda != null)
                        ou <span class="font-semibold">Pago</span> (encomenda)
                    @endif.
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
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Embalagem:
                                            @if($cBatch->qtd_caixas) {{ $cBatch->qtd_caixas }} caixas @endif
                                            @if($cBatch->qtd_sacos) {{ $cBatch->qtd_sacos }} sacos @endif
                                            @if($cBatch->qtd_sacolas) {{ $cBatch->qtd_sacolas }} sacolas @endif
                                            @if($cBatch->outros_embalagem) {{ $cBatch->outros_embalagem }} @endif
                                        </p>
                                        <div class="mt-2 text-left">
                                            <a target="_blank" href="{{ route('picking.etiquetas', $cBatch->id) }}" class="inline-flex items-center px-3 py-1.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold shadow-sm transition-colors">
                                                <x-heroicon-o-printer class="w-4 h-4 mr-1" /> Imprimir Etiquetas
                                            </a>
                                        </div>
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
                                            @if ($cItem->is_encomenda)
                                                <span class="font-medium">{{ $cItem->descricao_encomenda }}</span>
                                                <span class="text-xs text-purple-600 dark:text-purple-400 ml-1">(Encomenda)</span>
                                            @else
                                                <span class="font-medium">{{ $cItem->produto->nome ?? '—' }}</span>
                                            @endif
                                            : Separado <span class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_separada, 3, ',', '.'), '0'), ',') }}</span>
                                            de <span class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_solicitada, 3, ',', '.'), '0'), ',') }}</span>.
                                            @if ($cItem->motivo_nao_separado)
                                                <span class="text-amber-600 dark:text-amber-500 text-xs">(Motivo: {{ $cItem->motivo_nao_separado }})</span>
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
