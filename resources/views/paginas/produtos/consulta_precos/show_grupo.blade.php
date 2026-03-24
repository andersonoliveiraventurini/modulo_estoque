<x-layouts.app :title="__('Cotação #' . $grupo->id)">
    <div class="flex flex-col gap-6">

        {{-- ── CABEÇALHO ─────────────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-neutral-400 uppercase tracking-wider mb-1">Cotação</p>
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">#{{ $grupo->id }}</h2>
                    <p class="text-sm text-neutral-500 mt-1">
                        Cliente: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome }}</span>
                    </p>
                    <p class="text-sm text-neutral-500">
                        Vendedor: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $grupo->usuario->name }}</span>
                    </p>
                    @if ($grupo->validade)
                        <p class="text-sm mt-1 {{ $grupo->estaExpirado() ? 'text-red-500 font-semibold' : 'text-amber-600' }}">
                            Validade: {{ $grupo->validade->format('d/m/Y H:i') }}
                            {{ $grupo->estaExpirado() ? '⚠️ EXPIRADA' : '' }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-3">
                    @php
                        $statusMap = [
                            'Pendente'              => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                            'Aguardando fornecedor' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                            'Disponível'            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                            'Aprovado'              => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                            'Expirado'              => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                            'Cancelado'             => 'bg-zinc-100 text-zinc-600',
                        ];
                        $statusClass = $statusMap[$grupo->status] ?? 'bg-zinc-100 text-zinc-600';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                        {{ $grupo->status }}
                    </span>

                    @if ($grupo->status === 'Disponível' && !$grupo->estaExpirado())
                        <form action="{{ route('consulta_preco.aprovar_grupo', $grupo->id) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm">
                                <x-heroicon-o-check-circle class="w-4 h-4"/>
                                Aprovar Cotação
                            </x-button>
                        </form>
                    @endif

                    @if ($grupo->status === 'Aprovado' && !$grupo->orcamento_id)
                        <form action="{{ route('consulta_preco.gerar_orcamento', $grupo->id) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="secondary" size="sm">
                                <x-heroicon-o-document-plus class="w-4 h-4"/>
                                Gerar Orçamento
                            </x-button>
                        </form>
                    @endif

                    @if ($grupo->orcamento_id)
                        <a href="{{ route('orcamentos.show', $grupo->orcamento_id) }}">
                            <x-button size="sm" variant="secondary">
                                <x-heroicon-o-document-text class="w-4 h-4"/>
                                Ver Orçamento #{{ $grupo->orcamento_id }}
                            </x-button>
                        </a>
                    @endif
                </div>
            </div>

            @if ($grupo->observacao)
                <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-sm text-zinc-600 dark:text-zinc-400">
                    <strong>Observação:</strong> {{ $grupo->observacao }}
                </div>
            @endif
        </div>

        {{-- Modal novo item --}}
        <div id="modal-novo-item" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-lg p-6 relative">
                <button onclick="document.getElementById('modal-novo-item').classList.add('hidden')"
                        class="absolute top-3 right-3 text-zinc-400 hover:text-zinc-700">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
                <h3 class="text-lg font-semibold mb-4 text-zinc-800 dark:text-zinc-200">Adicionar Item à Cotação</h3>
                <form action="{{ route('consulta_preco.adicionar_item', $grupo->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Descrição <span class="text-red-500">*</span></label>
                        <input type="text" name="descricao" required placeholder="Ex: Perfil de alumínio 3m"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Quantidade <span class="text-red-500">*</span></label>
                            <input type="number" name="quantidade" min="1" value="1" required
                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Part Number</label>
                            <input type="text" name="part_number" placeholder="Opcional"
                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fornecedor sugerido</label>
                        <select name="fornecedor_ids[]"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Nenhum</option>
                            @foreach ($fornecedores as $f)
                                <option value="{{ $f->id }}">{{ $f->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cor</label>
                        <select name="cor_id"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value=""></option>
                            @foreach ($cores as $cor)
                                <option value="{{ $cor->id }}">{{ $cor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação</label>
                        <textarea name="observacao" rows="2"
                                  class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        @if ($grupo->orcamento_id)
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-lg text-sm text-amber-800 dark:text-amber-200 w-full">
                                ⚠️ <strong>Atenção:</strong> O Orçamento #{{ $grupo->orcamento_id }} voltará para <strong>Pendente</strong> e o PDF será deletado.
                            </div>
                        @endif
                        <button type="button" onclick="document.getElementById('modal-novo-item').classList.add('hidden')"
                                class="px-4 py-2 text-sm text-zinc-600 hover:text-zinc-800">Cancelar</button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            Adicionar Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── ITENS DA COTAÇÃO ──────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 text-blue-600"/>
                    Itens da Cotação ({{ $grupo->itens->count() }})
                </h3>
                @if (!in_array($grupo->status, ['Cancelado', 'Expirado']))
                    <button onclick="document.getElementById('modal-novo-item').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        <x-heroicon-o-plus class="w-4 h-4" /> Adicionar Item
                    </button>
                @endif
            </div>

            <div class="space-y-4">
                @foreach ($grupo->itens as $item)
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-zinc-50 dark:bg-zinc-800">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $itemStatusMap[$item->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $item->status }}
                                </span>
                                <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200 truncate">
                                    {{ $item->descricao ?: '(sem descrição)' }}
                                </span>
                                <span class="text-xs text-zinc-500 whitespace-nowrap">
                                    Qtd: {{ $item->quantidade }}
                                    @if ($item->cor) · Cor: {{ $item->cor->nome }} @endif
                                    @if ($item->part_number) · PN: {{ $item->part_number }} @endif
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2 items-center">

                                {{-- ── BOTÃO CADASTRAR COMO PRODUTO ── --}}
                                <a href="{{ route('produtos.create_from_item', $item->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border transition
                                       bg-violet-50 text-violet-700 border-violet-200 hover:bg-violet-100
                                       dark:bg-violet-900/20 dark:text-violet-300 dark:border-violet-700 dark:hover:bg-violet-900/40"
                                   title="Abrir cadastro de produto pré-preenchido com os dados deste item">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    Cadastrar como Produto
                                </a>

                                @if (!in_array($grupo->status, ['Cancelado', 'Expirado']))
                                    <a href="{{ route('consulta_preco.edit', $item->id) }}">
                                        <x-button size="sm" variant="secondary">
                                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                            {{ $item->fornecedorSelecionado ? 'Editar preços' : 'Preencher preços' }}
                                        </x-button>
                                    </a>
                                @endif

                                @if (!$grupo->orcamento_id || in_array($grupo->status, ['Aprovado', 'Disponível']))
                                    <form action="{{ route('consulta_preco.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Remover o item \'{{ addslashes($item->descricao) }}\' desta cotação?')">
                                        @csrf @method('DELETE')
                                        <x-button type="submit" size="sm" variant="danger">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" /> Remover
                                        </x-button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if ($item->fornecedores->isNotEmpty())
                            <div class="px-4 py-3">
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Fornecedores</p>
                                <table class="min-w-full text-sm">
                                    <thead>
                                    <tr class="text-xs text-zinc-500 border-b border-zinc-100 dark:border-zinc-700">
                                        <th class="text-left pb-1 font-medium">Fornecedor</th>
                                        <th class="text-left pb-1 font-medium">Comprador</th>
                                        <th class="text-right pb-1 font-medium">Preço Compra</th>
                                        <th class="text-right pb-1 font-medium">Preço Venda</th>
                                        <th class="text-center pb-1 font-medium">Prazo</th>
                                        <th class="text-center pb-1 font-medium">Selecionado</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                                    @foreach ($item->fornecedores as $forn)
                                        <tr class="{{ $forn->selecionado ? 'bg-emerald-50 dark:bg-zinc-700 ring-1 ring-inset ring-emerald-500/30' : '' }}">
                                            <td class="py-1.5 text-zinc-700 dark:text-zinc-300">{{ $forn->fornecedor->nome_fantasia ?? '—' }}</td>
                                            <td class="py-1.5 text-zinc-500 text-xs">{{ $forn->comprador?->name ?? '—' }}</td>
                                            <td class="py-1.5 text-right text-zinc-600 dark:text-zinc-400">
                                                {{ $forn->preco_compra ? 'R$ ' . number_format($forn->preco_compra, 2, ',', '.') : '—' }}
                                            </td>
                                            <td class="py-1.5 text-right font-semibold text-emerald-700 dark:text-emerald-400">
                                                {{ $forn->preco_venda ? 'R$ ' . number_format($forn->preco_venda, 2, ',', '.') : '—' }}
                                            </td>
                                            <td class="py-1.5 text-center text-zinc-500">{{ $forn->prazo_entrega ?? '—' }}</td>
                                            <td class="py-1.5 text-center">
                                                @if ($forn->selecionado)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/40">
                                                        <x-heroicon-o-check-circle class="w-3 h-3"/> Selecionado
                                                    </span>
                                                @else
                                                    <span class="text-zinc-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-4 py-3 text-sm text-zinc-400">
                                Nenhum fornecedor vinculado ainda. Compras preencherá os preços.
                            </div>
                        @endif

                        @if ($item->observacao)
                            <div class="px-4 pb-3 text-xs text-zinc-500 italic">Obs: {{ $item->observacao }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── RECEBIMENTOS ──────────────────────────────────── --}}
        @php
            $recebidoMap = [];
            foreach ($grupo->entradas as $entrada) {
                foreach ($entrada->itens as $ei) {
                    $recebidoMap[$ei->consulta_preco_id] = ($recebidoMap[$ei->consulta_preco_id] ?? 0) + $ei->quantidade_recebida;
                }
            }
        @endphp

        @if ($grupo->entradas->isNotEmpty())
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-emerald-600"/>
                        Recebimentos ({{ $grupo->entradas->count() }} entrada(s))
                    </h3>
                    <a href="{{ route('entrada_encomendas.create', ['grupo_id' => $grupo->id]) }}">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-plus class="w-4 h-4" /> Nova Entrada
                        </x-button>
                    </a>
                </div>

                <div class="mb-5 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Situação atual dos itens</p>
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="text-xs text-zinc-400 border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left pb-2 font-medium">Item</th>
                            <th class="text-right pb-2 font-medium">Pedido</th>
                            <th class="text-right pb-2 font-medium text-emerald-600">Recebido (total)</th>
                            <th class="text-right pb-2 font-medium text-amber-600">Pendente</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($grupo->itens as $item)
                            @php
                                $solicitado = (float) $item->quantidade;
                                $recebido   = (float) ($recebidoMap[$item->id] ?? 0);
                                $pendente   = max(0, $solicitado - $recebido);
                            @endphp
                            <tr>
                                <td class="py-2 text-zinc-700 dark:text-zinc-300">
                                    {{ $item->descricao }}
                                    @if ($item->cor)<span class="text-zinc-400 text-xs"> · {{ $item->cor->nome }}</span>@endif
                                </td>
                                <td class="py-2 text-right text-zinc-500">{{ number_format($solicitado, 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-semibold text-emerald-600">{{ number_format($recebido, 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-semibold {{ $pendente > 0 ? 'text-amber-600' : 'text-zinc-300' }}">
                                    {{ $pendente > 0 ? number_format($pendente, 0, ',', '.') : '✓' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3">
                    @foreach ($grupo->entradas->sortByDesc('data_recebimento') as $entrada)
                        @php
                            $entStatusMap = [
                                'Recebido parcialmente' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                'Recebido completo'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                'Entregue'              => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                            ];
                        @endphp
                        <div class="flex items-start gap-4 p-4 border border-zinc-100 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                <x-heroicon-o-inbox-arrow-down class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">Entrada #{{ $entrada->id }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $entStatusMap[$entrada->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $entrada->status }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4 text-xs text-zinc-500 mb-2">
                                    <span>📦 Recebido por <strong class="text-zinc-700 dark:text-zinc-300">{{ $entrada->recebedor->name }}</strong> em {{ $entrada->data_recebimento->format('d/m/Y') }}</span>
                                    @if ($entrada->destinatario)
                                        <span>🤝 Entregue para <strong class="text-zinc-700 dark:text-zinc-300">{{ $entrada->destinatario->name }}</strong>
                                            @if ($entrada->data_entrega) em {{ $entrada->data_entrega->format('d/m/Y') }} @endif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($entrada->itens as $ei)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs border
                                            {{ $ei->recebido_completo ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                            {{ $ei->consultaPreco->descricao }}:
                                            <strong>+{{ number_format($ei->quantidade_recebida, 0, ',', '.') }}</strong>
                                            <span class="opacity-60 ml-0.5">(total: {{ number_format($recebidoMap[$ei->consulta_preco_id] ?? $ei->quantidade_recebida, 0, ',', '.') }}/{{ number_format($ei->quantidade_solicitada, 0, ',', '.') }})</span>
                                        </span>
                                    @endforeach
                                </div>
                                @if ($entrada->observacao)
                                    <p class="text-xs text-zinc-400 italic mt-1">{{ $entrada->observacao }}</p>
                                @endif
                            </div>
                            <a href="{{ route('entrada_encomendas.show', $entrada->id) }}" class="flex-shrink-0">
                                <x-button size="sm" variant="secondary"><x-heroicon-o-eye class="w-3.5 h-3.5" /></x-button>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

        @elseif ($grupo->status === 'Aprovado')
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 text-zinc-500">
                        <x-heroicon-o-inbox class="w-5 h-5" />
                        <span class="text-sm">Nenhum recebimento registrado ainda.</span>
                    </div>
                    <a href="{{ route('entrada_encomendas.create', ['grupo_id' => $grupo->id]) }}">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-inbox-arrow-down class="w-4 h-4" /> Registrar Primeiro Recebimento
                        </x-button>
                    </a>
                </div>
            </div>
        @endif

        {{-- ── RODAPÉ ──────────────────────────────────────── --}}
        <div class="flex justify-between">
            <a href="{{ route('consulta_preco.index') }}">
                <x-button size="sm" variant="primary"><x-heroicon-o-arrow-uturn-left class="w-4 h-4"/> Voltar</x-button>
            </a>
            <form action="{{ route('consulta_preco.destroy_grupo', $grupo->id) }}" method="POST"
                  onsubmit="return confirm('Excluir esta cotação e todos os itens?')">
                @csrf @method('DELETE')
                <x-button type="submit" size="sm" variant="danger"><x-heroicon-o-trash class="w-4 h-4"/> Excluir Cotação</x-button>
            </form>
        </div>

    </div>
</x-layouts.app>
