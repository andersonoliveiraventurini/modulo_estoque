<x-layouts.app :title="__('Criar Movimentação')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-truck class="w-5 h-5 text-indigo-500" /> 
                        Criar Movimentação
                    </h2>
                    <a href="{{ route('movimentacao.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Voltar</a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded text-sm">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('movimentacao.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="form-movimentacao">
                    @csrf

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5" /> 
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-select name="tipo_entrada" label="Tipo de movimentação">
                                <option value="">Selecione</option>
                                <option value="entrada" {{ old('tipo_entrada') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ old('tipo_entrada') == 'saida' ? 'selected' : '' }}>Saída</option>
                            </x-select>
                            <x-input name="data_movimentacao" label="Data da Movimentação" type="date" value="{{ old('data_movimentacao', now()->format('Y-m-d')) }}" />
                            <x-input name="nota_fiscal_fornecedor" label="Nota Fiscal Fornecedor" placeholder="(opcional)" value="{{ old('nota_fiscal_fornecedor') }}" />
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Arquivo NF (PDF/Imagem)</label>
                                <input type="file" name="arquivo_nota_fiscal" accept=".pdf,.jpg,.jpeg,.png"
                                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-neutral-700 rounded-md shadow-sm bg-white dark:bg-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Pedido de Compra Vinculado <span class="text-xs text-gray-400">(opcional)</span>
                                </label>
                                <div class="flex gap-2">
                                    <select name="pedido_compra_id" id="pedido_compra_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">Nenhum</option>
                                        @foreach($pedidoCompras as $pc)
                                            <option value="{{ $pc->id }}" {{ old('pedido_compra_id') == $pc->id ? 'selected' : '' }}>
                                                #{{ $pc->id }} – {{ optional($pc->fornecedor)->nome_fantasia }} – {{ $pc->data_pedido->format('d/m/Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="carregarItensPedido()" id="btn-carregar-itens"
                                        class="mt-1 px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 transition flex items-center gap-1">
                                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                                        Carregar
                                    </button>
                                </div>
                            </div>

                            <x-input name="romaneiro" label="Romaneiro" placeholder="(opcional)" value="{{ old('romaneiro') }}" />
                            
                            <div class="flex items-center gap-6 mt-6 md:mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_reposicao" value="1" {{ old('is_reposicao') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">É Reposição?</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_devolucao" value="1" {{ old('is_devolucao') ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">É Devolução?</span>
                                </label>
                            </div>

                            <div class="md:col-span-3">
                                <x-input name="observacao" label="Observação Geral" placeholder="..." value="{{ old('observacao') }}" />
                            </div>
                        </div>
                    </div>

                    {{-- === Pesquisa de Produtos (Livewire) === --}}
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700" id="secao-busca-produtos">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-blue-600" />
                            Buscar Produtos
                        </h3>
                        <livewire:lista-produto-orcamento :showStock="false" :showDiscount="false" />
                    </div>

                    <!-- Tabela de Itens Selecionados -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-archive-box class="w-5 h-5 text-indigo-600" />                             
                            Itens para Movimentação
                        </h3>

                        <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">WT Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cor</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-20">Qtd.</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-20">Qtd. Vend.</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-32">V. Unit.</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Perecível</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Vencimento</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Enc.</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Localização</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Obs.</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-20">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="produtos-selecionados" class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    {{-- Preenchido via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                         <x-button type="submit" >Cadastrar Movimentação</x-button>
                        <x-button type="reset" variant="secondary">Limpar Formulário</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Modal Confirmação de Item --}}
    <div id="modal-confirmacao" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-full max-w-2xl shadow-xl relative overflow-y-auto max-h-[90vh]">
            <button onclick="fecharModal()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Detalhes do Item</h3>
            <p id="modal-produto-nome" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium mb-4"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ is_encomenda: false }">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade *</label>
                    <input id="modal-quantidade-input" type="number" step="0.01" value="1"
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor Unitário (R$)</label>
                    <input id="modal-preco-input" type="number" step="0.01" value="0.00" readonly
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-500 rounded-md px-3 py-2 cursor-not-allowed" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WT Code</label>
                    <input id="modal-wt-code-input" type="text" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cor</label>
                    <input id="modal-cor-input" type="text" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cód. Fornecedor</label>
                    <input id="modal-codigo-fornecedor-input" type="text" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qtd. Vendida</label>
                    <input id="modal-quantidade-vendida-input" type="number" step="0.01" value="0" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox" id="modal-is-encomenda-input" x-model="is_encomenda" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Encomenda?</label>
                </div>
                <div x-show="is_encomenda">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nº Pedido *</label>
                    <input id="modal-numero-pedido-input" type="text" :required="is_encomenda" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                    <select id="modal-vendedor-select" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2">
                        <option value="">Selecione</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}">{{ $v->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <livewire:seletor-enderecamento />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Data de Vencimento <span id="modal-vencimento-obrigatorio" class="text-red-500 hidden">*</span>
                    </label>
                    <input id="modal-vencimento-input" type="date" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 disabled:bg-gray-100 disabled:cursor-not-allowed" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observação do Item</label>
                    <input id="modal-obs-input" type="text" placeholder="Ex: pacote aberto..." class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
            </div>

            <button onclick="confirmarAdicao()" 
                class="w-full mt-6 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                Adicionar Item
            </button>
        </div>
    </div>

    <livewire:estoque.alocacao-modal />

    @push('scripts')
    <script>
        let produtosSelecionados = @json(old('produtos', []));
        let itemSendoAlocado = null;
        let produtoSendoAdicionado = null;
        let enderecamentoAtual = { armazem_id: null, corredor_id: null, posicao_id: null };

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('enderecamento-atualizado', (data) => {
                const values = Array.isArray(data) ? data[0] : data;
                enderecamentoAtual = values;
            });

            Livewire.on('alocacao-confirmada', (data) => {
                const values = Array.isArray(data) ? data[0] : data;
                if (itemSendoAlocado) {
                    itemSendoAlocado.alocacoes = values.allocations;
                    produtosSelecionados.push(itemSendoAlocado);
                    renderTable();
                    fecharModal();
                    itemSendoAlocado = null;
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (produtosSelecionados.length > 0) {
                // Se veio por old(), os campos de valor/quantidade podem estar como strings
                produtosSelecionados.forEach(p => {
                    p.quantidade = parseFloat(p.quantidade) || 0;
                    p.valor_unitario = parseFloat(p.valor) || parseFloat(p.valor_unitario) || 0;
                    // Mapear campos que podem vir do request/old
                    p.nome = p.nome || '';
                    p.cor = p.cor || '';
                    p.fornecedor = p.fornecedor || p.fornecedor_nome || '';
                });
                renderTable();
            }
        });

        // Bridge com o Livewire lista-produto-orcamento
        function selecionarProdutoComQuantidade(id, nome, preco, fornecedor, cor, partNumber, liberarDesconto, estoque, isPerishable) {
            produtoSendoAdicionado = { 
                id: id, 
                nome: nome, 
                preco: preco, 
                fornecedor: fornecedor, 
                cor: cor,
                is_perishable: isPerishable,
                maxQtd: null // Para alerta de inconsistência
            };

            document.getElementById('modal-produto-nome').textContent = nome + (cor ? ' ('+cor+')' : '') + (isPerishable ? ' [Perecível]' : '');
            document.getElementById('modal-preco-input').value = preco || 0;
            document.getElementById('modal-quantidade-input').value = 1;
            document.getElementById('modal-quantidade-vendida-input').value = 0;
            document.getElementById('modal-wt-code-input').value = '';
            document.getElementById('modal-cor-input').value = cor || '';
            document.getElementById('modal-codigo-fornecedor-input').value = '';
            document.getElementById('modal-is-encomenda-input').checked = false;
            document.getElementById('modal-numero-pedido-input').value = '';
            document.getElementById('modal-vendedor-select').value = '';
            document.getElementById('modal-obs-input').value = '';
            document.getElementById('modal-vencimento-input').value = '';
            
            // Lógica de perecível no modal
            const vInput = document.getElementById('modal-vencimento-input');
            const vStar = document.getElementById('modal-vencimento-obrigatorio');
            if (isPerishable) {
                vInput.disabled = false;
                vStar.classList.remove('hidden');
            } else {
                vInput.disabled = true;
                vStar.classList.add('hidden');
            }
            
            document.getElementById('modal-confirmacao').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modal-confirmacao').classList.add('hidden');
            produtoSendoAdicionado = null;
        }

        function confirmarAdicao() {
            if (!produtoSendoAdicionado) return;

            const qtd = parseFloat(document.getElementById('modal-quantidade-input').value) || 0;
            if (qtd <= 0) {
                alert('A quantidade deve ser maior que zero.');
                return;
            }

            const dataVencimento = document.getElementById('modal-vencimento-input').value;
            if (produtoSendoAdicionado.is_perishable && !dataVencimento) {
                alert('A data de vencimento é obrigatória para produtos perecíveis.');
                return;
            }

            const is_encomenda = document.getElementById('modal-is-encomenda-input').checked;
            const numero_pedido = document.getElementById('modal-numero-pedido-input').value;
            if (is_encomenda && !numero_pedido) {
                alert('O número do pedido é obrigatório para encomendas.');
                return;
            }

            itemSendoAlocado = {
                produto_id: produtoSendoAdicionado.id,
                nome: produtoSendoAdicionado.nome,
                cor: document.getElementById('modal-cor-input').value,
                wt_code: document.getElementById('modal-wt-code-input').value,
                codigo_fornecedor: document.getElementById('modal-codigo-fornecedor-input').value,
                fornecedor: produtoSendoAdicionado.fornecedor,
                quantidade: qtd,
                quantidade_vendida: parseFloat(document.getElementById('modal-quantidade-vendida-input').value) || 0,
                valor_unitario: parseFloat(document.getElementById('modal-preco-input').value) || 0,
                armazem_id: enderecamentoAtual.armazem_id,
                corredor_id: enderecamentoAtual.corredor_id,
                posicao_id: enderecamentoAtual.posicao_id,
                observacao: document.getElementById('modal-obs-input').value,
                data_vencimento: dataVencimento,
                is_perishable: produtoSendoAdicionado.is_perishable,
                is_encomenda: is_encomenda,
                numero_pedido: numero_pedido,
                vendedor_id: document.getElementById('modal-vendedor-select').value,
                vendedor_nome: document.getElementById('modal-vendedor-select').options[document.getElementById('modal-vendedor-select').selectedIndex]?.text || '',
                maxQtd: produtoSendoAdicionado.maxQtd,
                locked: produtoSendoAdicionado.locked || false 
            };

            // Abrir Modal de Alocação
            Livewire.dispatch('abrir-alocacao', {
                data: {
                    productId: itemSendoAlocado.produto_id,
                    productName: itemSendoAlocado.nome,
                    totalQuantity: itemSendoAlocado.quantidade,
                    existingAllocations: []
                }
            });
        }

        function removerItem(index) {
            produtosSelecionados.splice(index, 1);
            renderTable();
        }

        function editarAlocacao(index) {
            const item = produtosSelecionados[index];
            itemSendoAlocado = item;
            
            // Remove o item da lista original temporariamente (será readicionado ao confirmar)
            produtosSelecionados.splice(index, 1);

            Livewire.dispatch('abrir-alocacao', {
                data: {
                    productId: item.produto_id,
                    productName: item.nome,
                    totalQuantity: item.quantidade,
                    existingAllocations: item.alocacoes
                }
            });
        }

        function renderTable() {
            const tbody = document.getElementById('produtos-selecionados');
            tbody.innerHTML = '';

            produtosSelecionados.forEach((p, i) => {
                const totalItem = p.quantidade * p.valor_unitario;
                
                // Alerta de Inconsistência
                let alertClass = "";
                if (p.maxQtd && p.quantidade > p.maxQtd) {
                    alertClass = "bg-red-50 text-red-700 border-red-200";
                }

                const row = `
                    <tr class="${alertClass} dark:border-neutral-700">
                        <td class="px-4 py-2">
                            <span class="font-medium text-sm block">${p.nome}</span>
                            <small class="text-xs text-gray-400">${p.fornecedor || 'S/F'}</small>
                            <input type="hidden" name="produtos[${i}][produto_id]" value="${p.produto_id}">
                            <input type="hidden" name="produtos[${i}][valor_total]" value="${totalItem.toFixed(2)}">
                            <input type="hidden" name="produtos[${i}][fornecedor_id]" value="${p.fornecedor_id || ''}">
                            <input type="hidden" name="produtos[${i}][nome]" value="${p.nome || ''}">
                            <input type="hidden" name="produtos[${i}][fornecedor_nome]" value="${p.fornecedor || ''}">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="produtos[${i}][wt_code]" value="${p.wt_code || ''}" 
                                onchange="updateItem(${i}, 'wt_code', this.value)"
                                class="w-24 border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-xs p-1">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="produtos[${i}][cor]" value="${p.cor || ''}" 
                                onchange="updateItem(${i}, 'cor', this.value)"
                                class="w-24 border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-xs p-1">
                            <input type="hidden" name="produtos[${i}][codigo_fornecedor]" value="${p.codigo_fornecedor || ''}">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" step="0.01" name="produtos[${i}][quantidade]" value="${p.quantidade}" 
                                onchange="updateItem(${i}, 'quantidade', this.value)"
                                class="w-20 text-center border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" step="0.01" name="produtos[${i}][quantidade_vendida]" value="${p.quantidade_vendida || 0}" 
                                onchange="updateItem(${i}, 'quantidade_vendida', this.value)"
                                class="w-20 text-center border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" name="produtos[${i}][valor]" value="${p.valor_unitario.toFixed(2)}" 
                                readonly
                                class="w-full border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-500 rounded text-sm p-1 cursor-not-allowed">
                        </td>
                        <td class="px-4 py-2 text-center">
                            ${p.is_perishable 
                                ? '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Sim</span>' 
                                : '<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded-full">Não</span>'}
                            <input type="hidden" name="produtos[${i}][is_perishable]" value="${p.is_perishable ? 1 : 0}">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <input type="date" name="produtos[${i}][data_vencimento]" value="${p.data_vencimento || ''}" 
                                    onchange="updateItem(${i}, 'data_vencimento', this.value)"
                                    ${p.is_perishable ? 'required' : 'disabled'}
                                    class="w-32 text-xs border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded p-1 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                ${p.is_perishable && !p.data_vencimento ? '<span class="text-[10px] text-red-500 font-bold">Obrigatório*</span>' : ''}
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <input type="checkbox" name="produtos[${i}][is_encomenda]" value="1" ${p.is_encomenda ? 'checked' : ''}
                                    onchange="updateItem(${i}, 'is_encomenda', this.checked)"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                ${p.is_encomenda ? `<span class="text-[10px] text-indigo-600 font-bold">${p.numero_pedido || 'S/N'}</span>` : ''}
                                <input type="hidden" name="produtos[${i}][numero_pedido]" value="${p.numero_pedido || ''}">
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs text-gray-600 dark:text-gray-400">${p.vendedor_nome || '-'}</span>
                            <input type="hidden" name="produtos[${i}][vendedor_id]" value="${p.vendedor_id || ''}">
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                    ${p.alocacoes?.length || 0} Local(is)
                                </span>
                                <div class="flex flex-col gap-0.5">
                                    ${p.alocacoes?.map(a => `
                                        <span class="text-[10px] text-gray-500">${a.posicao_nome}: ${a.quantity}</span>
                                        <input type="hidden" name="produtos[${i}][alocacoes][${a.posicao_id}][posicao_id]" value="${a.posicao_id}">
                                        <input type="hidden" name="produtos[${i}][alocacoes][${a.posicao_id}][quantidade]" value="${a.quantity}">
                                    `).join('')}
                                </div>
                                <button type="button" onclick="editarAlocacao(${i})" class="text-[10px] text-indigo-500 hover:underline">Editar</button>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="produtos[${i}][observacao]" value="${p.observacao || ''}" 
                                onchange="updateItem(${i}, 'observacao', this.value)"
                                class="w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button type="button" onclick="removerItem(${i})" class="text-red-500 hover:text-red-700">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        function updateItem(index, field, value) {
            if (field === 'quantidade') {
                const val = parseFloat(value) || 0;
                produtosSelecionados[index][field] = val;

                // Checar alerta se for quantidade
                if (field === 'quantidade' && produtosSelecionados[index].maxQtd && val > produtosSelecionados[index].maxQtd) {
                    alert('AVISO: Quantidade superior ao pedido (' + produtosSelecionados[index].maxQtd + ').');
                }
            } else {
                produtosSelecionados[index][field] = value;
            }
            renderTable();
        }

        async function carregarItensPedido() {
            const pedidoId = document.getElementById('pedido_compra_id').value;
            if(!pedidoId) {
                alert('Selecione um Pedido de Compra primeiro.');
                return;
            }

            const btn = document.getElementById('btn-carregar-itens');
            btn.disabled = true;
            btn.textContent = 'Carregando...';

            try {
                const response = await fetch(`/pedido_compras/${pedidoId}/itens-json`);
                const itens = await response.json();

                if(!itens || itens.length === 0) {
                    alert('Nenhum item encontrado neste pedido.');
                } else {
                    // Limpar atuais ou apenas adicionar? O usuário pediu para "carregar", geralmente substitui o que está ali ou acrescenta.
                    // Vamos acrescentar para permitir conferência mista.
                    itens.forEach(item => {
                        produtosSelecionados.push({
                            produto_id: item.produto_id,
                            nome: item.nome,
                            cor: item.cor,
                            fornecedor: '', 
                            fornecedor_id: item.fornecedor_id,
                            quantidade: item.quantidade,
                            valor_unitario: item.valor_unitario,
                            armazem: 'HUB',
                            corredor: '',
                            posicao: '',
                            observacao: '',
                            data_vencimento: '',
                            is_perishable: item.is_perishable || false,
                            maxQtd: item.quantidade,
                            locked: true
                        });
                    });
                    renderTable();
                    alert(itens.length + ' itens carregados.');

                    // Esconder busca ao carregar pedido? Talvez não, pode-se querer adicionar itens extras.
                    // document.getElementById('secao-busca-produtos').classList.add('hidden');
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao buscar itens.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Carregar
                `;
            }
        }
    </script>
    @endpush
</x-layouts.app>
