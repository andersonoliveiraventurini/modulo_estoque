<x-layouts.app :title="__('Gerenciar Orçamento #' . $orcamento->id)">
    <div class="flex flex-col gap-6">

        {{-- Cabeçalho --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Gerenciar Orçamento #{{ $orcamento->id }}
                @if ($orcamento->status != 'Aprovar desconto')
                    -
                    @if ($orcamento->pdf_path)
                        <a href="{{ asset('storage/' . $orcamento->pdf_path) }}" target="_blank" rel="noopener">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                PDF
                            </x-button>
                        </a>
                    @endif
                @endif

                @if ($orcamento->status == 'Aprovar desconto' || $orcamento->status == 'Pendente')
                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                @endif
            </h2>

            <div class="flex flex-wrap justify-between items-start gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? '---' }}</p>
                        <p><strong>Telefone:</strong> {{ $orcamento->cliente->telefone ?? '---' }}</p>
                        <p><strong>Endereço:</strong> {{ $orcamento->cliente->endereco ?? '---' }}</p>
                        <p><strong>Obra:</strong> {{ $orcamento->obra ?? '---' }}</p>
                    </div>
                    <div>
                        <p><strong>Data do Orçamento:</strong> {{ $orcamento->created_at->format('d/m/Y') }}</p>
                        <p><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                        </p>
                        <p><strong>Prazo de Entrega:</strong> {{ $orcamento->prazo_entrega ?? '---' }}</p>
                        <p><strong>Vendedor:</strong> {{ $orcamento->vendedor->name ?? '---' }}</p>
                    </div>
                </div>

                {{-- Status Comercial + Atualização --}}
                <div class="text-right min-w-[280px]">
                    @if ($orcamento->status === 'Aprovar desconto')
                        <span
                            class="inline-block bg-yellow-200 text-yellow-800 text-sm px-3 py-1 rounded-full font-medium mb-2">
                            Aguardando aprovação de desconto
                        </span>
                        <form id="form-aprovar-{{ $orcamento->id }}" class="inline-flex flex-wrap gap-2"
                            data-id="{{ $orcamento->id }}"
                            action="{{ route('orcamentos.aprovar-desconto', $orcamento->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <select name="acao" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="aprovar">Aprovar Desconto</option>
                                <option value="reprovar">Reprovar Desconto</option>
                            </select>

                            <button type="submit"
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                Salvar
                            </button>
                        </form>
                    @endif
                    <br />
                    <span
                        class="inline-block bg-yellow-200 text-yellow-800 text-sm px-3 py-1 rounded-full font-medium mb-2">
                        Status
                    </span>
                    <form id="form-status-{{ $orcamento->id }}" class="inline-flex flex-wrap gap-2"
                        data-id="{{ $orcamento->id }}"
                        data-url="{{ route('orcamentos.atualizar-status', $orcamento->id) }}">
                        @csrf
                        @method('PUT')
                        <select name="status" class="border border-gray-300 rounded px-2 py-1 text-sm status-select"
                            data-id="{{ $orcamento->id }}">
                            @foreach (['Pendente', 'Aprovado', 'Cancelado', 'Rejeitado', 'Expirado'] as $s)
                                <option value="{{ $s }}" @selected($orcamento->status === $s)>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button"
                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 atualizar-status"
                            data-id="{{ $orcamento->id }}">
                            Atualizar
                        </button>
                    </form>
                    {{-- Workflow Operacional (separação/conferência) --}}
                    <div class="mt-4">
                        @php
                            $wf = $orcamento->workflow_status;
                            $map = [
                                'aguardando_separacao' => [
                                    'label' => 'Aguardando Separação',
                                    'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                ],
                                'em_separacao' => [
                                    'label' => 'Em Separação',
                                    'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                ],
                                'aguardando_conferencia' => [
                                    'label' => 'Aguardando Conferência',
                                    'class' =>
                                        'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
                                ],
                                'em_conferencia' => [
                                    'label' => 'Em Conferência',
                                    'class' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
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
                            <span
                                class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        @endif

                        <div class="mt-2 flex flex-wrap gap-2 justify-end">
                            <a href="{{ route('orcamentos.separacao.show', $orcamento->id) }}"
                                class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">
                                Separação
                            </a>
                            <a href="{{ route('orcamentos.conferencia.show', $orcamento->id) }}"
                                class="inline-flex items-center px-3 py-2 rounded bg-sky-600 hover:bg-sky-700 text-white text-sm">
                                Conferência
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progresso da Expedição --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
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
        </div>

        {{-- Card de chamada para Separação quando aprovado e sem lote ativo --}}
        @php
            $temBatchAtivo = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                ->whereIn('status', ['aberto', 'em_separacao'])
                ->exists();
        @endphp
        @if ($orcamento->status === 'Aprovado' && $temBatchAtivo)
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Pronto para Separação</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Inicie a separação para este orçamento.</p>
                    </div>
                    <a href="{{ route('orcamentos.separacao.show', $orcamento->id) }}"
                        class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">
                        Ir para Separação
                    </a>
                </div>
            </div>
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
                                <th class="px-3 py-2 border text-right">Preço Unit.</th>
                                <th class="px-3 py-2 border text-right">Preço Unit. c/ Desc.</th>
                                <th class="px-3 py-2 border text-right">Subtotal</th>
                                {{-- Novas colunas de estoque --}}
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
                                    <td class="px-3 py-2 border">{{ $prod->part_number ?? '—' }}</td>
                                    <td class="px-3 py-2 border">{{ $prod->fornecedor->nome ?? '—' }}</td>
                                    <td class="px-3 py-2 border">{{ $prod->cor ?? '—' }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $item->quantidade }}</td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right text-green-600 font-medium">R$
                                        {{ number_format($item->valor_unitario_com_desconto, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($item->valor_com_desconto, 2, ',', '.') }}
                                    </td>
                                    {{-- Estoque --}}
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($estoqueAtual, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($reservado, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right">
                                        {{ number_format($disponivel, 2, ',', '.') }}
                                    </td>
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
                                        {{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right">R$
                                        {{ number_format($vidro->preco_metro_quadrado * (($orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0) / 100), 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 border text-right font-semibold text-green-600">R$
                                        {{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}
                                    </td>
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
                    <p><strong>Desconto Percentual:</strong> {{ number_format($percentual, 2, ',', '.') }}%</p>
                    @foreach ($orcamento->descontos->where('tipo', 'fixo') as $desc)
                        <p><strong>{{ $desc->motivo }}:</strong> -R$ {{ number_format($desc->valor, 2, ',', '.') }}
                        </p>
                    @endforeach
                    @if ($orcamento->guia_recolhimento > 0)
                        <p><strong>Guia de Recolhimento:</strong> R$
                            {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}</p>
                    @endif
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

                function setLoading(el, isLoading) {
                    if (!el) {
                        return;
                    }
                    if (isLoading) {
                        el.textContent = 'Processando...';
                        el.disabled = true;
                        el.className += ' opacity-60 cursor-not-allowed';
                    } else {
                        var prev = el.getAttribute('data-prev-text');
                        if (prev) {
                            el.textContent = prev;
                        }
                        el.disabled = false;
                        el.className = el.className.replace(/\bopacity-60\b/g, '').replace(/\bcursor-not-allowed\b/g, '')
                            .replace(/\s{2,}/g, ' ').trim();
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
                        credentials: 'same-origin'
                    }).then(function(resp) {
                        if (!resp.ok) {
                            return resp.json().then(function(j) {
                                var msg = (j && j.message) ? j.message : 'Erro na requisicao';
                                var e = new Error(msg);
                                e.status = resp.status;
                                throw e;
                            })["catch"](function() {
                                return resp.text().then(function(t) {
                                    e2.status = resp.status;
                                    throw e2;
                                });
                            });
                        }
                        return resp.json()["catch"](function() {
                            return {};
                        });
                    });
                }

                // Clique no botão Atualizar Status (delegação global)
                document.addEventListener('click', function(ev) {
                    var btn = findAncestorWithClass(ev.target, 'atualizar-status');
                    if (!btn) {
                        return;
                    }

                    var id = btn.getAttribute('data-id');
                    var form = qs('#form-status-' + id);
                    if (!form) {
                        return;
                    }

                    var url = form.getAttribute('data-url');
                    var select = form.querySelector('.status-select');
                    var novoStatus = select ? select.value : '';


                    Swal.fire({
                        title: 'Confirmacao',
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
                                }, 800);
                            } else {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 800);
                            }
                        }, function(err) {
                            var emsg = (err && err.message) ? err.message :
                                'Nao foi possivel atualizar o status.';
                            Swal.fire({
                                title: 'Erro',
                                text: emsg,
                                icon: 'error'
                            });
                        }).then(function() {
                            setLoading(btn, false);
                        });
                    });
                });

                (function() {
                    'use strict';

                    // Submit do form Aprovar Desconto (delegação global)
                    document.addEventListener('submit', function(ev) {
                        // 1. Identifique o formulário que foi enviado a partir do evento.
                        // ev.target é o elemento que disparou o evento, que neste caso é o <form>.
                        var form = ev.target;

                        // 2. Verifique se este é o formulário que queremos interceptar.
                        // Isso evita que este código seja executado para *todos* os formulários da página.
                        // Usamos o ID que começa com "form-aprovar-" como um seletor.
                        if (!form.matches('[id^="form-aprovar-"]')) {
                            return; // Se não for o formulário de aprovação, não faça nada.
                        }

                        // Se o código chegou até aqui, sabemos que é o formulário correto.
                        // Agora o resto do seu código funcionará, pois a variável 'form' está definida.

                        ev.preventDefault(); // Previne o envio padrão do formulário.

                        var action = form.getAttribute('action');
                        var submitBtn = form.querySelector('button[type="submit"]');
                        var selectAcao = form.querySelector('select[name="acao"]');
                        var acao = selectAcao ? selectAcao.value : '';

                        // A função setLoading provavelmente não está definida neste escopo,
                        // certifique-se de que ela exista ou remova a chamada se não for necessária.
                        // setLoading(submitBtn, true);

                        // A função formFetch também é customizada, garantindo que ela exista.
                        formFetch(action, form, {
                            acao: acao
                        }).then(function(data) {
                            var msg = (data && data.message) ? data.message :
                                'Ação executada com sucesso!';
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
                                }, 800);
                            } else {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 800);
                            }
                        }, function(err) {
                            var emsg = (err && err.message) ? err.message :
                                'Não foi possível concluir a operação.';
                            Swal.fire({
                                title: 'Erro',
                                text: emsg,
                                icon: 'error'
                            });
                        }).then(function() {
                            // setLoading(submitBtn, false);
                        });
                    });

                })();
    </script>
</x-layouts.app>
