<x-layouts.app :title="__('Registrar Entrada de Encomenda')">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-blue-600" />
                Registrar Entrada de Encomenda
            </h2>

            <form action="{{ route('entrada_encomendas.store') }}" method="POST">
                @csrf

                {{-- ── Cabeçalho da entrada ──────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Cotação / Grupo <span class="text-red-500">*</span>
                        </label>
                        <select name="grupo_id" required
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                onchange="carregarItens(this.value)">
                            <option value="">Selecione a cotação...</option>
                            @foreach ($gruposDisponiveis as $g)
                                <option value="{{ $g->id }}"
                                    {{ ($grupo && $grupo->id == $g->id) ? 'selected' : '' }}>
                                    #{{ $g->id }} — {{ $g->cliente->nome_fantasia ?? $g->cliente->nome ?? '?' }}
                                    ({{ $g->itens->count() }} item(s))
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Recebimento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_recebimento" required
                               value="{{ old('data_recebimento', now()->format('Y-m-d')) }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Recebido por <span class="text-red-500">*</span>
                        </label>
                        <select name="recebido_por"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}" {{ auth()->id() == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Entregue para (separador)
                        </label>
                        <select name="entregue_para"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Não entregue ainda</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}" {{ old('entregue_para') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Entrega ao vendedor
                        </label>
                        <input type="date" name="data_entrega"
                               value="{{ old('data_entrega') }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>
                </div>

                {{-- ── Itens ─────────────────────────────────── --}}
                @if ($grupo)
                    @php
                        $jaRecebidoMap = [];
                        foreach ($grupo->entradas as $entrada) {
                            foreach ($entrada->itens as $ei) {
                                $jaRecebidoMap[$ei->consulta_preco_id] =
                                    ($jaRecebidoMap[$ei->consulta_preco_id] ?? 0) + $ei->quantidade_recebida;
                            }
                        }
                    @endphp

                    @if ($grupo->entradas->isNotEmpty())
                        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                            <strong>{{ $grupo->entradas->count() }} entrada(s) anterior(es)</strong> — os campos já mostram o que ainda está pendente.
                        </div>
                    @endif

                    @if (isset($fornecedoresStatus) && !empty($fornecedoresStatus))
                        @foreach ($fornecedoresStatus as $cnpj => $status)
                            @if (!$status['ativo'])
                                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-300">
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 inline-block mr-1" />
                                    <strong>Aviso:</strong> O fornecedor de CNPJ {{ $cnpj }} não está com a situação ATIVA na Receita Federal ({{ $status['situacao'] }}).
                                </div>
                            @endif
                            @if (!$status['tem_ie'])
                                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-sm text-amber-700 dark:text-amber-300">
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 inline-block mr-1" />
                                    <strong>Favor verificar - Fornecedor sem inscrição estadual</strong> (CNPJ: {{ $cnpj }}).
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <div class="space-y-6 mb-6">
                        @foreach ($grupo->itens as $index => $item)
                            @php
                                $forn         = $item->fornecedorSelecionado;
                                $jaRecebido   = (float) ($jaRecebidoMap[$item->id] ?? 0);
                                $totalPedido  = (float) $item->quantidade;
                                $pendente     = max(0, $totalPedido - $jaRecebido);
                                $estaCompleto = $pendente <= 0;
                            @endphp

                            <div class="border {{ $estaCompleto ? 'border-emerald-200 dark:border-emerald-800 opacity-60' : 'border-zinc-200 dark:border-zinc-700' }} rounded-xl overflow-hidden">

                                {{-- Cabeçalho do item --}}
                                <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-zinc-50 dark:bg-zinc-800">
                                    <div>
                                        <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">{{ $item->descricao }}</span>
                                        @if ($item->cor) <span class="text-zinc-400 text-xs"> · {{ $item->cor->nome }}</span> @endif
                                        @if ($item->part_number) <span class="text-zinc-400 text-xs"> · PN: {{ $item->part_number }}</span> @endif
                                    </div>
                                    @if ($estaCompleto)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                            <x-heroicon-o-check-circle class="w-3 h-3" /> Já recebido completo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <x-heroicon-o-clock class="w-3 h-3" /> Pendente: {{ number_format($pendente, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Fornecedor --}}
                                @if ($forn)
                                    <div class="px-4 py-2 border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50">
                                        <div class="flex flex-wrap gap-4 text-xs text-zinc-500">
                                            <span>🏭 <strong class="text-zinc-700 dark:text-zinc-300">{{ $forn->fornecedor->nome_fantasia }}</strong></span>
                                            @if ($forn->prazo_entrega) <span>⏱ {{ $forn->prazo_entrega }}</span> @endif
                                            @if ($forn->preco_compra) <span>💰 R$ {{ number_format($forn->preco_compra, 2, ',', '.') }}</span> @endif
                                            @if ($forn->comprador) <span>👤 {{ $forn->comprador->name }}</span> @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Histórico --}}
                                @if ($jaRecebido > 0)
                                    <div class="px-4 py-2 border-b border-emerald-100 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-900/10">
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                            ✓ Já recebido anteriormente: <strong>{{ number_format($jaRecebido, 0, ',', '.') }}</strong> de {{ number_format($totalPedido, 0, ',', '.') }}
                                        </p>
                                    </div>
                                @endif

                                <div class="px-4 py-4">
                                    <input type="hidden" name="itens[{{ $index }}][consulta_preco_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="itens[{{ $index }}][quantidade_solicitada]" value="{{ $totalPedido }}">

                                    {{-- Quantidade e observação --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">
                                                Qtd Recebida Agora @if(!$estaCompleto)<span class="text-red-500">*</span>@endif
                                            </label>
                                            <input type="number" step="1" min="0"
                                                   name="itens[{{ $index }}][quantidade_recebida]"
                                                   value="{{ old("itens.{$index}.quantidade_recebida", $estaCompleto ? 0 : $pendente) }}"
                                                   {{ $estaCompleto ? 'readonly' : 'required' }}
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none {{ $estaCompleto ? 'bg-zinc-100 dark:bg-zinc-700 cursor-not-allowed' : '' }}">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Observação</label>
                                            <input type="text"
                                                   name="itens[{{ $index }}][observacao]"
                                                   value="{{ old("itens.{$index}.observacao") }}"
                                                   placeholder="Ex: embalagem danificada..."
                                                   {{ $estaCompleto ? 'readonly' : '' }}
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none {{ $estaCompleto ? 'bg-zinc-100 dark:bg-zinc-700' : '' }}">
                                        </div>
                                    </div>

                                    {{-- Campos de identificação do produto --}}
                                    @if (!$estaCompleto)
                                        @include('paginas.produtos.entrada_encomendas._campos_produto_item', [
                                            'index'         => $index,
                                            'categorias'    => $categorias,
                                            'subCategorias' => $subCategorias,
                                        ])
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mb-6 p-6 text-center text-zinc-400 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-xl">
                        Selecione uma cotação acima para carregar os itens.
                    </div>
                @endif

                {{-- Observação geral --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação Geral</label>
                    <textarea name="observacao" rows="2"
                              placeholder="Informações adicionais sobre este recebimento..."
                              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old('observacao') }}</textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit" variant="primary">
                        <x-heroicon-o-check class="w-4 h-4" /> Registrar Entrada
                    </x-button>
                    <a href="{{ route('entrada_encomendas.aprovadas') }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filtra subcategorias pelo select de categoria
        function filtrarSubCategorias(selectCategoria) {
            const idx         = selectCategoria.dataset.index;
            const categoriaId = selectCategoria.value;
            const subSelect   = document.getElementById('sub_categoria_' + idx);

            if (!subSelect) return;

            // Esconde/mostra as options de subcategoria
            Array.from(subSelect.options).forEach(opt => {
                if (!opt.value) return; // mantém o placeholder
                const pertence = opt.dataset.categoria === categoriaId;
                opt.hidden   = !pertence;
                opt.disabled = !pertence;
            });

            // Reseta a seleção ao trocar categoria
            subSelect.value = '';
        }

        // Inicializa filtros ao carregar (para o caso de old())
        document.querySelectorAll('.categoria-select').forEach(sel => {
            if (sel.value) filtrarSubCategorias(sel);
        });

        function carregarItens(grupoId) {
            if (!grupoId) return;
            const url = new URL(window.location.href);
            url.searchParams.set('grupo_id', grupoId);
            window.location.href = url.toString();
        }
    </script>
</x-layouts.app>
