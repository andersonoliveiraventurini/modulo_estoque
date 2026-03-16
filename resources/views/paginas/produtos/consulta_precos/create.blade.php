<x-layouts.app :title="__('Nova Cotação')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                Nova Cotação — Cliente: {{ $cliente->nome_fantasia }}
            </h2>

            @if($cliente->bloqueado)
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 rounded-md">
                    <div class="flex items-center">
                        <x-heroicon-s-lock-closed class="h-5 w-5 mr-2" />
                        <p class="font-bold text-lg">Atenção: Cliente Bloqueado</p>
                    </div>
                    <p class="mt-2 text-sm">Este cliente está bloqueado. Descontos exigirão aprovação (independente do limite do vendedor) e opções de pagamento faturadas não devem estar disponíveis ao gerar o pedido futuro.</p>
                    @if($cliente->ultimoBloqueio)
                        <p class="mt-1 text-sm font-medium">Motivo: {{ $cliente->ultimoBloqueio->motivo }}</p>
                    @endif
                </div>
            @endif

            <form action="{{ route('consulta_preco.store') }}" method="POST" id="form-cotacao">
                @csrf
                <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">

                {{-- Observação geral do grupo --}}
                <div class="mb-6">
                    <x-textarea name="observacao_geral" label="Observação geral da cotação"
                                placeholder="Informações gerais para o time de compras..." rows="2">{{ old('observacao_geral') }}</x-textarea>
                </div>

                {{-- ─── LISTA DE ITENS ─────────────────────────────── --}}
                <div id="itens-container" class="space-y-4">
                    {{-- Item inicial --}}
                    <div class="item-cotacao bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl p-4" data-index="0">
                        @include('paginas.produtos.consulta_precos._item_form', [
                            'index' => 0,
                            'cores' => $cores,
                            'fornecedores' => $fornecedores,
                        ])
                    </div>
                </div>

                {{-- ─── BOTÃO ADICIONAR ITEM ──────────────────────── --}}
                <div class="mt-4">
                    <button type="button" id="btn-adicionar-item"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed border-blue-300 text-blue-600 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors text-sm font-medium">
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        Adicionar outro item
                    </button>
                </div>

                {{-- ─── AÇÕES ──────────────────────────────────────── --}}
                <div class="flex gap-4 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        Solicitar Cotação
                    </x-button>
                    <a href="{{ route('consulta_preco.index') }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Template do item para clonagem via JS --}}
    <template id="template-item">
        <div class="item-cotacao bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl p-4" data-index="__INDEX__">
            @include('paginas.produtos.consulta_precos._item_form', [
                'index' => '__INDEX__',
                'cores' => $cores,
                'fornecedores' => $fornecedores,
            ])
        </div>
    </template>

    <script>
        // ✅ Dados salvos pelo old() para repovoar após erro de validação
        const oldItens = @json(old('itens', []));
        let itemIndex = 0;

        function criarItemHtml(index, dados = {}) {
            const cores = @json($cores->map(fn($c) => ['id' => $c->id, 'nome' => $c->nome]));
            const fornecedores = @json($fornecedores->map(fn($f) => ['id' => $f->id, 'nome' => $f->nome_fantasia]));

            let opsCores = '<option value="">Selecione...</option>';
            cores.forEach(c => {
                const sel = dados.cor_id == c.id ? 'selected' : '';
                opsCores += `<option value="${c.id}" ${sel}>${c.nome}</option>`;
            });

            let opsForn = '<option value="">Sem preferência</option>';
            fornecedores.forEach(f => {
                const sel = dados.fornecedor_ids && dados.fornecedor_ids[0] == f.id ? 'selected' : '';
                opsForn += `<option value="${f.id}" ${sel}>${f.nome}</option>`;
            });

            return `
        <div class="item-cotacao bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl p-4" data-index="${index}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Item #${index + 1}</span>
                <button type="button" onclick="removerItem(this)"
                    class="btn-remover-item hidden items-center gap-1 text-xs text-red-500 hover:text-red-700 transition-colors">
                    🗑 Remover item
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" name="itens[${index}][descricao]" placeholder="Descrição do produto/item"
                        required value="${dados.descricao || ''}"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Quantidade <span class="text-red-500">*</span></label>
                    <input type="number" name="itens[${index}][quantidade]" placeholder="Qtd" min="1"
                        required value="${dados.quantidade || ''}"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Cor</label>
                    <select name="itens[${index}][cor_id]"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        ${opsCores}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Part Number</label>
                    <input type="text" name="itens[${index}][part_number]" placeholder="Part number (opcional)"
                        value="${dados.part_number || ''}"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Fornecedor sugerido (opcional)</label>
                    <select name="itens[${index}][fornecedor_ids][]"
                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        ${opsForn}
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Observação do item</label>
                <textarea name="itens[${index}][observacao]" rows="2"
                    placeholder="Detalhes adicionais para este item..."
                    class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                >${dados.observacao || ''}</textarea>
            </div>
        </div>`;
        }

        function inicializar() {
            const container = document.getElementById('itens-container');
            container.innerHTML = '';

            // Se houver dados do old(), restaura todos os itens
            if (oldItens && oldItens.length > 0) {
                oldItens.forEach(function (dados, i) {
                    container.insertAdjacentHTML('beforeend', criarItemHtml(i, dados));
                    itemIndex = i + 1;
                });
            } else {
                // Primeiro acesso — exibe um item vazio
                container.insertAdjacentHTML('beforeend', criarItemHtml(0, {}));
                itemIndex = 1;
            }

            atualizarBotoesRemover();
        }

        document.getElementById('btn-adicionar-item').addEventListener('click', function () {
            const container = document.getElementById('itens-container');
            container.insertAdjacentHTML('beforeend', criarItemHtml(itemIndex, {}));
            itemIndex++;
            atualizarBotoesRemover();
        });

        function removerItem(btn) {
            btn.closest('.item-cotacao').remove();
            // Renumera índices visuais (não os name[], não precisa mudar)
            document.querySelectorAll('.item-cotacao').forEach(function (item, i) {
                const num = item.querySelector('.item-numero');
                if (num) num.textContent = i + 1;
            });
            atualizarBotoesRemover();
        }

        function atualizarBotoesRemover() {
            const itens = document.querySelectorAll('.item-cotacao');
            itens.forEach(function (item) {
                const btn = item.querySelector('.btn-remover-item');
                if (btn) btn.style.display = itens.length > 1 ? 'flex' : 'none';
            });
        }

        inicializar();
    </script>
</x-layouts.app>
