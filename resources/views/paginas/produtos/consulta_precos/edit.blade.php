<x-layouts.app :title="__('Preencher Preços — Item de Cotação')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                    Preencher Preços — {{ $consulta->descricao }}
                </h2>
                <a href="{{ route('consulta_preco.show_grupo', $consulta->grupo_id) }}">
                    <x-button size="sm" variant="secondary">
                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                        Voltar
                    </x-button>
                </a>
            </div>

            {{-- Dados do item --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl text-sm">
                <div><span class="text-xs text-zinc-500 block">Quantidade</span> {{ $consulta->quantidade }}</div>
                <div><span class="text-xs text-zinc-500 block">Cor</span> {{ $consulta->cor->nome ?? '—' }}</div>
                <div><span class="text-xs text-zinc-500 block">Part Number</span> {{ $consulta->part_number ?? '—' }}</div>
                <div><span class="text-xs text-zinc-500 block">Cliente</span> {{ $consulta->grupo->cliente->nome_fantasia ?? '—' }}</div>
            </div>

            <form action="{{ route('consulta_preco.update', $consulta->id) }}" method="POST" id="form-edit">
                @csrf
                @method('PUT')

                {{-- Dados básicos editáveis --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <x-input name="descricao" label="Descrição"
                                 value="{{ old('descricao', $consulta->descricao) }}" required />
                    </div>
                    <div>
                        <x-input name="quantidade" label="Quantidade"
                                 value="{{ old('quantidade', $consulta->quantidade) }}" required />
                    </div>
                    <div>
                        <x-select name="cor_id" label="Cor">
                            <option value="">Selecione...</option>
                            @foreach ($cores as $cor)
                                <option value="{{ $cor->id }}"
                                    {{ old('cor_id', $consulta->cor_id) == $cor->id ? 'selected' : '' }}>
                                    {{ $cor->nome }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-input name="part_number" label="Part Number"
                                 value="{{ old('part_number', $consulta->part_number) }}" />
                    </div>
                </div>

                {{-- ─── FORNECEDORES ──────────────────────────────── --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-zinc-700 dark:text-zinc-300">Preços por Fornecedor</h3>
                        <button type="button" id="btn-add-fornecedor"
                                class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Adicionar fornecedor
                        </button>
                    </div>

                    <div id="fornecedores-container" class="space-y-3">
                        @php $fornIdx = 0; @endphp

                        {{-- Fornecedores já vinculados --}}
                        @forelse ($consulta->fornecedores as $fornVinculado)
                            <div class="fornecedor-row border border-zinc-200 dark:border-zinc-600 rounded-xl p-4 relative">
                                <input type="hidden"
                                       name="fornecedores[{{ $fornIdx }}][fornecedor_id]"
                                       value="{{ $fornVinculado->fornecedor_id }}">

                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Fornecedor</label>
                                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 py-2">
                                            {{ $fornVinculado->fornecedor->nome_fantasia }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Compra</label>
                                        <input type="text"
                                               name="fornecedores[{{ $fornIdx }}][preco_compra]"
                                               value="{{ old("fornecedores.{$fornIdx}.preco_compra", $fornVinculado->preco_compra ? number_format($fornVinculado->preco_compra, 2, ',', '.') : '') }}"
                                               placeholder="0,00"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                               oninput="mascaraMoeda(this)">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Venda</label>
                                        <input type="text"
                                               name="fornecedores[{{ $fornIdx }}][preco_venda]"
                                               value="{{ old("fornecedores.{$fornIdx}.preco_venda", $fornVinculado->preco_venda ? number_format($fornVinculado->preco_venda, 2, ',', '.') : '') }}"
                                               placeholder="0,00"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                               oninput="mascaraMoeda(this)">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Prazo Entrega</label>
                                        <input type="text"
                                               name="fornecedores[{{ $fornIdx }}][prazo_entrega]"
                                               value="{{ old("fornecedores.{$fornIdx}.prazo_entrega", $fornVinculado->prazo_entrega) }}"
                                               placeholder="Ex: 7 dias"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio"
                                                   name="fornecedor_selecionado_id"
                                                   value="{{ $fornVinculado->fornecedor_id }}"
                                                   {{ $fornVinculado->selecionado ? 'checked' : '' }}
                                                   class="w-4 h-4 text-emerald-600">
                                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Selecionar</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @php $fornIdx++; @endphp
                        @empty
                            {{-- Linha inicial vazia quando não há fornecedores vinculados --}}
                            <div class="fornecedor-row border border-zinc-200 dark:border-zinc-600 rounded-xl p-4">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Fornecedor</label>
                                        <select name="fornecedores[0][fornecedor_id]"
                                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                            <option value="">Selecione...</option>
                                            @foreach ($fornecedores as $f)
                                                <option value="{{ $f->id }}">{{ $f->nome_fantasia }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Compra</label>
                                        <input type="text" name="fornecedores[0][preco_compra]" placeholder="0,00"
                                               oninput="mascaraMoeda(this)"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Venda</label>
                                        <input type="text" name="fornecedores[0][preco_venda]" placeholder="0,00"
                                               oninput="mascaraMoeda(this)"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Prazo Entrega</label>
                                        <input type="text" name="fornecedores[0][prazo_entrega]" placeholder="Ex: 7 dias"
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="fornecedor_selecionado_id" value=""
                                                   class="w-4 h-4 text-emerald-600">
                                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Selecionar</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @php $fornIdx = 1; @endphp
                        @endforelse

                    </div>

                    {{-- Adicionar novo fornecedor (não cadastrado ainda no item) --}}
                    <div id="novos-fornecedores" class="space-y-3 mt-3"></div>
                </div>

                {{-- Observação --}}
                <div class="mb-6">
                    <x-textarea name="observacao" label="Observação" rows="2">{{ old('observacao', $consulta->observacao) }}</x-textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Salvar Preços
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Template para novo fornecedor --}}
    <template id="template-fornecedor">
        <div class="fornecedor-row border border-zinc-200 dark:border-zinc-600 rounded-xl p-4 relative">
            <button type="button" onclick="this.closest('.fornecedor-row').remove()"
                    class="absolute top-2 right-2 text-red-400 hover:text-red-600 text-xs">✕</button>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Fornecedor</label>
                    <select name="fornecedores[__IDX__][fornecedor_id]"
                            class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        <option value="">Selecione...</option>
                        @foreach ($fornecedores as $f)
                            <option value="{{ $f->id }}">{{ $f->nome_fantasia }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Compra</label>
                    <input type="text" name="fornecedores[__IDX__][preco_compra]" placeholder="0,00"
                           oninput="mascaraMoeda(this)"
                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Venda</label>
                    <input type="text" name="fornecedores[__IDX__][preco_venda]" placeholder="0,00"
                           oninput="mascaraMoeda(this)"
                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Prazo Entrega</label>
                    <input type="text" name="fornecedores[__IDX__][prazo_entrega]" placeholder="Ex: 7 dias"
                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        {{-- ✅ CORRETO — value preenchido dinamicamente via JS --}}
                        <input type="radio" name="fornecedor_selecionado_id" value="0" class="radio-selecionar w-4 h-4 text-emerald-600">
                        <span class="text-xs text-zinc-600 dark:text-zinc-400">Selecionar</span>
                    </label>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[name*="[fornecedor_id]"]')) {
                const row = e.target.closest('.fornecedor-row');
                if (!row) return;
                const radio = row.querySelector('input[type="radio"][name="fornecedor_selecionado_id"]');
                if (radio) {
                    radio.value = e.target.value;
                }
            }
        });

        // ✅ Inicializa os values dos radios já carregados na página
        document.querySelectorAll('.fornecedor-row').forEach(function(row) {
            const select = row.querySelector('select[name*="[fornecedor_id]"]');
            const radio  = row.querySelector('input[type="radio"][name="fornecedor_selecionado_id"]');
            if (select && radio && !radio.value) {
                radio.value = select.value;
            }
        });

        //  Recupera novos fornecedores adicionados via JS que foram perdidos na validação
        const oldFornecedores = @json(old('fornecedores', []));
        let fornIdx = {{ $fornIdx }};

        function mascaraMoeda(el) {
            let value = el.value
                .replace(/\D/g, '')
                .replace(/(\d)(\d{2})$/, '$1,$2')
                .replace(/(?=(\d{3})+(\D))\B/g, '.');
            el.value = value;
        }

        function criarLinhaFornecedor(idx, dados = {}) {
            const fornecedores = @json($fornecedores->map(fn($f) => ['id' => $f->id, 'nome' => $f->nome_fantasia]));

            let ops = '<option value="">Selecione...</option>';
            fornecedores.forEach(f => {
                const sel = dados.fornecedor_id == f.id ? 'selected' : '';
                ops += `<option value="${f.id}" ${sel}>${f.nome}</option>`;
            });

            const selecionadoId = @json(old('fornecedor_selecionado_id', $consulta->fornecedorSelecionado?->fornecedor_id ?? ''));

            return `
        <div class="fornecedor-row border border-zinc-200 dark:border-zinc-600 rounded-xl p-4 relative">
            <button type="button" onclick="this.closest('.fornecedor-row').remove()"
                class="absolute top-2 right-2 text-red-400 hover:text-red-600 text-xs">✕ remover</button>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Fornecedor</label>
                    <select name="fornecedores[${idx}][fornecedor_id]"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        ${ops}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Compra</label>
                    <input type="text" name="fornecedores[${idx}][preco_compra]"
                        value="${dados.preco_compra || ''}"
                        placeholder="0,00" oninput="mascaraMoeda(this)"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Preço Venda</label>
                    <input type="text" name="fornecedores[${idx}][preco_venda]"
                        value="${dados.preco_venda || ''}"
                        placeholder="0,00" oninput="mascaraMoeda(this)"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Prazo Entrega</label>
                    <input type="text" name="fornecedores[${idx}][prazo_entrega]"
                        value="${dados.prazo_entrega || ''}"
                        placeholder="Ex: 7 dias"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="fornecedor_selecionado_id"
                            value="${dados.fornecedor_id || ''}"
                            ${selecionadoId == dados.fornecedor_id ? 'checked' : ''}
                            class="w-4 h-4 text-emerald-600">
                        <span class="text-xs text-zinc-600 dark:text-zinc-400">Selecionar</span>
                    </label>
                </div>
            </div>
        </div>`;
        }

        function inicializarNovosForncedores() {
            const container = document.getElementById('novos-fornecedores');

            // Se veio do old() com índices além dos já renderizados pelo Blade
            // (fornIdx é o próximo índice após os do banco)
            if (oldFornecedores.length > {{ $fornIdx }}) {
                for (let i = {{ $fornIdx }}; i < oldFornecedores.length; i++) {
                    container.insertAdjacentHTML('beforeend', criarLinhaFornecedor(i, oldFornecedores[i]));
                    fornIdx = i + 1;
                }
            }
        }

        document.getElementById('btn-add-fornecedor').addEventListener('click', function () {
            const container = document.getElementById('novos-fornecedores');
            container.insertAdjacentHTML('beforeend', criarLinhaFornecedor(fornIdx, {}));
            fornIdx++;
        });

        // ✅ Restaura valores do old() nos fornecedores já renderizados pelo Blade
        // (cobre caso de erro de validação nos campos dos fornecedores existentes)
        (function restaurarOldFornecedoresExistentes() {
            if (!oldFornecedores.length) return;

            document.querySelectorAll('#fornecedores-container .fornecedor-row').forEach(function (row, i) {
                if (!oldFornecedores[i]) return;
                const dados = oldFornecedores[i];

                const compra = row.querySelector('[name$="[preco_compra]"]');
                const venda  = row.querySelector('[name$="[preco_venda]"]');
                const prazo  = row.querySelector('[name$="[prazo_entrega]"]');

                if (compra && dados.preco_compra) compra.value = dados.preco_compra;
                if (venda  && dados.preco_venda)  venda.value  = dados.preco_venda;
                if (prazo  && dados.prazo_entrega) prazo.value = dados.prazo_entrega;
            });
        })();

        inicializarNovosForncedores();
    </script>
</x-layouts.app>
