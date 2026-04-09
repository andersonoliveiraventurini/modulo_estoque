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
                            class="text-xs text-neutral-500 dark:text-neutral-500 font-semibold uppercase tracking-wider leading-none mb-0.5">
                            Orçamento</p>
                        @php
                            $statusColors = [
                                'Pendente' => [
                                    'bg' => 'bg-amber-100',
                                    'text' => 'text-amber-800',
                                    'dark' => 'dark:bg-amber-900/40 dark:text-amber-200',
                                ],
                                'Aprovado' => [
                                    'bg' => 'bg-green-100',
                                    'text' => 'text-green-800',
                                    'dark' => 'dark:bg-green-900/40 dark:text-green-200',
                                ],
                                'Cancelado' => [
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                    'dark' => 'dark:bg-red-900/40 dark:text-red-200',
                                ],
                                'Reprovado' => [
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                    'dark' => 'dark:bg-red-900/40 dark:text-red-200',
                                ],
                                'Expirado' => [
                                    'bg' => 'bg-black',
                                    'text' => 'text-white',
                                    'dark' => 'dark:bg-zinc-950 dark:text-zinc-400',
                                ],
                                'Pago' => [
                                    'bg' => 'bg-green-100',
                                    'text' => 'text-green-800',
                                    'dark' => 'dark:bg-green-900/40 dark:text-green-200',
                                ],
                                'Sem estoque' => [
                                    'bg' => 'bg-pink-100',
                                    'text' => 'text-pink-700',
                                    'dark' => 'dark:bg-pink-900/40 dark:text-pink-300',
                                ],
                                'Pagamento pendente' => [
                                    'bg' => 'bg-orange-100',
                                    'text' => 'text-orange-800',
                                    'dark' => 'dark:bg-orange-900/40 dark:text-orange-200',
                                ],
                            ];
                            $statusDisplay = $orcamento->status === 'Reprovado' ? 'Reprovado' : $orcamento->status;
                            $currentStatusColor = $statusColors[$orcamento->status] ?? $statusColors['Pendente'];

                            // Lógica de Transporte (Rota vs Balcão)
                            $transportIds = $orcamento->transportes->pluck('id')->toArray();
                            $isRota = collect($transportIds)
                                ->intersect([1, 2, 3, 6, 7])
                                ->isNotEmpty();
                            $isBalcao = collect($transportIds)
                                ->intersect([4, 5])
                                ->isNotEmpty();
                        @endphp
                        <h2
                            class="text-lg font-bold text-neutral-900 dark:text-white leading-tight truncate flex flex-wrap items-center gap-2">
                            #{{ $orcamento->id }}
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $currentStatusColor['bg'] }} {{ $currentStatusColor['text'] }} {{ $currentStatusColor['dark'] }} border border-current border-opacity-20 flex-shrink-0">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                {{ $statusDisplay }}
                            </span>

                            {{-- Badge de Rota/Balcão --}}
                            @if ($isRota)
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800 flex-shrink-0">
                                    <x-heroicon-o-truck class="w-3.5 h-3.5" />
                                    Rota
                                </span>
                            @elseif ($isBalcao)
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 flex-shrink-0">
                                    <x-heroicon-o-building-storefront class="w-3.5 h-3.5" />
                                    Balcão
                                </span>
                            @endif

                            @if ($orcamento->encomenda !== null)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300 border border-violet-200 dark:border-violet-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    Encomenda
                                </span>
                            @endif
                        </h2>
                    </div>
                </div>

                {{-- Ações do Header --}}
                <div class="flex items-center flex-wrap gap-2">

                    {{-- PDF removido do header, exibido no painel de status --}}

                    {{-- Editar --}}
                    @if (in_array($orcamento->status, ['Aprovar desconto', 'Aprovar pagamento', 'Pendente', 'Aprovado', 'Sem estoque']))
                        <a href="{{ route('orcamentos.edit', $orcamento->id) }}">
                            <x-button size="sm" variant="secondary">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                <span class="hidden sm:inline">Editar</span>
                            </x-button>
                        </a>
                    @endif

                    @if ($orcamento->encomenda ?? null)
                        {{-- Orçamento gerado por cotação — exibe link para a cotação --}}
                        @php
                            $grupoId = \App\Models\ConsultaPrecoGrupo::where('orcamento_id', $orcamento->id)->value(
                                'id',
                            );
                        @endphp
                        @if ($grupoId)
                            <a href="{{ route('consulta_preco.show_grupo', $grupoId) }}">
                                <x-button size="sm" variant="purple">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                    <span class="hidden sm:inline">Ver Cotação</span>
                                </x-button>
                            </a>
                        @endif
                    @else
                        @if ($orcamento->status !== 'Pago')
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
                        @endif
                    @endif
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
                                        class="text-xs text-neutral-600 dark:text-neutral-400 font-semibold flex-shrink-0">{{ $c['label'] }}:</span>
                                    <span
                                        class="text-sm truncate {{ $c['bold'] ?? false ? 'text-neutral-900 dark:text-white font-bold' : 'text-neutral-800 dark:text-neutral-300' }}">
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
                                        class="text-xs text-neutral-600 dark:text-neutral-400 font-semibold flex-shrink-0">{{ $c['label'] }}:</span>
                                    <span
                                        class="text-sm truncate text-neutral-800 dark:text-neutral-300 font-medium">{{ $c['value'] ?: '---' }}</span>
                                </div>
                            @endforeach

                            @if ($orcamento->outros_meios_pagamento)
                                <div class="flex items-baseline gap-1.5 min-w-0">
                                    <span
                                        class="text-xs text-blue-600 dark:text-blue-400 font-medium flex-shrink-0">Meio
                                        especial:</span>
                                    <span
                                        class="text-sm truncate text-blue-600 dark:text-blue-400 font-semibold">{{ $orcamento->outros_meios_pagamento }}</span>
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- ──────── PAINEL DE STATUS ──────── --}}
                    @if (!$prazoExpirado || in_array($orcamento->status, ['Aprovado', 'Pago', 'Sem estoque']))
                        <div class="lg:w-72 flex-shrink-0">
                            <div class="space-y-4">

                                @php
                                    // Cálculo global de itens sem estoque para uso em múltiplas seções
                                    $itensSemEstoqueViewGlobal = $orcamento->encomenda
                                        ? collect()
                                        : $orcamento->itens->filter(function ($item) use ($orcamento) {
                                            $produto = $item->produto;
                                            if (!$produto) {
                                                return is_null($item->produto_id) ? false : true;
                                            }
                                            $reservadoOutros = \App\Models\EstoqueReserva::where(
                                                'produto_id',
                                                $produto->id,
                                            )
                                                ->where('status', 'ativa')
                                                ->where('orcamento_id', '!=', $orcamento->id)
                                                ->sum('quantidade');
                                            $disponivel = max(0, ($produto->estoque_atual ?? 0) - $reservadoOutros);
                                            return $disponivel < $item->quantidade;
                                        });
                                @endphp

                                {{-- ── STATUS: APROVAR DESCONTO ── --}}
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

                                    {{-- ── Reprovado ── --}}
                                @elseif ($orcamento->status === 'Reprovado')
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-red-700 dark:text-red-400">Orçamento
                                                Reprovado</p>
                                        </div>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Este orçamento foi reprovado durante a aprovação do meio de pagamento.
                                        </p>
                                    </div>
                                @elseif ($orcamento->status === 'Sem estoque')
                                    @php
                                        // Itens pendentes / sem estoque: produtos com falta + itens sem produto (ex.: encomenda listados para informação)
                                        $itensSemEstoqueView = $orcamento->encomenda
                                            ? collect()
                                            : $orcamento->itens->filter(function ($item) use ($orcamento) {
                                                $produto = $item->produto;
                                                if (!$produto) {
                                                    return is_null($item->produto_id) ? false : true;
                                                }
                                                // Regra unificada: Estoque Atual - Reservas de OUTROS orçamentos
                                                $reservadoOutros = \App\Models\EstoqueReserva::where(
                                                    'produto_id',
                                                    $produto->id,
                                                )
                                                    ->where('status', 'ativa')
                                                    ->where('orcamento_id', '!=', $orcamento->id)
                                                    ->sum('quantidade');
                                                $disponivel = max(0, ($produto->estoque_atual ?? 0) - $reservadoOutros);
                                                return $disponivel < $item->quantidade;
                                            });
                                        $ehEncomendaSemProdutos =
                                            ($orcamento->encomenda ?? false) && $itensSemEstoqueView->isEmpty();
                                    @endphp
                                    <form id="form-status-{{ $orcamento->id }}" class="space-y-2.5"
                                        data-id="{{ $orcamento->id }}"
                                        data-url="{{ route('orcamentos.atualizar-status', $orcamento->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <p
                                                class="mb-2 text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">
                                                Alterar Status Comercial
                                            </p>
                                            <div class="flex gap-2">
                                                <select name="status"
                                                    class="flex-1 border border-gray-300 dark:border-neutral-600 dark:bg-zinc-700 dark:text-white rounded-lg px-2 py-1.5 text-sm status-select focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                                    data-id="{{ $orcamento->id }}">
                                                    @foreach (['Pendente', 'Cancelado', 'Reprovado', 'Expirado'] as $s)
                                                        <option value="{{ $s }}"
                                                            @selected($orcamento->status === $s)>
                                                            {{ $s }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors atualizar-status"
                                                    data-id="{{ $orcamento->id }}">
                                                    Salvar
                                                </button>
                                            </div>
                                        </div>

                                        
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                💡 Selecione o novo status e clique em <strong>Salvar</strong> para
                                                alterar.
                                            </p>

                                        {{-- Aviso permanente de falta de estoque --}}
                                        @if ($itensSemEstoqueViewGlobal->isNotEmpty() && $orcamento->status !== 'Aprovado')
                                            <div
                                                class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start gap-2 animate-pulse">
                                                <x-heroicon-o-exclamation-triangle
                                                    class="w-4 h-4 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                                                <div>
                                                    <p
                                                        class="text-[10px] font-bold text-red-700 dark:text-red-400 uppercase tracking-tight">
                                                        Bloqueio de Aprovação</p>
                                                    <p
                                                        class="text-[11px] text-red-600 dark:text-red-300 leading-tight">
                                                        Existem <strong>{{ $itensSemEstoqueViewGlobal->count() }}
                                                            item(ns)</strong> sem saldo suficiente. A aprovação não será
                                                        permitida até que o estoque seja regularizado.
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </form>
                                    @if ($ehEncomendaSemProdutos)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Este orçamento é uma <strong>encomenda</strong>; itens de encomenda não
                                            possuem estoque próprio.
                                            O status pode ter sido definido anteriormente. Você pode visualizar o
                                            PDF abaixo e tentar aprovar novamente.
                                        </p>
                                    @else
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Este orçamento não pode seguir por falta de estoque.
                                            O status voltará automaticamente para <strong>Pendente</strong>
                                            assim que todos os produtos ficarem disponíveis.
                                        </p>
                                    @endif
                                    @if ($itensSemEstoqueView->isNotEmpty())
                                        <ul class="mt-1 space-y-1">
                                            @foreach ($itensSemEstoqueView as $item)
                                                @php
                                                    $prod = $item->produto;
                                                    if ($prod) {
                                                        $reservadoOutros = \App\Models\EstoqueReserva::where(
                                                            'produto_id',
                                                            $prod->id,
                                                        )
                                                            ->where('status', 'ativa')
                                                            ->where('orcamento_id', '!=', $orcamento->id)
                                                            ->sum('quantidade');
                                                        $disponivel = max(
                                                            0,
                                                            ($prod->estoque_atual ?? 0) - $reservadoOutros,
                                                        );
                                                        $faltam = max(0, $item->quantidade - $disponivel);
                                                    } else {
                                                        $faltam = (int) $item->quantidade;
                                                    }
                                                @endphp
                                                <li
                                                    class="flex items-center justify-between text-xs bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded px-2 py-1">
                                                    <span class="font-medium text-red-800 dark:text-red-200 truncate">
                                                        {{ $prod ? $prod->nome ?? "Item #{$item->id}" : "Item #{$item->id}" }}
                                                    </span>
                                                    <span class="text-red-600 dark:text-red-400 flex-shrink-0 ml-2">
                                                        Faltam {{ number_format($faltam, 0, ',', '.') }} un.
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                            </div>

                            {{-- ── STATUS NORMAL ── --}}
                        @else
                            {{-- Seletor de Status --}}
                            <div class="space-y-2">
                                @if ($orcamento->status === 'Pago')
                                    @php $pagamentoRegistrado = $orcamento->pagamento; @endphp
                                    <div
                                        class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg">
                                        <x-heroicon-o-check-badge
                                            class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
                                        <div>
                                            <p class="text-sm font-semibold text-green-800 dark:text-green-200">
                                                Pago pelo cliente
                                            </p>
                                            <p class="text-xs text-green-700 dark:text-green-300 mt-0.5">
                                                Este orçamento foi finalizado e não pode ter o status alterado.
                                            </p>
                                            @if ($pagamentoRegistrado)
                                                <a href="{{ route('pagamentos.show', $pagamentoRegistrado->id) }}"
                                                    class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-green-700 dark:text-green-300 underline hover:text-green-900 dark:hover:text-green-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Ver comprovante — Pagamento #{{ $pagamentoRegistrado->id }}
                                                </a>
                                            @endif

                                            @if ($orcamento->encomenda)
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <a href="{{ route('orcamentos.separacao.show', $orcamento->id) }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium">
                                                        <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                                        Separação
                                                    </a>
                                                    <a href="{{ route('orcamentos.conferencia.show', $orcamento->id) }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-sky-600 hover:bg-sky-700 text-white text-xs font-medium">
                                                        <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                                                        Conferência
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{-- ✅ Alerta quando cotação tem itens sem fornecedor --}}
                                    @if ($cotacaoBloqueada ?? false)
                                        <div
                                            class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 rounded-lg text-xs text-amber-800 dark:text-amber-200">
                                            <div class="flex items-start gap-2">
                                                <x-heroicon-o-exclamation-triangle
                                                    class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" />
                                                <div>
                                                    <strong>Cotação pendente</strong><br>
                                                    Há itens da encomenda sem fornecedor selecionado.
                                                    O status só pode ser alterado após todos os itens serem
                                                    precificados.
                                                    <a href="{{ route('consulta_preco.show_grupo', \App\Models\ConsultaPrecoGrupo::where('orcamento_id', $orcamento->id)->value('id')) }}"
                                                        class="underline font-semibold block mt-1">
                                                        → Ir para a Cotação
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($orcamento->condicao_id != null)

                                        {{-- Aviso: prazo expirado (apenas para status não aprovado) --}}
                                        @if ($prazoExpirado && $orcamento->status !== 'Aprovado')
                                            @if ($orcamento->encomenda)
                                                {{-- Encomenda: mensagem simplificada --}}
                                                <div
                                                    class="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg text-xs text-blue-800 dark:text-blue-200">
                                                    <x-heroicon-o-check-circle
                                                        class="w-4 h-4 flex-shrink-0 mt-0.5 text-blue-500" />
                                                    <div>
                                                        Todos os itens ficaram disponíveis em
                                                        {{ \Carbon\Carbon::parse($ultimaAtualizacao)->format('d/m/Y \à\s H:i') }}.
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Orçamento regular: mensagem de prazo expirado --}}
                                                <div
                                                    class="flex items-start gap-2 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-xs text-red-800 dark:text-red-200">
                                                    <x-heroicon-o-clock
                                                        class="w-4 h-4 flex-shrink-0 mt-0.5 text-red-500" />
                                                    <div>
                                                        <strong>Prazo de aprovação expirado</strong><br>
                                                        Todos os itens ficaram disponíveis em
                                                        {{ \Carbon\Carbon::parse($ultimaAtualizacao)->format('d/m/Y \à\s H:i') }},
                                                        mas o prazo de {{ $orcamento->encomenda ? '10' : '5' }} dias
                                                        para aprovar o orçamento foi
                                                        encerrado.
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Aviso: nem todos os itens disponíveis --}}
                                        @elseif ($itensConsulta->isNotEmpty() && !$todosDisponiveis)
                                            <div
                                                class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-lg text-xs text-amber-800 dark:text-amber-200">
                                                <x-heroicon-o-exclamation-triangle
                                                    class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" />
                                                <div>
                                                    <strong>Cotação pendente</strong><br>
                                                    Nem todos os itens estão com status
                                                    <strong>Disponível</strong>.
                                                    A opção <strong>Aprovado</strong> só será liberada quando
                                                    todos os itens estiverem disponíveis.
                                                </div>
                                            </div>
                                        @endif

                                        @php
                                            $temDescontoPendente = $orcamento
                                                ->descontos()
                                                ->whereNull('aprovado_em')
                                                ->whereNull('rejeitado_em')
                                                ->exists();
                                            $temPagamentoPendente = $orcamento
                                                ->solicitacoesPagamento()
                                                ->where('status', 'Pendente')
                                                ->exists();
                                            $bloqueioPorPendencia = $temDescontoPendente || $temPagamentoPendente;
                                        @endphp

                                        {{-- ─────────────────────────────────────────── --}}
                                        {{-- FORMULÁRIO PARA ALTERAR STATUS --}}
                                        {{-- ─────────────────────────────────────────── --}}
                                        <form id="form-status-{{ $orcamento->id }}" class="space-y-2.5"
                                            data-id="{{ $orcamento->id }}"
                                            data-url="{{ route('orcamentos.atualizar-status', $orcamento->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <p
                                                    class="mb-2 text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">
                                                    Alterar Status Comercial
                                                </p>
                                                <div class="flex gap-2">
                                                    <select name="status"
                                                        {{ $statusBloqueado || $bloqueioPorPendencia ? 'disabled' : '' }}
                                                        class="flex-1 border border-gray-300 dark:border-neutral-600 dark:bg-zinc-700 dark:text-white rounded-lg px-2 py-1.5 text-sm status-select focus:ring-2 focus:ring-blue-300 focus:outline-none
                    {{ $statusBloqueado || $bloqueioPorPendencia ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                        data-id="{{ $orcamento->id }}">
                                                        @foreach (['Pendente', 'Aprovado', 'Cancelado', 'Reprovado', 'Expirado'] as $s)
                                                            @if ($s === 'Aprovado' && ($bloqueiaAprovado || $bloqueioPorPendencia) && $orcamento->status !== 'Aprovado')
                                                                @continue
                                                            @endif
                                                            <option value="{{ $s }}"
                                                                @selected($orcamento->status === $s)>
                                                                {{ $s }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button"
                                                        {{ $statusBloqueado || $bloqueioPorPendencia ? 'disabled' : '' }}
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors atualizar-status
                    {{ $statusBloqueado || $bloqueioPorPendencia ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                        data-id="{{ $orcamento->id }}">
                                                        Salvar
                                                    </button>
                                                </div>
                                            </div>

                                            @if ($bloqueioPorPendencia)
                                                <div
                                                    class="p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-[11px] text-amber-700 dark:text-amber-300">
                                                    <p class="font-bold flex items-center gap-1">
                                                        <x-heroicon-o-lock-closed class="w-3 h-3" />
                                                        Alteração Bloqueada
                                                    </p>
                                                    <p>
                                                        @if ($temDescontoPendente)
                                                            • Existem descontos aguardando aprovação.<br>
                                                        @endif
                                                        @if ($temPagamentoPendente)
                                                            • Existe solicitação de pagamento pendente.<br>
                                                        @endif
                                                        O status comercial só poderá ser alterado após a resolução
                                                        destas pendências.
                                                    </p>
                                                </div>
                                            @else
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                    💡 Selecione o novo status e clique em <strong>Salvar</strong> para
                                                    alterar.
                                                </p>
                                            @endif

                                            {{-- Aviso permanente de falta de estoque --}}
                                            @if ($itensSemEstoqueViewGlobal->isNotEmpty() && $orcamento->status !== 'Aprovado')
                                                <div
                                                    class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start gap-2 animate-pulse">
                                                    <x-heroicon-o-exclamation-triangle
                                                        class="w-4 h-4 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                                                    <div>
                                                        <p
                                                            class="text-[10px] font-bold text-red-700 dark:text-red-400 uppercase tracking-tight">
                                                            Bloqueio de Aprovação</p>
                                                        <p
                                                            class="text-[11px] text-red-600 dark:text-red-300 leading-tight">
                                                            Existem <strong>{{ $itensSemEstoqueViewGlobal->count() }}
                                                                item(ns)</strong> sem saldo suficiente. A aprovação não
                                                            será permitida até que o estoque seja regularizado.
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </form>

                                        @if ($orcamento->encomenda && $orcamento->status === 'Aprovado')
                                            @if ($isRota)
                                                <a href="{{ route('orcamentos.rota_pagamento', $orcamento->id) }}"
                                                    class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium">
                                                    <x-heroicon-o-truck class="w-4 h-4" />
                                                    Faturamento de Rota
                                                </a>
                                            @else
                                                <a href="{{ route('orcamentos.pagamento', $orcamento->id) }}"
                                                    class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium">
                                                    <x-heroicon-o-banknotes class="w-4 h-4" />
                                                    Registrar pagamento no balcão
                                                </a>
                                            @endif
                                        @endif
                                    @endif

                                @endif
                            </div>

                            {{-- ── Badge Workflow ── --}}
                            @php
                                $wf = $orcamento->workflow_status;
                                $map = [
                                    'aguardando_pagamento' => [
                                        'label' => 'Aguardando Pagamento',
                                        'class' =>
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                        'dot' => 'animate-pulse',
                                    ],
                                    'aguardando_separacao' => [
                                        'label' => 'Aguardando Separação',
                                        'class' =>
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                        'dot' => '',
                                    ],
                                    'em_separacao' => [
                                        'label' => 'Em Separação',
                                        'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                        'dot' => '',
                                    ],
                                    'aguardando_conferencia' => [
                                        'label' => 'Aguardando Conferência',
                                        'class' =>
                                            'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
                                        'dot' => '',
                                    ],
                                    'em_conferencia' => [
                                        'label' => 'Em Conferência',
                                        'class' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
                                        'dot' => '',
                                    ],
                                    'conferido' => [
                                        'label' => 'Conferido',
                                        'class' =>
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                        'dot' => '',
                                    ],
                                    'finalizado' => [
                                        'label' => 'Conferido e Finalizado',
                                        'class' =>
                                            'bg-emerald-200 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-100',
                                        'dot' => '',
                                    ],
                                ];
                                $badge = $map[$wf] ?? null;
                            @endphp

                            @if ($badge)
                                <div>
                                    <p
                                        class="text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider mb-1.5">
                                        Logística</p>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $badge['class'] }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-current opacity-70 {{ $badge['dot'] ?? '' }}"></span>
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            @elseif($orcamento->status === 'Sem estoque')
                                <div>
                                    <p
                                        class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1.5">
                                        Logística</p>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200 border border-red-200 dark:border-red-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                        Bloqueado por Estoque
                                    </span>
                                </div>
                            @endif

                            {{-- ── Botões Operacionais: ocultos para encomendas aguardando pagamento ── --}}
                            @if ($orcamento->status === 'Aprovado')
                                @if ($orcamento->workflow_status !== 'aguardando_pagamento')
                                    <div class="space-y-2">
                                        <p
                                            class="text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase tracking-wider">
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
                                @else
                                    {{-- Encomenda aprovada, separação bloqueada --}}
                                    <div
                                        class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m0-8v4m9 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">
                                                    Separação bloqueada</p>
                                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                                    Encomenda aprovada. A separação inicia após confirmação do
                                                    pagamento pelo financeiro.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- ── BOTÃO PDF DESTACADO (sempre visível quando existir PDF, independente do status) ── --}}
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                    Baixar PDF do Orçamento
                                </a>
                            @endif
                    @endif

                </div>
            </div>
            @endif

        </div>
    </div>
    </div>

    {{-- ══════════════════════════════════════
             BANNER ENCOMENDA — aguardando pagamento / em separação
        ══════════════════════════════════════ --}}
    @if ($orcamento->encomenda !== null)
        @php
            $temPickingAtivo = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                ->whereIn('status', ['aberto', 'em_separacao'])
                ->exists();
        @endphp

        @if (!$temPickingAtivo && $orcamento->status === 'Aprovado')
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-5 flex items-start gap-4 shadow">
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m0-8v4m9 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-amber-800 dark:text-amber-200 text-sm">
                        Encomenda aprovada — Separação aguardando pagamento
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        Este orçamento é uma <strong>encomenda</strong>. O lote de separação não será iniciado
                        até que o pagamento seja confirmado pelo financeiro via
                        <a href="{{ $isRota ? route('orcamentos.rota_pagamento', $orcamento->id) : route('solicitacoes-pagamento.aprovar', $orcamento->id) }}"
                            class="underline font-semibold hover:text-amber-900 dark:hover:text-amber-100">
                            {{ $isRota ? 'faturamento de rota' : 'solicitação de pagamento' }}
                        </a>.
                    </p>
                </div>
            </div>
        @elseif ($temPickingAtivo && $orcamento->status === 'Pago')
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-700 p-5 flex items-start gap-4 shadow">
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-emerald-800 dark:text-emerald-200 text-sm">
                        Encomenda — Pagamento confirmado · Separação em andamento
                    </p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-0.5">
                        O pagamento desta encomenda foi confirmado e o lote de separação foi iniciado
                        automaticamente.
                    </p>
                </div>
            </div>
        @endif
    @endif

    {{-- ✅ ALERTA PARA APROVAÇÕES PENDENTES --}}
    @php
        $temDescontoPendenteGlobal = $orcamento
            ->descontos()
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->exists();
    @endphp
    @if ($orcamento->status === 'Aprovar desconto' || $temDescontoPendenteGlobal)
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
                            O PDF não estará disponível até que todos os descontos sejam aprovados ou reprovados.
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
                            O PDF não estará disponível até que o meio de pagamento seja aprovado ou reprovado.
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

    @if ($orcamento->status === 'Reprovado')
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-o-x-circle class="h-5 w-5 text-red-400 dark:text-red-500" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        Orçamento Reprovado
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>
                            Este orçamento foi reprovado durante a aprovação de meio de pagamento.
                            Não é possível prosseguir com a separação ou conferência.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Progresso da Expedição — oculto enquanto encomenda aguarda pagamento --}}
    @if (in_array($orcamento->status, ['Aprovado', 'Pago']) && $orcamento->workflow_status !== 'aguardando_pagamento')
        {{-- ✅ SÓ MOSTRA SE NÃO FOR Reprovado --}}
        @if ($orcamento->status !== 'Reprovado')
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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

                {{-- Link para o PDF de Conferência quando executada --}}
                @php
                    $temConferenciaConcluida = $orcamento->conferencias()->where('status', 'concluida')->exists();
                @endphp

                @if ($temConferenciaConcluida)
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('orcamentos.conferencia.pdf', $orcamento->id) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-sky-600 rounded-lg text-sky-600 hover:bg-sky-600 hover:text-white dark:text-sky-400 dark:border-sky-400 dark:hover:bg-sky-400 dark:hover:text-zinc-900 text-sm font-semibold transition-all duration-200 group">
                            <x-heroicon-o-document-text class="w-5 h-5 transition-transform group-hover:scale-110" />
                            Visualizar PDF de Conferência
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Card de chamada para Separação — oculto para encomendas aguardando pagamento --}}
        @php
            $temBatchAtivo = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                ->whereIn('status', ['aberto', 'em_separacao'])
                ->exists();
        @endphp
        @if ($orcamento->status === 'Aprovado' && $temBatchAtivo && $orcamento->status !== 'Reprovado')
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
    @if ($orcamento->itens->count() > 0 && $orcamento->itens->whereNotNull('produto_id')->count() > 0)
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
                            <th class="px-3 py-2 border text-right" title="Estoque real no sistema">Estoque</th>
                            <th class="px-3 py-2 border text-right" title="Reservado por este e outros orçamentos">
                                Reservado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($orcamento->itens as $item)
                            @php
                                $prod = $item->produto;
                                if (!$prod) {
                                    continue;
                                }
                                $info = $estoqueInfo[$item->produto_id] ?? null;
                                $estoqueAtual = $info ? (float) $info['estoque_atual'] : 0;
                                $reservado = $info ? (float) $info['reservado_total'] : 0;
                                $reservadoOutros = $info ? (float) $info['reservado_outros'] : 0;
                                $detalheReservas = $info ? $info['detalhe_reservas'] : 'Sem reservas';

                                $temReservaOutros = $reservadoOutros > 0;
                                $temReservaPropria = $reservado - $reservadoOutros > 0;
                                $semEstoque = $estoqueAtual - $reservadoOutros < (float) $item->quantidade;
                            @endphp
                            <tr class="{{ $semEstoque ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <td class="px-3 py-2 border">{{ $prod->codigo ?? $item->produto_id }}</td>
                                <td class="px-3 py-2 border">{{ $prod->nome ?? '—' }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '—' }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome ?? '—' }}</td>
                                <td class="px-3 py-2 border">
                                    <span class="w-5 h-5 border border-zinc-300 dark:border-zinc-600 rounded"
                                        style="background-color: {{ $prod->cor->codigo_hex ?? '' }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    {{ $prod->cor->nome ?? '—' }}
                                </td>
                                <td
                                    class="px-3 py-2 border text-center font-semibold {{ $semEstoque ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ $item->quantidade }}
                                    @if ($semEstoque)
                                        <div class="text-xs font-normal text-red-500 mt-0.5">
                                            Faltam
                                            {{ number_format((float) $item->quantidade - ($estoqueAtual - $reservadoOutros), 0, ',', '.') }}
                                            un.
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 border text-center">
                                    {{ ($prod->liberar_desconto ?? 0) == 0 ? 'Não' : 'Sim' }}</td>
                                <td class="px-3 py-2 border text-right">R$
                                    {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 border text-right text-green-600 font-medium">R$
                                    {{ number_format($item->valor_unitario_com_desconto, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 border text-right">R$
                                    {{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 border text-right">
                                    {{ number_format($estoqueAtual, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 border text-right" title="{{ $detalheReservas }}">
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($temReservaOutros)
                                            <span class="text-xs text-red-600 font-bold"
                                                title="Outros orçamentos têm reserva">⚠️</span>
                                            <span
                                                class="text-red-600 font-semibold">{{ number_format($reservado, 2, ',', '.') }}</span>
                                        @elseif($temReservaPropria)
                                            <span
                                                class="text-amber-600">{{ number_format($reservado, 2, ',', '.') }}</span>
                                        @else
                                            <span
                                                class="text-gray-500">{{ number_format($reservado, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Itens da Encomenda (cotação de preço) --}}
    @if ($orcamento->encomenda ?? null)
        @php
            $grupo = \App\Models\ConsultaPrecoGrupo::with(['itens.cor', 'itens.fornecedorSelecionado.fornecedor'])
                ->where('orcamento_id', $orcamento->id)
                ->first();
        @endphp
        @if ($grupo && $grupo->itens->count() > 0)
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 text-purple-600" />
                    Itens da Encomenda
                    <span class="text-xs font-normal text-zinc-400">(gerado a partir da cotação
                        #{{ $grupo->id }})</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 border text-left">Descrição</th>
                                <th class="px-3 py-2 border text-left">Cor</th>
                                <th class="px-3 py-2 border text-left">Part Number</th>
                                <th class="px-3 py-2 border text-center">Qtd</th>
                                <th class="px-3 py-2 border text-left">Fornecedor Selecionado</th>
                                <th class="px-3 py-2 border text-right">Preço Compra</th>
                                <th class="px-3 py-2 border text-right">Preço Venda</th>
                                <th class="px-3 py-2 border text-left">Prazo Entrega</th>
                                <th class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-right">Preço
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($grupo->itens as $item)
                                @php $forn = $item->fornecedorSelecionado; @endphp
                                <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $item->descricao }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400">
                                        {{ $item->cor->nome ?? '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-500">
                                        {{ $item->part_number ?? '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-center font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ $item->quantidade }}
                                    </td>
                                    <td class="px-3 py-2 border border-zinc-200 dark:border-zinc-700">
                                        @if ($forn)
                                            <span
                                                class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-medium">
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                                {{ $forn->fornecedor->nome_fantasia }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400 text-xs">Não selecionado</span>
                                        @endif
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-right text-zinc-600 dark:text-zinc-400">
                                        {{ $forn && $forn->preco_compra ? 'R$ ' . number_format($forn->preco_compra, 2, ',', '.') : '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ $forn && $forn->preco_venda ? 'R$ ' . number_format($forn->preco_venda, 2, ',', '.') : '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                                        {{ $forn->prazo_entrega ?? '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        @if ($forn && $forn->preco_venda)
                                            R$
                                            {{ number_format((float) $forn->preco_venda * (float) $item->quantidade, 2, ',', '.') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
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
        $totalItens = (float) $orcamento->itens->whereNotNull('produto_id')->sum('valor_com_desconto');

        // Total encomenda = soma dos itens do orçamento (produto_id null) já com desconto por item aplicado
        $totalEncomenda = (float) $orcamento->itens->whereNull('produto_id')->sum('valor_com_desconto');

        $totalVidros = (float) $orcamento->vidros->sum('valor_com_desconto');
        $totalFixos = (float) $orcamento->descontos->where('tipo', 'fixo')->sum('valor');
        $percentual = $orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0;
        $valorFinal =
            $totalItens +
            $totalEncomenda +
            $totalVidros -
            $totalFixos +
            (float) ($orcamento->frete ?? 0) +
            (float) ($orcamento->guia_recolhimento ?? 0);
    @endphp

    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Totais e Descontos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                @if ($percentual > 0)
                    <div class="flex items-center justify-between mb-2">
                        <span><strong>Desconto Percentual:</strong>
                            {{ number_format($percentual, 2, ',', '.') }}%@php
                                $descontoPercentual = $orcamento->descontos->where('tipo', 'percentual')->first();
                            @endphp
                            @if ($descontoPercentual)
                                @if ($descontoPercentual->aprovado_em)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200"
                                        title="{{ $descontoPercentual->motivo }}">
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

                @foreach ($orcamento->descontos->where('tipo', 'fixo') as $desc)
                    <div class="flex items-center justify-between mb-2">
                        <span>
                            <strong>{{ $desc->motivo ?: 'Desconto Fixo' }}:</strong>
                            -R$ {{ number_format($desc->valor, 2, ',', '.') }}
                            @if ($desc->aprovado_em || $desc->aprovado_por)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200"
                                    title="{{ $desc->motivo }}">
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

                @foreach ($orcamento->descontos->where('tipo', 'produto') as $desc)
                    <div class="flex items-center justify-between mb-2">
                        <span>
                            <strong>{{ $desc->motivo ?: 'Desconto em Produto' }}:</strong>
                            -R$ {{ number_format($desc->valor, 2, ',', '.') }}
                            @if ($desc->aprovado_em || $desc->aprovado_por)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200"
                                    title="{{ $desc->motivo }}">
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

                @if ($orcamento->guia_recolhimento > 0)
                    <p class="mt-2"><strong>Guia de Recolhimento:</strong> R$
                        {{ number_format((float) $orcamento->guia_recolhimento, 2, ',', '.') }}</p>
                @endif

                @if ((float) $orcamento->frete > 0)
                    <p><strong>Frete:</strong> R$ {{ number_format((float) $orcamento->frete, 2, ',', '.') }}</p>
                @endif
            </div>
            <div>
                @if ($orcamento->itens->count() > 0)
                    <p><strong>Total Produtos:</strong> R$ {{ number_format($totalItens, 2, ',', '.') }}</p>
                @endif
                @if ($totalEncomenda > 0)
                    <p><strong>Total Encomenda:</strong> R$ {{ number_format($totalEncomenda, 2, ',', '.') }}</p>
                @endif
                @if ($orcamento->vidros->count() > 0)
                    <p><strong>Total Vidros:</strong> R$ {{ number_format($totalVidros, 2, ',', '.') }}</p>
                @endif
                <p class="text-lg font-semibold text-green-600 mt-2">
                    Valor Orçamento: R$ {{ number_format($valorFinal, 2, ',', '.') }}
                </p>
                @if ($orcamento->valor_residuais > 0)
                    <p class="text-sm font-medium text-blue-600">
                        + Cobranças Residuais: R$ {{ number_format($orcamento->valor_residuais, 2, ',', '.') }}
                    </p>
                    <p class="text-xl font-bold text-emerald-700 mt-1 border-t pt-1">
                        Total Geral: R$ {{ number_format($orcamento->valor_total_final, 2, ',', '.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Histórico de Pagamentos --}}
    @if ($orcamento->pagamentos()->ativos()->exists())
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow mb-4">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-neutral-800 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                    Histórico de Pagamentos
                </h4>
                @if ($orcamento->valor_restante > 0)
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 border border-amber-200">
                        Pendente: R$ {{ number_format($orcamento->valor_restante, 2, ',', '.') }}
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500">
                        <tr>
                            <th class="px-4 py-2">Data</th>
                            <th class="px-4 py-2">Tipo</th>
                            <th class="px-4 py-2">Condição</th>
                            <th class="px-4 py-2">Valor</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach ($orcamento->pagamentos()->ativos()->get() as $p)
                            <tr class="{{ $p->tipo === 'residual' ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                                <td class="px-4 py-3">
                                    {{ $p->data_pagamento ? $p->data_pagamento->format('d/m/Y H:i') : '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($p->tipo === 'residual')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 text-[10px] font-bold uppercase">
                                            Residual
                                        </span>
                                    @else
                                        <span class="text-neutral-500 text-xs">Principal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $p->condicaoPagamento->nome ?? 'N/A' }}</td>
                                <td class="px-4 py-3 font-medium">R$
                                    {{ number_format($p->valor_final, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    @if ($p->valor_pago >= $p->valor_final)
                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-semibold text-xs">
                                            <x-heroicon-o-check-circle class="w-4 h-4" /> Pago
                                        </span>
                                    @else
                                        <span
                                            class="text-amber-600 dark:text-amber-400 flex items-center gap-1 font-semibold text-xs">
                                            <x-heroicon-o-clock class="w-4 h-4" /> Aguardando
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($p->pdf_path)
                                            <a href="{{ asset('storage/' . $p->pdf_path) }}" target="_blank"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                                title="Ver Comprovante">
                                                <x-heroicon-o-document-text class="w-4 h-4" />
                                            </a>
                                        @endif

                                        @if ($p->valor_pago < $p->valor_final)
                                            <a href="{{ route('orcamentos.residuais.pagar', $p->id) }}"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition-colors">
                                                PAGAR
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($p->observacoes)
                                <tr class="{{ $p->tipo === 'residual' ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                                    <td colspan="6" class="px-4 py-0 pb-2 text-[11px] text-neutral-400 italic">
                                        Obs: {{ $p->observacoes }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Cobrança Residual (apenas encomendas com pagamento ativo) --}}
    @if ($orcamento->isEncomenda() && $orcamento->pagamentos()->ativos()->exists())
        <div class="mt-4 flex justify-end">
            <a href="{{ route('orcamentos.residuais', $orcamento->id) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition-all">
                <x-heroicon-o-currency-dollar class="w-5 h-5 mr-2" />
                Gerenciar Valores Residuais
            </a>
        </div>
    @endif

    {{-- Observações --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
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

        @if ($orcamento->status !== 'Pago')
            <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                @csrf
                @method('DELETE')
                <x-button type="submit" size="sm" variant="danger">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Excluir Orçamento
                </x-button>
            </form>
        @endif
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
                        return respClone.json().then(function(j) {
                            var msg = (j && j.message) ? j.message : 'Erro na requisição';
                            var e = new Error(msg);
                            e.status = resp.status;
                            throw e;
                        }).catch(function() {
                            return resp.text().then(function() {
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

            document.addEventListener('click', function(ev) {
                var btn = ev.target;
                if (!btn.classList.contains('atualizar-status')) {
                    btn = findAncestorWithClass(ev.target, 'atualizar-status');
                }
                if (!btn) return;

                var id = btn.getAttribute('data-id');
                console.log('Botão atualizar-status clicado. ID:', id);

                var form = qs('#form-status-' + id);
                if (!form) {
                    console.error('Formulário não encontrado: #form-status-' + id);
                    Swal.fire({
                        title: 'Erro',
                        text: 'Erro interno: formulário de status não localizado (ID: ' + id + ').',
                        icon: 'error'
                    });
                    return;
                }

                var url = form.getAttribute('data-url');
                var select = form.querySelector('.status-select');
                var novoStatus = select ? select.value : '';
                
                console.log('Dados do formulário:', {
                    url: url,
                    status: novoStatus,
                    id: id
                });

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
                    if (!res.isConfirmed) return;
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
                            title: 'Atenção',
                            text: emsg,
                            icon: 'warning' // Mudado para warning para ser menos 'erro de sistema' e mais 'regra de negócio'
                        }).then(function() {
                            // Recarrega a página mesmo em erro de validação (422), 
                            // pois o status pode ter sido alterado para 'Sem estoque' no backend
                            if (err.status === 422) {
                                window.location.reload();
                            }
                        });
                        setLoading(btn, false);
                    });
                });
            });

            document.addEventListener('submit', function(ev) {
                var form = ev.target;
                if (!form.matches('[id^="form-aprovar-"]')) return;
                ev.preventDefault();

                var action = form.getAttribute('action');
                var submitBtn = form.querySelector('button[type="submit"]');
                var selectAcao = form.querySelector('select[name="acao"]');
                var acao = selectAcao ? selectAcao.value : '';

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
