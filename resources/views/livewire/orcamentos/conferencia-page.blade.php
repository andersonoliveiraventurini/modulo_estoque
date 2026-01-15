<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    {{-- CABEÇALHO DA PÁGINA --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Conferência do Orçamento #{{ $orcamento->id }}
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

    {{-- SEÇÃO 1: CONFERÊNCIA ATIVA --}}
    @if ($conferencia)
        <div class="rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Conferência Ativa
                            #{{ $conferencia->id }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status: <span
                                class="font-medium text-indigo-600 dark:text-indigo-400">{{ $conferencia->status }}</span>
                            •
                            Início: {{ optional($conferencia->started_at)->format('d/m/Y H:i') }}
                            @if ($conferencia->conferente)
                                • Conferente: {{ $conferencia->conferente->name }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col items-end">
                        @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                            <button wire:click="concluir" wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                                <span wire:loading.remove wire:target="concluir">Concluir Conferência</span>
                                <span wire:loading wire:target="concluir">Concluindo...</span>
                            </button>
                        @endif
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
                                Separada</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                style="min-width: 500px;">Ações de Conferência</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($conferencia->itens as $it)
                            @php $p = $it->produto; @endphp
                            <tr wire:key="conf-row-{{ $it->id }}">
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                    <span class="font-medium">{{ $p->nome }}</span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $p->sku ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 align-top">
                                    {{ rtrim(rtrim(number_format($it->qty_separada, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-wrap items-start gap-2">
                                            <input type="number" step="any" min="0"
                                                wire:model.defer="inputs.{{ $it->id }}.qty"
                                                class="w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                                            <input type="text" placeholder="Motivo da divergência (se houver)"
                                                wire:model.defer="inputs.{{ $it->id }}.motivo"
                                                class="flex-1 min-w-[200px] rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">

                                            @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                                                <button wire:click="salvarItem({{ $it->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors">Salvar</button>
                                            @endif
                                        </div>

                                        @if ($it->conferido_por_id)
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Conferido por:</span>
                                                {{ optional($it->conferidoPor)->name }} em
                                                {{ optional($it->conferido_em)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                        @if ($it->motivo_divergencia)
                                            <div class="text-xs text-amber-600 dark:text-amber-400"><span
                                                    class="font-semibold">Motivo:</span> {{ $it->motivo_divergencia }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if ($it->status === 'divergente')
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                                            Divergente
                                            ({{ rtrim(rtrim(number_format($it->divergencia, 3, ',', '.'), '0'), ',') }})
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                            OK
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    Nenhum item encontrado nesta conferência.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SEÇÃO DE EMBALAGEM - Antes de concluir --}}
        @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
            <div class="mt-6 rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informações de Embalagem</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Confirme ou ajuste como foram armazenados os {{ $conferencia->itens->count() }} itens presentes nesta conferência:
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
                        <button wire:click="concluir" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="concluir">Concluir Conferência</span>
                            <span wire:loading wire:target="concluir">Concluindo...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div
            class="rounded-lg border-2 border-dashed p-8 text-center border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nenhuma conferência em andamento</h3>
            @if (in_array($orcamento->status, ['Aprovado']))
                <p class="text-gray-500 dark:text-gray-400 mt-2 mb-4">
                    Clique no botão abaixo para criar uma nova conferência e iniciar o processo de verificação dos
                    itens separados.
                </p>
                <button wire:click="iniciarConferencia" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="iniciarConferencia">Iniciar Nova Conferência</span>
                    <span wire:loading wire:target="iniciarConferencia">Iniciando...</span>
                </button>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400"><br />
                    A conferência só pode ser iniciada quando o orçamento estiver com status
                    <span class="font-semibold">Aprovado</span> e houver um lote de separação concluído.
                </div>
            @endif
        </div>

        {{-- SEÇÃO 3: HISTÓRICO DE CONFERÊNCIAS CONCLUÍDAS --}}
        @if ($concludedConferencias && $concludedConferencias->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Histórico de Conferências
                    Concluídas
                </h3>
                <div class="space-y-4" x-data="{ open: null }">
                    @foreach ($concludedConferencias as $cConf)
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                            <button @click="open = open === {{ $cConf->id }} ? null : {{ $cConf->id }}"
                                class="w-full flex justify-between items-center p-4 text-left">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">Conferência
                                        #{{ $cConf->id }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Concluída em: {{ optional($cConf->finished_at)->format('d/m/Y H:i') }}
                                        @if ($cConf->conferente)
                                            por {{ $cConf->conferente->name }}
                                        @endif
                                        • Lote de Separação: #{{ $cConf->picking_batch_id }}
                                    </p>
                                    @if ($cConf->qtd_caixas || $cConf->qtd_sacos || $cConf->qtd_sacolas || $cConf->outros_embalagem)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Embalagem: 
                                            @if($cConf->qtd_caixas) {{ $cConf->qtd_caixas }} caixas @endif
                                            @if($cConf->qtd_sacos) {{ $cConf->qtd_sacos }} sacos @endif
                                            @if($cConf->qtd_sacolas) {{ $cConf->qtd_sacolas }} sacolas @endif
                                            @if($cConf->outros_embalagem) {{ $cConf->outros_embalagem }} @endif
                                        </p>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform"
                                    :class="{ 'rotate-180': open === {{ $cConf->id }} }" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open === {{ $cConf->id }}" x-collapse
                                class="border-t border-gray-200 dark:border-gray-700 p-4">
                                <ul class="space-y-2">
                                    @foreach ($cConf->itens as $cItem)
                                        <li class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">{{ $cItem->produto->nome }}</span>:
                                            Conferido <span
                                                class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_conferida, 3, ',', '.'), '0'), ',') }}</span>
                                            de <span
                                                class="font-semibold">{{ rtrim(rtrim(number_format($cItem->qty_separada, 3, ',', '.'), '0'), ',') }}</span>.
                                            @if ($cItem->status === 'divergente')
                                                <span
                                                    class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">
                                                    Divergência:
                                                    {{ rtrim(rtrim(number_format($cItem->divergencia, 3, ',', '.'), '0'), ',') }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                    OK
                                                </span>
                                            @endif
                                            @if ($cItem->motivo_divergencia)
                                                <span class="text-amber-600 dark:text-amber-500 text-xs block mt-1">
                                                    Motivo: {{ $cItem->motivo_divergencia }}</span>
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