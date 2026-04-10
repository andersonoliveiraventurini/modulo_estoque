<x-layouts.app :title="'Pagamento #' . $pagamento->id">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-5xl mx-auto space-y-6">

                    {{-- ── Cabeçalho ──────────────────────────────────────────────── --}}
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Orçamento #{{ $pagamento->orcamento_id }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                                <span
                                    class="font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider text-[10px] bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">Comprovante
                                    de Pagamento #{{ $pagamento->id }}</span>
                                <span>•</span>
                                Registrado em {{ $pagamento->data_pagamento?->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 flex-wrap">

                            {{-- ── Badge de status ── --}}
                            @if ($pagamento->estornado)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Estornado
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Confirmado
                                </span>
                            @endif

                            {{-- ── Botão PDF do comprovante ── --}}
                            @if ($pagamento->temPdf())
                                <a href="{{ route('pagamentos.comprovante-pdf', $pagamento) }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow transition-colors">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Comprovante PDF
                                </a>
                            @else
                                {{-- PDF ainda não gerado (falha de I/O no momento do pagamento) --}}
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-xs text-gray-400 dark:text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    PDF não gerado
                                </span>
                            @endif

                            <a href="{{ route('orcamentos.show', $pagamento->orcamento_id) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-semibold shadow-sm hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
                                <x-heroicon-o-arrow-left class="w-4 h-4" />
                                Ver Orçamento
                            </a>
                        </div>
                    </div>

                    {{-- ── Alerta estorno ──────────────────────────────────────────── --}}
                    @if ($pagamento->estornado)
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-red-900 dark:text-red-200">Pagamento estornado</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-0.5">
                                        Em {{ $pagamento->data_estorno?->format('d/m/Y \à\s H:i') }}
                                        — Motivo: <em>{{ $pagamento->motivo_estorno }}</em>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── Flash ──────────────────────────────────────────────────── --}}
                    @if (session('success'))
                        <div
                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-sm text-green-800 dark:text-green-300 font-medium">
                            ✓ {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-xl p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-yellow-900 dark:text-yellow-200 text-sm">Atenção</h4>
                                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mt-0.5">
                                        {{ session('warning') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── Grid principal ──────────────────────────────────────────── --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        {{-- ── Coluna esquerda ─────────────────────────────────────── --}}
                        <div class="lg:col-span-2 space-y-6">

                            {{-- Dados do orçamento --}}
                            <div
                                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5">
                                <h3
                                    class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Dados do Orçamento
                                </h3>
                                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Atendente (recebeu)</dt>
                                        <dd class="mt-0.5 flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($pagamento->user->name ?? 'A', 0, 1)) }}
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $pagamento->user->name ?? 'N/A' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Data / Hora</dt>
                                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                                            {{ $pagamento->data_pagamento?->format('d/m/Y H:i') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Cliente</dt>
                                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                                            {{ $pagamento->orcamento->cliente->nome ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Obra</dt>
                                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                                            {{ $pagamento->orcamento->obra ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Condição do orçamento</dt>
                                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                                            {{ $pagamento->orcamento->condicaoPagamento->nome ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Documento fiscal</dt>
                                        <dd class="mt-0.5">
                                            @if ($pagamento->tipo_documento === 'nota_fiscal')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">📄
                                                    Nota Fiscal</span>
                                                @if ($pagamento->cnpj_cpf_nota)
                                                    <span
                                                        class="ml-1 text-xs text-gray-500">{{ $pagamento->cnpj_cpf_nota }}</span>
                                                @endif
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">🧾
                                                    Cupom Fiscal</span>
                                            @endif
                                        </dd>
                                    </div>
                                    @if ($pagamento->observacoes)
                                        <div class="col-span-2">
                                            <dt class="text-gray-500 dark:text-gray-400">Observações</dt>
                                            <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                                                {{ $pagamento->observacoes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            {{-- Formas de pagamento --}}
                            <div
                                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5">
                                <h3
                                    class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Formas de Pagamento

                                    @php
                                        $condicaoOrcamentoId = $pagamento->orcamento->condicao_pagamento_id;
                                        $metodoAlterado = $pagamento->formas->contains(function ($forma) use (
                                            $condicaoOrcamentoId,
                                        ) {
                                            return $forma->condicao_pagamento_id != $condicaoOrcamentoId;
                                        });
                                    @endphp

                                    @if ($metodoAlterado)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 animate-pulse border border-orange-200 dark:border-orange-800 uppercase tracking-tighter">
                                            ⚠ Condição Alterada
                                        </span>
                                    @endif
                                </h3>
                                <div class="space-y-3">
                                    @foreach ($pagamento->formas as $forma)
                                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span
                                                        class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                                        {{ $forma->condicaoPagamento->nome ?? 'N/A' }}
                                                    </span>
                                                    @if ($forma->parcelas > 1 || ($forma->condicaoPagamento && $forma->condicaoPagamento->tipo === 'cartao_credito'))
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                                            {{ $forma->parcelas }}x
                                                            @if ($forma->valor_parcela > 0)
                                                                (R$
                                                                {{ number_format($forma->valor_parcela, 2, ',', '.') }})
                                                            @endif
                                                        </span>
                                                    @endif
                                                    @if ($forma->usa_credito)
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                                            Crédito cliente
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-base font-bold text-blue-700 dark:text-blue-300">
                                                    R$ {{ number_format($forma->valor, 2, ',', '.') }}
                                                </span>
                                            </div>
                                            @if ($forma->comprovantes->isNotEmpty())
                                                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                        Comprovantes:</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($forma->comprovantes as $comp)
                                                            @if (str_contains($comp->mime_type, 'pdf'))
                                                                <a href="{{ route('pagamentos.comprovante.download', $comp->id) }}"
                                                                    target="_blank"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition-colors">
                                                                    📄 {{ $comp->nome_original }}
                                                                    <span
                                                                        class="text-gray-400">({{ number_format($comp->tamanho / 1024, 0) }}
                                                                        KB)</span>
                                                                </a>
                                                            @else
                                                                <button type="button"
                                                                    onclick="abrirLightbox('{{ route('pagamentos.comprovante.download', $comp->id) }}', '{{ addslashes($comp->nome_original) }}')"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition-colors">
                                                                    🖼️ {{ $comp->nome_original }}
                                                                    <span
                                                                        class="text-gray-400">({{ number_format($comp->tamanho / 1024, 0) }}
                                                                        KB)</span>
                                                                </button>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                            @php $comprovantesGerais = $pagamento->comprovantes->whereNull('pagamento_forma_id'); @endphp
                            @if ($comprovantesGerais->isNotEmpty())
                                <div
                                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5 mt-6">
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        Comprovantes Gerais
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($comprovantesGerais as $comp)
                                            @if (str_contains($comp->mime_type, 'pdf'))
                                                <a href="{{ route('pagamentos.comprovante.download', $comp->id) }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition-colors">
                                                    📄 {{ $comp->nome_original }}
                                                    <span
                                                        class="text-gray-400">({{ number_format($comp->tamanho / 1024, 0) }}
                                                        KB)</span>
                                                </a>
                                            @else
                                                <button type="button"
                                                    onclick="abrirLightbox('{{ route('pagamentos.comprovante.download', $comp->id) }}', '{{ addslashes($comp->nome_original) }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 transition-colors">
                                                    🖼️ {{ $comp->nome_original }}
                                                    <span
                                                        class="text-gray-400">({{ number_format($comp->tamanho / 1024, 0) }}
                                                        KB)</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Comprovantes de Rota (Upload direto) --}}
                            @if ($pagamento->routeBillingAttachments->isNotEmpty())
                                <div
                                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5 mt-6">
                                    <h3
                                        class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        Comprovantes de Rota
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($pagamento->routeBillingAttachments as $att)
                                            <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                                title="{{ $att->notes }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border {{ is_null($att->is_valid) ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/20' : ($att->is_valid ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/20' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/20') }} text-xs font-medium hover:opacity-80 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                Doc #{{ $att->id }}
                                                @if ($att->notes)
                                                    <span
                                                        class="opacity-60 text-[10px] ml-1">({{ $att->notes }})</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- ── Coluna direita ───────────────────────────────────────── --}}
                        <div class="space-y-6">

                            {{-- ── Card PDF do comprovante ── --}}
                            <div
                                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5">
                                <h3
                                    class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Comprovante de Pagamento
                                </h3>
                                @if ($pagamento->temPdf())
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        PDF gerado automaticamente no momento do pagamento.
                                        Abre no navegador — use o botão de impressão do visualizador para imprimir ou
                                        salvar.
                                    </p>
                                    <a href="{{ route('pagamentos.comprovante-pdf', $pagamento) }}" target="_blank"
                                        class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow transition-colors">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Visualizar / Imprimir PDF
                                    </a>
                                @else
                                    <div class="flex flex-col items-center justify-center py-4 text-center">
                                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 dark:text-gray-500">PDF não disponível</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">Houve uma falha na
                                            geração.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Resumo financeiro --}}
                            <div
                                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Resumo
                                    Financeiro</h3>
                                <dl class="space-y-3 text-sm">
                                    @php
                                        // O valor total dos itens deve ser a soma dos (valor_unitario * quantidade) SEM os descontos individuais
                                        $valorTotalItensOriginal = $pagamento->orcamento->itens->sum(function ($item) {
                                            return $item->valor_unitario * $item->quantidade;
                                        });

                                        // O desconto aprovado total (inclui descontos nos itens e descontos extras)
                                        $descontoAprovadoTotal = $pagamento->desconto_aplicado;

                                        // O valor final deve ser o Total Original - Desconto Aprovado - Desconto Balcao
                                        // Se houver cobranças residuais, elas somam ao total.
                                        $valorResiduais = $pagamento->orcamento->valor_residuais;
                                        $valorCalculadoFinal = $valorTotalItensOriginal + $valorResiduais - $descontoAprovadoTotal - $pagamento->desconto_balcao;
                                        
                                        // No entanto, para exibir o que está no banco:
                                        $valorExibidoFinal = $pagamento->valor_final;
                                    @endphp
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Valor total itens</dt>
                                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                                            R$ {{ number_format($valorTotalItensOriginal, 2, ',', '.') }}
                                        </dd>
                                    </div>

                                    @if ($descontoAprovadoTotal > 0.01)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500 dark:text-gray-400">Desconto aprovado</dt>
                                            <dd class="font-medium text-red-600 dark:text-red-400">
                                                − R$ {{ number_format($descontoAprovadoTotal, 2, ',', '.') }}
                                            </dd>
                                        </div>
                                    @endif

                                    @if ($pagamento->desconto_balcao > 0.01)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500 dark:text-gray-400">Desconto balcão</dt>
                                            <dd class="font-medium text-red-600 dark:text-red-400">
                                                − R$ {{ number_format($pagamento->desconto_balcao, 2, ',', '.') }}
                                            </dd>
                                        </div>
                                    @endif

                                    <div
                                        class="flex justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                                        <dt class="font-semibold text-gray-700 dark:text-gray-300">Valor final</dt>
                                        <dd class="font-bold text-blue-700 dark:text-blue-300 text-base">
                                            R$ {{ number_format($valorExibidoFinal, 2, ',', '.') }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Valor pago</dt>
                                        <dd class="font-medium text-green-700 dark:text-green-400">
                                            R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}
                                        </dd>
                                    </div>
                                    @if ($pagamento->troco > 0.005)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500 dark:text-gray-400">Troco</dt>
                                            <dd class="font-medium text-orange-600 dark:text-orange-400">
                                                R$ {{ number_format($pagamento->troco, 2, ',', '.') }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            {{-- Estornar --}}
                            @if (!$pagamento->estornado && auth()->user()->can('create', App\Models\Estorno::class))
                                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-red-200 dark:border-red-900 p-5 pt-4">
                                    <h3 class="text-base font-semibold text-red-700 dark:text-red-400 mb-2 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        Solicitar Estorno
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
                                        Use esta opção se precisar reverter ou ressarcir o cliente deste recebimento. O estorno passará por aprovação gerencial.
                                    </p>
                                    <a href="{{ route('estornos.form', $pagamento->id) }}"
                                        class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors text-center cursor-pointer select-none">
                                        Prosseguir para formulário de estorno
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- LIGHTBOX                                                            --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4"
        onclick="fecharLightbox(event)">
        <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between bg-zinc-900/90 rounded-t-xl px-4 py-2.5">
                <span id="lightboxNome" class="text-sm text-white font-medium truncate max-w-xs"></span>
                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                    <a id="lightboxDownload" href="#" download
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>
                    <button onclick="fecharLightbox()"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-zinc-800 rounded-b-xl overflow-auto flex items-center justify-center"
                style="max-height: calc(90vh - 50px);">
                <img id="lightboxImg" src="" alt=""
                    class="max-w-full max-h-full object-contain select-none" style="max-height: calc(90vh - 50px);">
            </div>
        </div>
    </div>

    <script>
        function abrirLightbox(url, nome) {
            document.getElementById('lightboxImg').src = url;
            document.getElementById('lightboxImg').alt = nome;
            document.getElementById('lightboxNome').textContent = nome;
            document.getElementById('lightboxDownload').href = url;
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            lb.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function fecharLightbox(e) {
            if (e && e.target !== document.getElementById('lightbox')) return;
            const lb = document.getElementById('lightbox');
            lb.classList.add('hidden');
            lb.classList.remove('flex');
            document.getElementById('lightboxImg').src = '';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') fecharLightbox();
        });
    </script>
</x-layouts.app>
