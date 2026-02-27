<x-layouts.app :title="__('Gerenciar Orçamento #' . $orcamento->id)">
    <div class="flex flex-col gap-6">

        {{-- Cabeçalho --}}
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 shadow-sm overflow-hidden">

            {{-- ══════════════════════════════════════
         HEADER DO CARD
    ══════════════════════════════════════ --}}
            <div
                class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex flex-wrap items-center justify-between gap-3">

                {{-- Título --}}
                <div class="flex items-center gap-2 min-w-0">
                    <div
                        class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs text-neutral-400 dark:text-neutral-500 font-medium uppercase tracking-wider leading-none mb-0.5">
                            Orçamento</p>
                        <h2 class="text-lg font-bold text-neutral-900 dark:text-white leading-tight truncate">
                            #{{ $orcamento->id }}
                        </h2>
                    </div>
                </div>

                {{-- Ações do Header --}}
                <div class="flex items-center flex-wrap gap-2">

                    {{-- PDF removido do header, exibido no painel de status --}}

                    {{-- Editar --}}
                    @if (in_array($orcamento->status, ['Aprovar desconto', 'Aprovar pagamento', 'Pendente', 'Aprovado']))
                        <a href="{{ route('orcamentos.edit', $orcamento->id) }}">
                            <x-button size="sm" variant="secondary">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                <span class="hidden sm:inline">Editar</span>
                            </x-button>
                        </a>
                    @endif

                    {{-- Atualizar Preços --}}
                    <form action="{{ route('orcamentos.atualizar-precos', $orcamento->id) }}" method="POST"
                        onsubmit="return confirm('Tem certeza? Isso irá atualizar todos os preços com os valores atuais dos produtos e remover os descontos aplicados.')">
                        @csrf
                        <button type="submit">
                            <x-button size="sm" variant="orange" tag="span">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                <span class="hidden sm:inline">Atualizar Preços</span>
                            </x-button>
                        </button>
                    </form>

                </div>
            </div>

            {{-- ══════════════════════════════════════
         CORPO DO CARD
    ══════════════════════════════════════ --}}
            <div class="px-5 py-4">
                <div class="flex flex-col lg:flex-row lg:items-start gap-5">

                    {{-- ──────── INFORMAÇÕES COMPACTAS ──────── --}}
                    <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-0 min-w-0">

                        {{-- helper macro: cada linha é label + valor inline --}}
                        @php
                            $campos = [
                                ['label' => 'Cliente', 'value' => $orcamento->cliente->nome ?? null, 'bold' => true],
                                ['label' => 'Telefone', 'value' => $orcamento->cliente->telefone ?? null],
                                ['label' => 'Endereço', 'value' => $orcamento->cliente->endereco ?? null],
                                ['label' => 'Obra', 'value' => $orcamento->obra ?? null],
                            ];
                            $campos2 = [
                                ['label' => 'Data', 'value' => $orcamento->created_at->format('d/m/Y')],
                                [
                                    'label' => 'Validade',
                                    'value' => \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y'),
                                ],
                                ['label' => 'Entrega', 'value' => $orcamento->prazo_entrega ?? null],
                                ['label' => 'Vendedor', 'value' => $orcamento->vendedor->name ?? null],
                                ['label' => 'Pagamento', 'value' => $orcamento->condicaoPagamento->nome ?? null],
                            ];
                        @endphp

                        {{-- Coluna 1 --}}
                        <div class="space-y-1.5">
                            @foreach ($campos as $c)
                                <div class="flex items-baseline gap-1.5 min-w-0">
                                    <span
                                        class="text-xs text-neutral-500 dark:text-neutral-400 font-medium flex-shrink-0">{{ $c['label'] }}:</span>
                                    <span
                                        class="text-sm truncate {{ $c['bold'] ?? false ? 'text-white font-semibold' : 'text-neutral-200' }}">
                                        {{ $c['value'] ?: '---' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Coluna 2 --}}
                        <div class="space-y-1.5">
                            @foreach ($campos2 as $c)
                                <div class="flex items-baseline gap-1.5 min-w-0">
                                    <span
                                        class="text-xs text-neutral-500 dark:text-neutral-400 font-medium flex-shrink-0">{{ $c['label'] }}:</span>
                                    <span class="text-sm truncate text-neutral-200">{{ $c['value'] ?: '---' }}</span>
                                </div>
                            @endforeach

                            @if ($orcamento->outros_meios_pagamento)
                                <div class="flex items-baseline gap-1.5 min-w-0">
                                    <span class="text-xs text-blue-400 font-medium flex-shrink-0">Meio especial:</span>
                                    <span
                                        class="text-sm truncate text-blue-400 font-semibold">{{ $orcamento->outros_meios_pagamento }}</span>
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- ──────── PAINEL DE STATUS ──────── --}}
                    @if ($orcamento->validade >= now() || in_array($orcamento->status, ['Aprovado']))
                        <div class="lg:w-72 flex-shrink-0">
                            <div class="space-y-4">

                                {{-- ── APROVAR DESCONTO ── --}}
                                @if ($orcamento->status === 'Aprovar desconto')
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">
                                                Aguardando aprovação de desconto</p>
                                        </div>
                                        <a href="/descontos/orcamento/{{ $orcamento->id }}" class="block">
                                            <x-button size="sm" variant="primary" class="w-full justify-center">
                                                <x-heroicon-o-receipt-percent class="w-4 h-4" />
                                                Validar Desconto
                                            </x-button>
                                        </a>
                                    </div>

                                    {{-- ── APROVAR PAGAMENTO ── --}}
                                @elseif ($orcamento->status === 'Aprovar pagamento')
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-2 h-2 rounded-full bg-orange-400 animate-pulse flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                                                Aguardando aprovação de pagamento</p>
                                        </div>
                                        <a href="{{ route('solicitacoes-pagamento.aprovar', $orcamento->id) }}"
                                            class="block">
                                            <x-button size="sm" variant="primary" class="w-full justify-center">
                                                <x-heroicon-o-credit-card class="w-4 h-4" />
                                                Validar Pagamento
                                            </x-button>
                                        </a>
                                    </div>

                                    {{-- ── REJEITADO ── --}}
                                @elseif ($orcamento->status === 'Rejeitado')
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-red-700 dark:text-red-400">Orçamento
                                                Rejeitado</p>
                                        </div>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Este orçamento foi rejeitado durante a aprovação do meio de pagamento.
                                        </p>
                                    </div>

                                    {{-- ── STATUS NORMAL ── --}}
                                @else
                                    {{-- Seletor de Status --}}
                                    <div class="space-y-2">
                                        <p
                                            class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                            Status Comercial</p>
                                        <form id="form-status-{{ $orcamento->id }}" class="flex gap-2"
                                            data-id="{{ $orcamento->id }}"
                                            data-url="{{ route('orcamentos.atualizar-status', $orcamento->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <select name="status"
                                                class="flex-1 border border-gray-300 dark:border-neutral-600 dark:bg-zinc-700 dark:text-white rounded-lg px-2 py-1.5 text-sm status-select focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                                data-id="{{ $orcamento->id }}">
                                                @foreach (['Pendente', 'Aprovado', 'Cancelado', 'Rejeitado', 'Expirado'] as $s)
                                                    <option value="{{ $s }}" @selected($orcamento->status === $s)>
                                                        {{ $s }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors atualizar-status"
                                                data-id="{{ $orcamento->id }}">
                                                Salvar
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Badge Workflow --}}
                                    @php
                                        $wf = $orcamento->workflow_status;
                                        $map = [
                                            'aguardando_separacao' => [
                                                'label' => 'Aguardando Separação',
                                                'class' =>
                                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                            ],
                                            'em_separacao' => [
                                                'label' => 'Em Separação',
                                                'class' =>
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                            ],
                                            'aguardando_conferencia' => [
                                                'label' => 'Aguardando Conferência',
                                                'class' =>
                                                    'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
                                            ],
                                            'em_conferencia' => [
                                                'label' => 'Em Conferência',
                                                'class' =>
                                                    'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
                                            ],
                                            'conferido' => [
                                                'label' => 'Conferido',
                                                'class' =>
                                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                            ],
                                            'finalizado' => [
                                                'label' => 'Conferido e Finalizado',
                                                'class' =>
                                                    'bg-emerald-200 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-100',
                                            ],
                                        ];
                                        $badge = $map[$wf] ?? null;
                                    @endphp

                                    @if ($badge)
                                        <div>
                                            <p
                                                class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">
                                                Logística</p>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $badge['class'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                                {{ $badge['label'] }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Botões Operacionais --}}
                                    <div class="space-y-2">
                                        <p
                                            class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                            Operacional</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('orcamentos.separacao.show', $orcamento->id) }}"
                                                class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                Separação
                                            </a>
                                            <a href="{{ route('orcamentos.conferencia.show', $orcamento->id) }}"
                                                class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                Conferência
                                            </a>
                                        </div>
                                    </div>

                                    {{-- ── BOTÃO PDF DESTACADO ── --}}
                                    @if (in_array($orcamento->status, ['Aprovar desconto', 'Aprovar pagamento', 'Pendente', 'Aprovado']))

                                        @if ($orcamento->pdf_path)
                                            <a href="{{ asset('storage/' . $orcamento->pdf_path) }}" target="_blank"
                                                rel="noopener"
                                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg
                                           bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700
                                           text-white text-sm font-semibold tracking-wide
                                           shadow-md shadow-emerald-900/40
                                           transition-all duration-150 group">
                                                <svg class="w-4 h-4 transition-transform group-hover:-translate-y-0.5 group-hover:scale-110"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                                </svg>
                                                Baixar PDF do Orçamento
                                            </a>
                                        @endif
                                    @endif
                                @endif

                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ✅ ALERTA PARA APROVAÇÕES PENDENTES --}}
        @if ($orcamento->status === 'Aprovar desconto')
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400 dark:text-yellow-500" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Aprovação de Desconto Necessária
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>
                                Este orçamento possui descontos pendentes de aprovação.
                                O PDF não estará disponível até que todos os descontos sejam aprovados ou rejeitados.
                            </p>
                        </div>
                        <div class="mt-4">
                            <a href="/descontos/orcamento/{{ $orcamento->id }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-receipt-percent class="w-4 h-4" />
                                Ir para Aprovação de Descontos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($orcamento->status === 'Aprovar pagamento')
            <div
                class="bg-orange-50 dark:bg-orange-900/20 border-l-4 border-orange-400 dark:border-orange-600 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-orange-400 dark:text-orange-500" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                            Aprovação de Meio de Pagamento Necessária
                        </h3>
                        <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                            <p>
                                Este orçamento possui uma solicitação de meio de pagamento especial pendente de
                                aprovação.
                                O PDF não estará disponível até que o meio de pagamento seja aprovado ou rejeitado.
                            </p>
                            @if ($orcamento->outros_meios_pagamento)
                                <p class="mt-2 font-medium">
                                    Meio solicitado: {{ $orcamento->outros_meios_pagamento }}
                                </p>
                            @endif
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('solicitacoes-pagamento.aprovar', $orcamento->id) }}"
                                 class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-credit-card class="w-4 h-4" />
                                Ir para Aprovação de Meio de Pagamento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($orcamento->status === 'Rejeitado')
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-red-400 dark:text-red-500" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            Orçamento Rejeitado
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p>
                                Este orçamento foi rejeitado durante a aprovação de meio de pagamento.
                                Não é possível prosseguir com a separação ou conferência.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($orcamento->status, ['Aprovado']))
            {{-- Progresso da Expedição --}}
            {{-- ✅ SÓ MOSTRA SE NÃO FOR REJEITADO --}}
            @if ($orcamento->status !== 'Rejeitado')
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Progresso da Expedição
                    </h3>
                    @php
                        $s = $orcamento->workflow_status;
                        $done = 'text-emerald-600';
                        $todo = 'text-gray-400 dark:text-gray-500';
                        $active = 'text-indigo-600';
                        $step = function (string $label, bool $isActive, bool $isDone) use ($done, $todo, $active) {
                            $cl = $isDone ? $done : ($isActive ? $active : $todo);
                            return "<div class='flex items-center gap-2 {$cl}'>
                                    <span class='text-sm'>{$label}</span>
                                </div>";
                        };
                        $is = fn($arr) => in_array($s, $arr, true);
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        {!! $step(
                            'Aguardando Separação',
                            $s === 'aguardando_separacao',
                            $is(['em_separacao', 'aguardando_conferencia', 'em_conferencia', 'conferido', 'finalizado']),
                        ) !!}
                        {!! $step(
                            'Em Separação',
                            $s === 'em_separacao',
                            $is(['aguardando_conferencia', 'em_conferencia', 'conferido', 'finalizado']),
                        ) !!}
                        {!! $step(
                            'Aguardando Conferência',
                            $s === 'aguardando_conferencia',
                            $is(['em_conferencia', 'conferido', 'finalizado']),
                        ) !!}
                        {!! $step('Em Conferência / Finalização', $is(['em_conferencia']), $is(['conferido', 'finalizado'])) !!}
                    </div>
                </div>
            @endif

            {{-- Card de chamada para Separação quando aprovado e sem lote ativo --}}
            @php
                $temBatchAtivo = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                    ->whereIn('status', ['aberto', 'em_separacao'])
                    ->exists();
            @endphp
            {{-- ✅ SÓ MOSTRA SE STATUS FOR APROVADO E NÃO FOR REJEITADO --}}
            @if ($orcamento->status === 'Aprovado' && $temBatchAtivo && $orcamento->status !== 'Rejeitado')
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Pronto para Separação
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Inicie a separação para este orçamento.
                            </p>
                        </div>
                        <a href="{{ route('orcamentos.separacao.show', $orcamento->id) }}"
                            class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">
                            Ir para Separação
                        </a>
                    </div>
                </div>
            @endif
        @endif
        {{-- Itens do Orçamento --}}
        @if ($orcamento->itens->count() > 0)
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h9M15.5 20V10" />
                    </svg>
                    Itens do Orçamento
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 border">Código</th>
                                <th class="px-3 py-2 border">Produto</th>
                                <th class="px-3 py-2 border">Part Number</th>
                                <th class="px-3 py-2 border">Fornecedor</th>
                                <th class="px-3 py-2 border">Cor</th>
                                <th class="px-3 py-2 border text-center">Qtd</th>
                                <th class="px-3 py-2 border text-center">Aceita Desconto</th>
                                <th class="px-3 py-2 border text-right">Preço Unit.</th>
                                <th class="px-3 py-2 border text-right">Preço Unit. c/ Desc.</th>
                                <th class="px-3 py-2 border text-right">Subtotal</th>
                                <th class="px-3 py-2 border text-right">Estoque Atual</th>
                                <th class="px-3 py-2 border text-right">Reservado</th>
                                <th class="px-3 py-2 border text-right">Disponível</th>
                                <th class="px-3 py-2 border text-center">Alerta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($orcamento->itens as $item)
                                @php
                                    $prod = $item->produto;
                                    if (!$prod) continue;
                                    $reservado = (float) \App\Models\EstoqueReserva::where('produto_id', $prod->id ?? 0)
                                        ->where('status', 'ativa')
                                        ->sum('quantidade');
                                    $estoqueAtual = (float) ($prod->estoque_atual ?? 0);
                                    $disponivel = $estoqueAtual - $reservado;
                                    $min = (float) ($prod->estoque_minimo ?? 0);
                                    $risco = $disponivel - (float) $item->quantidade < $min;
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 border">{{ $prod->codigo ?? $item->produto_id }}</td>
                                    <td class="px-3 py-2 border">{{ $prod->nome ?? '—' }}</td>
                                    <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '—' }}</td>
                                    <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome ?? '—' }}</td>
                                    <td class="px-3 py-2 border">
                                        <span class="w-5 h-5 border border-zinc-300 dark:border-zinc-600 rounded"
                                            style="background-color: {{ $prod->cor->codigo_hex ?? '' }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                        {{ $prod->cor->nome ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 border text-center">{{ $item->quantidade }}</td>
                                    <td class="px-3 py-2 border text-center">
                                        {{ ($prod->liberar_desconto ?? 0) == 0 ? 'Não' : 'Sim' }}</td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right text-green-600 font-medium">R$
                                        {{ number_format($item->valor_unitario_com_desconto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($estoqueAtual, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($reservado, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($disponivel, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-center">
                                        @if ($risco)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                                Abaixo do mínimo
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                OK
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Vidros / Esteiras --}}
        @if ($orcamento->vidros->count() > 0)
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Vidros e Esteiras
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 border text-center">Qtd</th>
                                <th class="px-3 py-2 border">Descrição</th>
                                <th class="px-3 py-2 border text-center">Altura (mm)</th>
                                <th class="px-3 py-2 border text-center">Largura (mm)</th>
                                <th class="px-3 py-2 border text-right">Preço m²</th>
                                <th class="px-3 py-2 border text-right">Desc.</th>
                                <th class="px-3 py-2 border text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orcamento->vidros as $vidro)
                                <tr>
                                    <td class="px-3 py-2 border text-center">{{ $vidro->quantidade }}</td>
                                    <td class="px-3 py-2 border">{{ $vidro->descricao }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $vidro->altura }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $vidro->largura }}</td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($vidro->preco_metro_quadrado * (($orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0) / 100), 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right font-semibold text-green-600">R$
                                        {{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif



        {{-- Totais e Descontos --}}
        @php
            $totalItens = $orcamento->itens->sum('valor_com_desconto');
            $totalVidros = $orcamento->vidros->sum('valor_com_desconto');
            $totalFixos = $orcamento->descontos->where('tipo', 'fixo')->sum('valor');
            $percentual = $orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0;
            $valorFinal =
                $totalItens +
                $totalVidros -
                $totalFixos +
                ($orcamento->frete ?? 0) +
                ($orcamento->guia_recolhimento ?? 0);
        @endphp

        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Totais e Descontos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    {{-- Desconto Percentual --}}
                    @if ($percentual > 0)
                        <div class="flex items-center justify-between mb-2">
                            <span><strong>Desconto Percentual:</strong>
                                {{ number_format($percentual, 2, ',', '.') }}%@php
                                    $descontoPercentual = $orcamento->descontos->where('tipo', 'percentual')->first();
                                @endphp
                                @if ($descontoPercentual)
                                    @if ($descontoPercentual->aprovado_em)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                            <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                            Aprovado
                                        </span>
                                    @else
                                        <a href="/descontos/orcamento/{{ $orcamento->id }}">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                                <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                                Pendente
                                            </span></a>
                                    @endif
                                @endif
                            </span>
                        </div>
                    @endif

                    {{-- Descontos Fixos --}}
                    @foreach ($orcamento->descontos->where('tipo', 'fixo') as $desc)
                        <div class="flex items-center justify-between mb-2">
                            <span>
                                <strong>{{ $desc->motivo ?: 'Desconto Fixo' }}:</strong>
                                -R$ {{ number_format($desc->valor, 2, ',', '.') }}

                                @if ($desc->aprovado_em || $desc->aprovado_por)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                        <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                        Aprovado
                                    </span>
                                @else
                                    <a href="/descontos/orcamento/{{ $orcamento->id }}">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                            <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                            Pendente
                                        </span>
                                    </a>
                                @endif
                            </span>

                        </div>
                    @endforeach

                    {{-- Descontos por Produto --}}
                    @foreach ($orcamento->descontos->where('tipo', 'produto') as $desc)
                        <div class="flex items-center justify-between mb-2">
                            <span>
                                <strong>{{ $desc->motivo ?: 'Desconto em Produto' }}:</strong>
                                -R$ {{ number_format($desc->valor, 2, ',', '.') }}

                                @if ($desc->aprovado_em || $desc->aprovado_por)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                        <x-heroicon-o-check-circle class="w-3 h-3 mr-1" />
                                        Aprovado
                                    </span>
                                @else
                                    <a href="/descontos/orcamento/{{ $orcamento->id }}">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                            <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                                            Pendente
                                        </span>
                                    </a>
                                @endif
                            </span>

                        </div>
                    @endforeach

                    {{-- Guia de Recolhimento --}}
                    @if ($orcamento->guia_recolhimento > 0)
                        <p class="mt-2"><strong>Guia de Recolhimento:</strong> R$
                            {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}</p>
                    @endif

                    {{-- Frete --}}
                    @if ($orcamento->frete > 0)
                        <p><strong>Frete:</strong> R$ {{ number_format($orcamento->frete, 2, ',', '.') }}</p>
                    @endif
                </div>
                <div>
                    @if ($orcamento->itens->count() > 0)
                        <p><strong>Total Produtos:</strong> R$ {{ number_format($totalItens, 2, ',', '.') }}</p>
                    @endif
                    @if ($orcamento->vidros->count() > 0)
                        <p><strong>Total Vidros:</strong> R$ {{ number_format($totalVidros, 2, ',', '.') }}</p>
                    @endif
                    <p class="text-lg font-semibold text-green-600 mt-2">
                        Valor Final: R$ {{ number_format($valorFinal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Observações --}}
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h4 class="font-medium mb-2">Observações</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ $orcamento->observacoes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>

        {{-- Ações --}}
        <div class="flex justify-between">
            <a href="{{ route('orcamentos.index') }}">
                <x-button size="sm" variant="primary">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                    Orçamentos
                </x-button>
            </a>

            <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                @csrf
                @method('DELETE')
                <x-button type="submit" size="sm" variant="danger">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Excluir Orçamento
                </x-button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            'use strict';

            function qs(sel, ctx) {
                if (!ctx) {
                    ctx = document;
                }
                return ctx.querySelector(sel);
            }

            function qsa(sel, ctx) {
                if (!ctx) {
                    ctx = document;
                }
                return Array.prototype.slice.call(ctx.querySelectorAll(sel));
            }

            function findAncestorWithClass(el, className) {
                while (el && el !== document) {
                    if (el.classList && el.classList.contains(className)) {
                        return el;
                    }
                    el = el.parentNode;
                }
                return null;
            }

            function setLoading(btn, isLoading) {
                if (!btn) return;

                if (isLoading) {
                    btn.disabled = true;
                    btn.dataset.originalText = btn.textContent;
                    btn.textContent = 'Processando...';
                } else {
                    btn.disabled = false;
                    btn.textContent = btn.dataset.originalText || 'Salvar';
                }
            }

            function formFetch(url, formEl, extra) {
                if (!extra) {
                    extra = {};
                }
                var fd = new FormData(formEl);

                for (var k in extra) {
                    if (Object.prototype.hasOwnProperty.call(extra, k)) {
                        fd.set(k, extra[k]);
                    }
                }

                return fetch(url, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(function(resp) {
                    var respClone = resp.clone();

                    if (!resp.ok) {
                        return respClone.json()
                            .then(function(j) {
                                var msg = (j && j.message) ? j.message : 'Erro na requisição';
                                var e = new Error(msg);
                                e.status = resp.status;
                                throw e;
                            })
                            .catch(function() {
                                return resp.text().then(function(t) {
                                    var e2 = new Error('Erro: ' + resp.status);
                                    e2.status = resp.status;
                                    throw e2;
                                });
                            });
                    }

                    return resp.json().catch(function() {
                        return {};
                    });
                });
            }

            // Clique no botão Atualizar Status (delegação global)
            document.addEventListener('click', function(ev) {
                var btn = ev.target;

                if (!btn.classList.contains('atualizar-status')) {
                    btn = findAncestorWithClass(ev.target, 'atualizar-status');
                }

                if (!btn) {
                    return;
                }

                var id = btn.getAttribute('data-id');
                var form = qs('#form-status-' + id);

                if (!form) {
                    console.error('Formulário não encontrado: #form-status-' + id);
                    return;
                }

                var url = form.getAttribute('data-url');
                var select = form.querySelector('.status-select');
                var novoStatus = select ? select.value : '';

                if (!url) {
                    console.error('URL não encontrada no formulário');
                    return;
                }

                Swal.fire({
                    title: 'Confirmação',
                    text: 'Deseja realmente alterar o status para "' + novoStatus + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, atualizar',
                    cancelButtonText: 'Cancelar'
                }).then(function(res) {
                    if (!res.isConfirmed) {
                        return;
                    }

                    setLoading(btn, true);

                    var hasMethod = !!form.querySelector('input[name="_method"]');
                    var extra = {
                        status: novoStatus
                    };
                    if (!hasMethod) {
                        extra._method = 'PUT';
                    }

                    formFetch(url, form, extra).then(function(data) {
                        var msg = (data && data.message) ? data.message :
                            'Status atualizado com sucesso!';
                        Swal.fire({
                            title: 'Sucesso',
                            text: msg,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        if (data && data.redirect) {
                            setTimeout(function() {
                                window.location.href = data.redirect;
                            }, 1600);
                        } else {
                            setTimeout(function() {
                                window.location.reload();
                            }, 1600);
                        }
                    }, function(err) {
                        var emsg = (err && err.message) ? err.message :
                            'Não foi possível atualizar o status.';
                        Swal.fire({
                            title: 'Erro',
                            text: emsg,
                            icon: 'error'
                        });
                        setLoading(btn, false);
                    });
                });
            });

            // Submit do form Aprovar Desconto (delegação global)
            document.addEventListener('submit', function(ev) {
                var form = ev.target;

                if (!form.matches('[id^="form-aprovar-"]')) {
                    return;
                }

                ev.preventDefault();

                var action = form.getAttribute('action');
                var submitBtn = form.querySelector('button[type="submit"]');
                var selectAcao = form.querySelector('select[name="acao"]');
                var acao = selectAcao ? selectAcao.value : '';

                console.log('Action URL:', action);
                console.log('Ação selecionada:', acao);

                if (!action) {
                    console.error('Action não encontrada no formulário');
                    return;
                }

                if (!acao) {
                    Swal.fire({
                        title: 'Atenção',
                        text: 'Por favor, selecione uma ação.',
                        icon: 'warning'
                    });
                    return;
                }

                setLoading(submitBtn, true);

                formFetch(action, form, {
                    acao: acao
                }).then(function(data) {
                    var msg = (data && data.message) ? data.message : 'Ação executada com sucesso!';
                    Swal.fire({
                        title: 'Sucesso',
                        text: msg,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    if (data && data.redirect) {
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1600);
                    } else {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1600);
                    }
                }).catch(function(err) {
                    console.error('Erro capturado:', err);
                    var emsg = (err && err.message) ? err.message :
                        'Não foi possível concluir a operação.';
                    Swal.fire({
                        title: 'Erro',
                        text: emsg,
                        icon: 'error'
                    });
                    setLoading(submitBtn, false);
                });
            });

        })();
    </script>
</x-layouts.app>
