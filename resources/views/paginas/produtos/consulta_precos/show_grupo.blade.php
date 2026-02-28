<x-layouts.app :title="__('Cotação #' . $grupo->id)">
    <div class="flex flex-col gap-6">

        {{-- ── CABEÇALHO ─────────────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-neutral-400 uppercase tracking-wider mb-1">Cotação</p>
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">#{{ $grupo->id }}</h2>
                    <p class="text-sm text-neutral-500 mt-1">
                        Cliente: <span
                            class="font-medium text-neutral-700 dark:text-neutral-300">{{ $grupo->cliente->nome_fantasia ?? $grupo->cliente->nome }}</span>
                    </p>
                    <p class="text-sm text-neutral-500">
                        Vendedor: <span
                            class="font-medium text-neutral-700 dark:text-neutral-300">{{ $grupo->usuario->name }}</span>
                    </p>
                    @if ($grupo->validade)
                        <p class="text-sm mt-1 {{ $grupo->estaExpirado() ? 'text-red-500 font-semibold' : 'text-amber-600' }}">
                            Validade: {{ $grupo->validade->format('d/m/Y H:i') }}
                            {{ $grupo->estaExpirado() ? '⚠️ EXPIRADA' : '' }}
                        </p>
                    @endif
                </div>

                {{-- Badge de status --}}
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
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                        {{ $grupo->status }}
                    </span>

                    {{-- Ações por status --}}
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
        {{-- ✅ Botão adicionar item --}}
        @if (!in_array($grupo->status, ['Cancelado', 'Expirado']) && !$grupo->orcamento_id)
            <button onclick="document.getElementById('modal-novo-item').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" />
                Adicionar Item
            </button>
        @endif

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
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fabricante / Fornecedor sugerido</label>
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
                            <option value="">Selecione...</option>
                            @foreach ($cores as $cor)
                                <option value="{{ $cor->id }}">{{ $cor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação</label>
                        <textarea name="observacao" rows="2" placeholder="Detalhes adicionais..."
                                  class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('modal-novo-item').classList.add('hidden')"
                                class="px-4 py-2 text-sm text-zinc-600 hover:text-zinc-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Adicionar Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── ITENS DA COTAÇÃO ──────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <x-heroicon-o-shopping-cart class="w-5 h-5 text-blue-600"/>
                Itens da Cotação ({{ $grupo->itens->count() }})
            </h3>

            <div class="space-y-4">
                @foreach ($grupo->itens as $item)
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        {{-- Cabeçalho do item --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-zinc-50 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
        <span
            class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $itemStatusMap[$item->status] ?? 'bg-zinc-100 text-zinc-600' }}">
            {{ $item->status }}
        </span>
                                <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">
            {{ $item->descricao ?: '(sem descrição)' }}
        </span>
                                <span class="text-xs text-zinc-500">
            Qtd: {{ $item->quantidade }}
                                    @if ($item->cor)
                                        · Cor: {{ $item->cor->nome }}
                                    @endif
                                    @if ($item->part_number)
                                        · PN: {{ $item->part_number }}
                                    @endif
        </span>
                            </div>
                            <div class="flex gap-2">
                                @if (!in_array($grupo->status, ['Cancelado', 'Expirado']))
                                    <a href="{{ route('consulta_preco.edit', $item->id) }}">
                                        <x-button size="sm" variant="secondary">
                                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                            {{ $item->fornecedorSelecionado ? 'Editar preços' : 'Preencher preços' }}
                                        </x-button>
                                    </a>
                                @endif
                                    {{--  Remover item da cotação --}}
                                    @if (!$grupo->orcamento_id || in_array($grupo->status, ['Aprovado', 'Disponível']))
                                        <form action="{{ route('consulta_preco.destroy', $item->id) }}" method="POST"
                                              onsubmit="return confirm('Remover o item \'{{ addslashes($item->descricao) }}\' desta cotação?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                Remover
                                            </x-button>
                                        </form>
                                    @endif
                            </div>
                        </div>

                        {{-- Fornecedores do item --}}
                        @if ($item->fornecedores->isNotEmpty())
                            <div class="px-4 py-3">
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">
                                    Fornecedores</p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                        <tr class="text-xs text-zinc-500 border-b border-zinc-100 dark:border-zinc-700">
                                            <th class="text-left pb-1 font-medium">Fornecedor</th>
                                            <th class="text-right pb-1 font-medium">Preço Compra</th>
                                            <th class="text-right pb-1 font-medium">Preço Venda</th>
                                            <th class="text-center pb-1 font-medium">Prazo</th>
                                            <th class="text-center pb-1 font-medium">Selecionado</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                                        @foreach ($item->fornecedores as $forn)
                                            <tr class="{{ $forn->selecionado ? 'bg-emerald-50 dark:bg-zinc-700 ring-1 ring-inset ring-emerald-500/30' : '' }}">
                                                <td class="py-1.5 text-zinc-700 dark:text-zinc-300">
                                                    {{ $forn->fornecedor->nome_fantasia ?? '—' }}
                                                </td>
                                                <td class="py-1.5 text-right text-zinc-600 dark:text-zinc-400">
                                                    {{ $forn->preco_compra ? 'R$ ' . number_format($forn->preco_compra, 2, ',', '.') : '—' }}
                                                </td>
                                                <td class="py-1.5 text-right font-semibold text-emerald-700 dark:text-emerald-400">
                                                    {{ $forn->preco_venda ? 'R$ ' . number_format($forn->preco_venda, 2, ',', '.') : '—' }}
                                                </td>
                                                <td class="py-1.5 text-center text-zinc-500">
                                                    {{ $forn->prazo_entrega ?? '—' }}
                                                </td>
                                                <td class="py-1.5 text-center">
                                                    @if ($forn->selecionado)
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/40">
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
                            </div>
                        @else
                            <div class="px-4 py-3 text-sm text-zinc-400">
                                Nenhum fornecedor vinculado ainda. Compras preencherá os preços.
                            </div>
                        @endif

                        @if ($item->observacao)
                            <div class="px-4 pb-3 text-xs text-zinc-500 italic">
                                Obs: {{ $item->observacao }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── AÇÕES RODAPÉ ──────────────────────────────────── --}}
        <div class="flex justify-between">
            <a href="{{ route('consulta_preco.index') }}">
                <x-button size="sm" variant="primary">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4"/>
                    Voltar
                </x-button>
            </a>
            <form action="{{ route('consulta_preco.destroy_grupo', $grupo->id) }}" method="POST"
                  onsubmit="return confirm('Excluir esta cotação e todos os itens?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" size="sm" variant="danger">
                    <x-heroicon-o-trash class="w-4 h-4"/>
                    Excluir Cotação
                </x-button>
            </form>
        </div>

    </div>
</x-layouts.app>
