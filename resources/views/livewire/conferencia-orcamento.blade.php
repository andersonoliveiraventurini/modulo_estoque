<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
     x-data="fotoPreview()"
     x-init="init()">

    {{-- ═══ LIGHTBOX GLOBAL ═══════════════════════════════════════════════════ --}}
    <div x-show="lightboxSrc"
         x-cloak
         @click.self="lightboxSrc = null"
         @keydown.escape.window="lightboxSrc = null"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm p-4">
        <img :src="lightboxSrc"
             class="max-w-full max-h-[90vh] rounded-xl shadow-2xl object-contain" />
        <button @click="lightboxSrc = null"
            class="absolute top-4 right-4 text-white bg-black/50 hover:bg-black/80
                   w-10 h-10 rounded-full flex items-center justify-center text-2xl font-bold transition">
            ×
        </button>
    </div>

    {{-- CABEÇALHO --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Conferência do Orçamento #{{ $orcamento->id }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Cliente: {{ optional($orcamento->cliente)->nome }}
            • Status: <span class="font-semibold">{{ $orcamento->status ?? '—' }}</span>
            • Workflow: <span class="font-semibold">{{ $orcamento->workflow_status ?? '—' }}</span>
        </p>
    </div>

    {{-- FEEDBACK --}}
    @if (session('success'))
        <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800
                    dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded border border-rose-300 bg-rose-50 px-4 py-3 text-rose-800
                    dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- ═══════════════════════ CONFERÊNCIA ATIVA ════════════════════════════ --}}
    @if ($conferencia)

        <div class="rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700">

            {{-- Cabeçalho da conferência --}}
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            Conferência Ativa #{{ $conferencia->id }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status:
                            <span class="font-medium text-indigo-600 dark:text-indigo-400">
                                {{ $conferencia->status }}
                            </span>
                            • Início: {{ optional($conferencia->started_at)->format('d/m/Y H:i') }}
                            @if ($conferencia->conferente)
                                • Conferente: {{ $conferencia->conferente->name }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── ITENS ─────────────────────────────────────────────────────── --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($conferencia->itens as $it)
                    @php $p = $it->produto; @endphp
                    <div wire:key="conf-row-{{ $it->id }}" class="p-4 md:p-5">
                        <div class="flex flex-wrap gap-5">

                            {{-- ① Info do produto --}}
                            <div class="w-44 shrink-0">
                                <p class="font-medium text-gray-800 dark:text-gray-200 leading-tight">
                                    {{ $p->nome }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    SKU: {{ $p->sku ?? '—' }}
                                </p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Separada:
                                    <span class="font-semibold">
                                        {{ rtrim(rtrim(number_format($it->qty_separada, 3, ',', '.'), '0'), ',') }}
                                    </span>
                                </p>

                                {{-- Badge status --}}
                                @if ($it->status === 'divergente')
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                 bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                                        ⚠ Divergente
                                        ({{ rtrim(rtrim(number_format($it->divergencia, 3, ',', '.'), '0'), ',') }})
                                    </span>
                                @elseif ($it->status === 'ok')
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                        ✓ OK
                                    </span>
                                @else
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-200">
                                        ⏳ Não conferido
                                    </span>
                                @endif
                            </div>

                            {{-- ② Formulário de conferência --}}
                            @if ($podeEditar)
                                <div class="flex-1 min-w-[260px] space-y-3">

                                    {{-- Qty + Motivo --}}
                                    <div class="flex flex-wrap gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                Qty conferida
                                            </label>
                                            <input type="number" step="any" min="0"
                                                wire:model.defer="inputs.{{ $it->id }}.qty"
                                                class="w-28 rounded-md border-gray-300 dark:border-gray-600
                                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                                        </div>
                                        <div class="flex-1 min-w-[180px]">
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                Motivo (se divergir)
                                            </label>
                                            <input type="text"
                                                wire:model.defer="inputs.{{ $it->id }}.motivo"
                                                placeholder="Motivo da divergência…"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600
                                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                                        </div>
                                    </div>

                                    {{-- Upload + preview --}}
                                    <div x-data="{ previews: [] }">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Fotos do item
                                            <span class="font-normal text-gray-400">
                                                (opcional · jpg/png/webp · máx. 10 MB cada)
                                            </span>
                                        </label>

                                        <div class="flex flex-wrap gap-2 items-center">
                                            {{-- Input file com preview Alpine --}}
                                            <label class="cursor-pointer inline-flex items-center gap-1.5
                                                          px-3 py-1.5 rounded-md border border-dashed
                                                          border-indigo-300 dark:border-indigo-600
                                                          bg-indigo-50 dark:bg-indigo-900/30
                                                          text-indigo-700 dark:text-indigo-300
                                                          text-xs font-semibold hover:bg-indigo-100 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16l4-4m0 0l4 4m-4-4v9M20 12a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                                </svg>
                                                Selecionar fotos
                                                <input type="file"
                                                    wire:model="novasFotos.{{ $it->id }}"
                                                    multiple accept="image/jpeg,image/png,image/webp"
                                                    class="hidden"
                                                    @change="
                                                        previews = [];
                                                        Array.from($event.target.files).forEach(f => {
                                                            const r = new FileReader();
                                                            r.onload = e => previews.push(e.target.result);
                                                            r.readAsDataURL(f);
                                                        })
                                                    " />
                                            </label>

                                            <input type="text"
                                                wire:model.defer="legendas.{{ $it->id }}"
                                                placeholder="Legenda para as fotos (opcional)"
                                                class="flex-1 min-w-[160px] rounded-md border-gray-300 dark:border-gray-600
                                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                       focus:ring-indigo-500 focus:border-indigo-500 text-xs" />
                                        </div>

                                        {{-- Progresso de upload Livewire --}}
                                        <div wire:loading wire:target="novasFotos.{{ $it->id }}"
                                            class="mt-1 text-xs text-indigo-500 animate-pulse flex items-center gap-1">
                                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                            </svg>
                                            Enviando fotos…
                                        </div>

                                        {{-- Preview das fotos selecionadas (antes de salvar) --}}
                                        <template x-if="previews.length > 0">
                                            <div class="mt-2">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                    Preview — fotos selecionadas
                                                    (<span x-text="previews.length"></span>):
                                                </p>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="(src, i) in previews" :key="i">
                                                        <button type="button"
                                                            @click="$dispatch('open-lightbox', { src })"
                                                            class="w-16 h-16 rounded-lg overflow-hidden border-2
                                                                   border-indigo-300 dark:border-indigo-600
                                                                   hover:ring-2 hover:ring-indigo-400 transition
                                                                   relative group">
                                                            <img :src="src"
                                                                 class="w-full h-full object-cover" />
                                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10
                                                                        transition flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition"
                                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    </template>
                                                </div>
                                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                                    ↑ Clique em "Salvar item" para confirmar o envio das fotos acima.
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Botão salvar --}}
                                    <div>
                                        <button wire:click="salvarItem({{ $it->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="salvarItem({{ $it->id }})"
                                            class="px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700
                                                   text-white text-sm font-semibold shadow-sm transition-colors
                                                   disabled:opacity-50">
                                            <span wire:loading.remove wire:target="salvarItem({{ $it->id }})">
                                                Salvar item
                                            </span>
                                            <span wire:loading wire:target="salvarItem({{ $it->id }})">
                                                Salvando…
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- ③ Fotos já salvas --}}
                            @if ($it->fotos->isNotEmpty())
                                <div class="w-full">
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">
                                        📷 Fotos registradas ({{ $it->fotos->count() }})
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($it->fotos as $foto)
                                            <div class="relative group" wire:key="foto-{{ $foto->id }}">
                                                <button type="button"
                                                    @click="$dispatch('open-lightbox', { src: '{{ $foto->url }}' })"
                                                    class="block w-20 h-20 rounded-lg overflow-hidden border
                                                           border-gray-200 dark:border-gray-700 shadow-sm
                                                           hover:ring-2 hover:ring-indigo-400 transition">
                                                    <img src="{{ $foto->url }}"
                                                         alt="{{ $foto->legenda ?? 'Foto' }}"
                                                         class="w-full h-full object-cover" />
                                                </button>

                                                @if ($foto->legenda)
                                                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white
                                                                text-[10px] px-1 py-0.5 rounded-b-lg
                                                                opacity-0 group-hover:opacity-100 transition truncate">
                                                        {{ $foto->legenda }}
                                                    </div>
                                                @endif

                                                @if ($podeEditar)
                                                    <button type="button"
                                                        wire:click="removerFoto({{ $foto->id }})"
                                                        wire:confirm="Remover esta foto permanentemente?"
                                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full
                                                               bg-rose-600 text-white text-xs font-bold leading-none
                                                               hidden group-hover:flex items-center justify-center
                                                               shadow hover:bg-rose-700 transition">
                                                        ×
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Meta de conferência --}}
                        @if ($it->conferido_por_id)
                            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                Conferido por {{ optional($it->conferidoPor)->name }}
                                em {{ optional($it->conferido_em)->format('d/m/Y H:i') }}
                            </div>
                        @endif
                        @if ($it->motivo_divergencia)
                            <div class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                <span class="font-semibold">Motivo:</span> {{ $it->motivo_divergencia }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Nenhum item encontrado nesta conferência.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── EMBALAGEM + CONCLUIR ─────────────────────────────────────────── --}}
        @if ($podeEditar)
            <div class="mt-6 rounded-lg border bg-white dark:bg-gray-900
                        {{ !$embalagemOk ? 'border-amber-400 dark:border-amber-600' : 'border-gray-200 dark:border-gray-700' }}">
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Informações de Embalagem
                        </h3>
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400">
                            * obrigatório para concluir
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Confirme ou ajuste como foram armazenados os
                        <span class="font-semibold">{{ $conferencia->itens->count() }}</span>
                        itens desta conferência. Preencha ao menos um campo.
                    </p>

                    <div class="flex flex-col gap-3 md:flex-row md:items-end flex-wrap">
                        <div class="flex-1 min-w-[100px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Caixas
                            </label>
                            <input type="number" wire:model.live="caixas" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>
                        <div class="flex-1 min-w-[100px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sacos
                            </label>
                            <input type="number" wire:model.live="sacos" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>
                        <div class="flex-1 min-w-[100px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sacolas
                            </label>
                            <input type="number" wire:model.live="sacolas" min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Outros
                            </label>
                            <input type="text" wire:model.live="outros" placeholder="Ex: 2 Pallets"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                       focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>

                        <div class="shrink-0">
                            <button wire:click="concluir" wire:loading.attr="disabled"
                                @if(!$embalagemOk) disabled title="Preencha ao menos um tipo de embalagem" @endif
                                class="px-5 py-2 rounded-md font-semibold text-sm shadow-sm transition-colors
                                       {{ $embalagemOk
                                           ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                           : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' }}
                                       disabled:opacity-60">
                                <span wire:loading.remove wire:target="concluir">
                                    {{ $embalagemOk ? '✓ Concluir Conferência' : '⚠ Preencha a embalagem' }}
                                </span>
                                <span wire:loading wire:target="concluir">Concluindo…</span>
                            </button>
                        </div>
                    </div>

                    @if(!$embalagemOk)
                        <p class="mt-3 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Informe ao menos um campo de embalagem para habilitar a conclusão.
                        </p>
                    @endif
                </div>
            </div>
        @endif

    {{-- ═══════════════════════ SEM CONFERÊNCIA ATIVA ════════════════════════ --}}
    @else
        <div class="rounded-lg border-2 border-dashed p-8 text-center
                    border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Nenhuma conferência em andamento
            </h3>

            @if ($podeEditar)
                <p class="text-gray-500 dark:text-gray-400 mt-2 mb-4">
                    Clique no botão abaixo para criar uma nova conferência.
                </p>
                <button wire:click="iniciarConferencia" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700
                           text-white font-semibold text-sm shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="iniciarConferencia">Iniciar Nova Conferência</span>
                    <span wire:loading wire:target="iniciarConferencia">Iniciando…</span>
                </button>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    A conferência só pode ser iniciada quando o orçamento estiver com status
                    <span class="font-semibold">Aprovado</span> e houver um lote de separação concluído.
                    <br />
                    <span class="text-xs mt-1 block">
                        Status atual: <strong>{{ $orcamento->status ?? '—' }}</strong>
                        • Workflow: <strong>{{ $orcamento->workflow_status ?? '—' }}</strong>
                    </span>
                </p>
            @endif
        </div>
    @endif

    {{-- ═══════════════════ HISTÓRICO DE CONFERÊNCIAS ════════════════════════ --}}
    @if ($concludedConferencias && $concludedConferencias->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Histórico de Conferências Concluídas  <a href="{{ route('orcamentos.conferencia.pdf', $orcamento) }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md
          bg-gray-700 hover:bg-gray-900 text-white text-xs font-semibold shadow-sm transition-colors">
    📄 Baixar Relatório PDF
</a>
            </h3>
            <div class="space-y-4" x-data="{ open: null }">
                @foreach ($concludedConferencias as $cConf)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700
                                bg-white dark:bg-gray-900 shadow-sm">
                        <button @click="open = open === {{ $cConf->id }} ? null : {{ $cConf->id }}"
                            class="w-full flex justify-between items-center p-4 text-left">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800 dark:text-gray-200">
                                    Conferência #{{ $cConf->id }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Concluída em: {{ optional($cConf->finished_at)->format('d/m/Y H:i') }}
                                    @if ($cConf->conferente) por {{ $cConf->conferente->name }} @endif
                                    • Lote #{{ $cConf->picking_batch_id }}
                                </p>
                                @if ($cConf->qtd_caixas || $cConf->qtd_sacos || $cConf->qtd_sacolas || $cConf->outros_embalagem)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                        📦 Embalagem:
                                        @php $emb = []; @endphp
                                        @if ($cConf->qtd_caixas)       @php $emb[] = $cConf->qtd_caixas  . ' caixa(s)';     @endphp @endif
                                        @if ($cConf->qtd_sacos)        @php $emb[] = $cConf->qtd_sacos   . ' saco(s)';      @endphp @endif
                                        @if ($cConf->qtd_sacolas)      @php $emb[] = $cConf->qtd_sacolas . ' sacola(s)';    @endphp @endif
                                        @if ($cConf->outros_embalagem) @php $emb[] = 'Outros: ' . $cConf->outros_embalagem; @endphp @endif
                                        {{ implode(' • ', $emb) }}
                                    </p>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transform transition-transform shrink-0"
                                 :class="{ 'rotate-180': open === {{ $cConf->id }} }"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open === {{ $cConf->id }}"
                             x-collapse
                             class="border-t border-gray-200 dark:border-gray-700 p-4">
                            <ul class="space-y-5">
                                @foreach ($cConf->itens as $cItem)
                                    <li class="text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex flex-wrap items-start gap-3">
                                            <div class="flex-1 min-w-[200px]">
                                                <span class="font-medium">{{ $cItem->produto->nome }}</span>:
                                                Conferido
                                                <span class="font-semibold">
                                                    {{ rtrim(rtrim(number_format($cItem->qty_conferida, 3, ',', '.'), '0'), ',') }}
                                                </span>
                                                de
                                                <span class="font-semibold">
                                                    {{ rtrim(rtrim(number_format($cItem->qty_separada, 3, ',', '.'), '0'), ',') }}
                                                </span>.

                                                @php
                                                    // Status real: só OK/Divergente se foi efetivamente conferido
                                                    $statusReal = $cItem->conferido_por_id
                                                        ? $cItem->status
                                                        : 'pendente';
                                                @endphp

                                                @if ($statusReal === 'divergente')
                                                    <span class="inline-flex items-center ml-1 px-2 py-0.5 rounded text-xs
                                                                 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">
                                                        ⚠ Divergência:
                                                        {{ rtrim(rtrim(number_format($cItem->divergencia, 3, ',', '.'), '0'), ',') }}
                                                    </span>
                                                @elseif ($statusReal === 'ok')
                                                    <span class="inline-flex items-center ml-1 px-2 py-0.5 rounded text-xs
                                                                 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                        ✓ OK
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center ml-1 px-2 py-0.5 rounded text-xs
                                                                 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200">
                                                        ⏳ Não conferido
                                                    </span>
                                                @endif

                                                @if ($cItem->motivo_divergencia)
                                                    <span class="block mt-0.5 text-xs text-amber-600 dark:text-amber-500">
                                                        Motivo: {{ $cItem->motivo_divergencia }}
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Fotos do item no histórico --}}
                                            @if ($cItem->fotos->isNotEmpty())
                                                <div class="flex flex-wrap gap-1.5">
                                                    <p class="w-full text-xs text-gray-400 dark:text-gray-500 mb-1">
                                                        📷 {{ $cItem->fotos->count() }}
                                                        {{ Str::plural('foto', $cItem->fotos->count()) }}:
                                                    </p>
                                                    @foreach ($cItem->fotos as $foto)
                                                        <button type="button"
                                                            @click="$dispatch('open-lightbox', { src: '{{ $foto->url }}' })"
                                                            class="w-16 h-16 rounded-md overflow-hidden border
                                                                   border-gray-200 dark:border-gray-700 shadow-sm
                                                                   hover:ring-2 hover:ring-indigo-400 transition
                                                                   relative group"
                                                            title="{{ $foto->legenda ?? 'Ver foto' }}">
                                                            <img src="{{ $foto->url }}"
                                                                 alt="{{ $foto->legenda ?? 'Foto' }}"
                                                                 class="w-full h-full object-cover" />
                                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20
                                                                        transition flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition"
                                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    function fotoPreview() {
        return {
            lightboxSrc: null,
            init() {
                // Escuta evento global para abrir lightbox
                window.addEventListener('open-lightbox', (e) => {
                    this.lightboxSrc = e.detail.src;
                });
            }
        }
    }
</script>