<x-layouts.app :title="__('Editar Entrada #' . $entradaEncomenda->id)">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-pencil-square class="w-5 h-5 text-blue-600" />
                Editar Entrada #{{ $entradaEncomenda->id }}
                <span class="text-sm font-normal text-zinc-400 ml-2">
                    (Cotação #{{ $entradaEncomenda->grupo_id }} — {{ $entradaEncomenda->grupo->cliente->nome_fantasia ?? '?' }})
                </span>
            </h2>

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('entrada_encomendas.update', $entradaEncomenda->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ── Cabeçalho ──────────────────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Recebimento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_recebimento" required
                               value="{{ old('data_recebimento', $entradaEncomenda->data_recebimento->format('Y-m-d')) }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Recebido por
                        </label>
                        <select name="recebido_por"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('recebido_por', $entradaEncomenda->recebido_por) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Entregue para
                        </label>
                        <select name="entregue_para"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Não entregue ainda</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('entregue_para', $entradaEncomenda->entregue_para) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Entrega ao vendedor
                        </label>
                        <input type="date" name="data_entrega"
                               value="{{ old('data_entrega', $entradaEncomenda->data_entrega?->format('Y-m-d')) }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>
                </div>

                {{-- ── Itens ───────────────────────────────────── --}}
                <h3 class="font-semibold text-zinc-700 dark:text-zinc-300 text-sm uppercase tracking-wider mb-3">Itens</h3>

                <div class="space-y-4 mb-6">
                    @foreach ($entradaEncomenda->itens as $itemEntrada)
                        @php $idx = $loop->index; @endphp

                        <div class="border {{ $itemEntrada->recebido_completo ? 'border-emerald-200 dark:border-emerald-700' : 'border-amber-200 dark:border-amber-700' }} rounded-xl overflow-hidden">

                            {{-- Cabeçalho do item --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-zinc-50 dark:bg-zinc-800">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                                    {{ $itemEntrada->consultaPreco->descricao }}
                                    @if ($itemEntrada->consultaPreco->cor)
                                        <span class="text-zinc-400 text-xs font-normal"> · {{ $itemEntrada->consultaPreco->cor->nome }}</span>
                                    @endif
                                </p>
                                @if ($itemEntrada->recebido_completo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        <x-heroicon-o-check-circle class="w-3 h-3" /> Completo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        <x-heroicon-o-clock class="w-3 h-3" />
                                        Pendente: {{ number_format($itemEntrada->quantidadePendente(), 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <div class="px-4 py-4">
                                <input type="hidden" name="itens[{{ $idx }}][id]" value="{{ $itemEntrada->id }}">

                                {{-- Quantidade + Observação --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Qtd Solicitada</label>
                                        <input type="number" readonly
                                               value="{{ $itemEntrada->quantidade_solicitada }}"
                                               class="w-full border border-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm bg-zinc-100 cursor-not-allowed text-zinc-400">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">
                                            Qtd Recebida <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" step="0.01" min="0" required
                                               name="itens[{{ $idx }}][quantidade_recebida]"
                                               value="{{ old("itens.{$idx}.quantidade_recebida", $itemEntrada->quantidade_recebida) }}"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        @error("itens.{$idx}.quantidade_recebida")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Observação</label>
                                        <input type="text"
                                               name="itens[{{ $idx }}][observacao]"
                                               value="{{ old("itens.{$idx}.observacao", $itemEntrada->observacao) }}"
                                               placeholder="Item faltante, avariado..."
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                </div>

                                {{-- Informações do produto --}}
                                <div class="border-t border-zinc-100 dark:border-zinc-700 pt-4">
                                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">
                                        Informações do produto <span class="font-normal normal-case">(opcionais)</span>
                                    </p>

                                    {{-- NCM · Código de Barras · SKU --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">NCM</label>
                                            <input type="text"
                                                   name="itens[{{ $idx }}][ncm]"
                                                   value="{{ old("itens.{$idx}.ncm", $itemEntrada->ncm) }}"
                                                   placeholder="Ex: 7610.10.00"
                                                   maxlength="20"
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Código de Barras (EAN)</label>
                                            <input type="text"
                                                   name="itens[{{ $idx }}][codigo_barras]"
                                                   value="{{ old("itens.{$idx}.codigo_barras", $itemEntrada->codigo_barras) }}"
                                                   placeholder="Ex: 7891234567890"
                                                   maxlength="50"
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">SKU / Código</label>
                                            <input type="text"
                                                   name="itens[{{ $idx }}][sku]"
                                                   value="{{ old("itens.{$idx}.sku", $itemEntrada->sku) }}"
                                                   placeholder="Ex: ALU-PERF-3M"
                                                   maxlength="50"
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>
                                    </div>

                                    {{-- Unidade · Peso · Categoria · Subcategoria --}}
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Unidade de Medida</label>
                                            <select name="itens[{{ $idx }}][unidade_medida]"
                                                    class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                                <option value="">Selecione...</option>
                                                @foreach ([
                                                    'UN'  => 'UN — Unidade',
                                                    'PC'  => 'PC — Peça',
                                                    'CX'  => 'CX — Caixa',
                                                    'KG'  => 'KG — Quilograma',
                                                    'G'   => 'G — Grama',
                                                    'M'   => 'M — Metro',
                                                    'M2'  => 'M² — Metro quadrado',
                                                    'M3'  => 'M³ — Metro cúbico',
                                                    'L'   => 'L — Litro',
                                                    'ML'  => 'ML — Mililitro',
                                                    'PAR' => 'PAR — Par',
                                                    'RL'  => 'RL — Rolo',
                                                    'PT'  => 'PT — Pacote',
                                                ] as $val => $label)
                                                    <option value="{{ $val }}"
                                                        {{ old("itens.{$idx}.unidade_medida", $itemEntrada->unidade_medida) == $val ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Peso (kg)</label>
                                            <input type="number" step="0.001" min="0"
                                                   name="itens[{{ $idx }}][peso]"
                                                   value="{{ old("itens.{$idx}.peso", $itemEntrada->peso) }}"
                                                   placeholder="Ex: 1.250"
                                                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Categoria</label>
                                            <select name="itens[{{ $idx }}][categoria_id]"
                                                    data-index="{{ $idx }}"
                                                    class="categoria-select w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                                    onchange="filtrarSubCategorias(this)">
                                                <option value="">Selecione...</option>
                                                @foreach ($categorias as $cat)
                                                    <option value="{{ $cat->id }}"
                                                        {{ old("itens.{$idx}.categoria_id", $itemEntrada->categoria_id) == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->nome }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-zinc-500 mb-1">Subcategoria</label>
                                            <select name="itens[{{ $idx }}][sub_categoria_id]"
                                                    id="sub_categoria_{{ $idx }}"
                                                    class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                                <option value="">Selecione a categoria primeiro</option>
                                                @foreach ($subCategorias as $sub)
                                                    @php
                                                        $catAtual = old("itens.{$idx}.categoria_id", $itemEntrada->categoria_id);
                                                        $subAtual = old("itens.{$idx}.sub_categoria_id", $itemEntrada->sub_categoria_id);
                                                    @endphp
                                                    <option value="{{ $sub->id }}"
                                                            data-categoria="{{ $sub->categoria_id }}"
                                                            {{ $subAtual == $sub->id ? 'selected' : '' }}
                                                            @if ($catAtual && $catAtual != $sub->categoria_id) hidden disabled @endif>
                                                        {{ $sub->nome }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Observação geral --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação Geral</label>
                    <textarea name="observacao" rows="2"
                              placeholder="Informações adicionais sobre este recebimento..."
                              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old('observacao', $entradaEncomenda->observacao) }}</textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit" variant="primary">
                        <x-heroicon-o-check class="w-4 h-4" /> Salvar Alterações
                    </x-button>
                    <a href="{{ route('entrada_encomendas.show', $entradaEncomenda->id) }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filtrarSubCategorias(selectCategoria) {
            const idx         = selectCategoria.dataset.index;
            const categoriaId = selectCategoria.value;
            const subSelect   = document.getElementById('sub_categoria_' + idx);
            if (!subSelect) return;

            Array.from(subSelect.options).forEach(opt => {
                if (!opt.value) {
                    opt.text = categoriaId ? 'Selecione a subcategoria...' : 'Selecione a categoria primeiro';
                    return;
                }
                const pertence = opt.dataset.categoria === categoriaId;
                opt.hidden   = !pertence;
                opt.disabled = !pertence;
            });
            subSelect.value = '';
        }

        // Inicializa filtros para categorias já selecionadas
        document.querySelectorAll('.categoria-select').forEach(sel => {
            if (sel.value) filtrarSubCategorias(sel);
        });
    </script>
</x-layouts.app>