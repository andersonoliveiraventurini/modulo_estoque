<x-layouts.app title="Novo Pedido de Compra">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-indigo-500" />
                        Novo Pedido de Compra
                    </h2>
                    <a href="{{ route('pedido_compras.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Voltar</a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded text-sm">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('pedido_compras.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- Dados Gerais --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dados Gerais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor *</label>
                                <select name="fornecedor_id" id="fornecedor_id_select" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Selecione um fornecedor</option>
                                    @foreach($fornecedores as $f)
                                        <option value="{{ $f->id }}" data-cnpj="{{ preg_replace('/\D/', '', $f->cnpj) }}" {{ old('fornecedor_id') == $f->id ? 'selected' : '' }}>{{ $f->nome_fantasia }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input name="numero_pedido" label="Nº do Pedido / Referência" placeholder="Ex: PO-2024-001" value="{{ old('numero_pedido') }}" />
                            <x-input name="data_pedido" label="Data do Pedido *" type="date" value="{{ old('data_pedido', date('Y-m-d')) }}" />
                            <x-input name="previsao_entrega" label="Previsão de Entrega" type="date" value="{{ old('previsao_entrega') }}" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condição de Pagamento *</label>
                                <select name="condicao_pagamento_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="">Selecione</option>
                                    @foreach($condicoes as $c)
                                        <option value="{{ $c->id }}" {{ old('condicao_pagamento_id') == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-input name="forma_pagamento_descricao" label="Detalhes Pag. / Parcelas" placeholder="Ex: 30/60/90 dias" value="{{ old('forma_pagamento_descricao') }}" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo do Pedido (PDF/Imagem)</label>
                                <input type="file" name="arquivo_pedido" accept=".pdf,.jpg,.jpeg,.png"
                                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-neutral-700 rounded-md shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                            </div>

                            <div class="md:col-span-3">
                                <x-input name="observacao" label="Observações" placeholder="Informações adicionais..." value="{{ old('observacao') }}" />
                            </div>
                        </div>
                    </div>

                    {{-- === Pesquisa de Produtos (Livewire) === --}}
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-blue-600" />
                            Buscar Produtos
                        </h3>
                        <livewire:lista-produto-orcamento :showStock="false" :showDiscount="false" />
                    </div>

                    {{-- === Itens do Pedido === --}}
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-list-bullet class="w-5 h-5 text-indigo-600" />
                            Itens do Pedido
                        </h3>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="puxarEstoqueMinimo()" class="flex items-center gap-1 px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-xs font-semibold hover:bg-amber-100 transition shadow-sm">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                Puxar Estoque Mínimo
                            </button>
                            <button type="button" onclick="puxarFaltas()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md text-xs font-semibold hover:bg-indigo-100 transition shadow-sm">
                                <x-heroicon-o-queue-list class="w-4 h-4" />
                                Puxar Faltas Pendentes
                            </button>
                        </div>

                        <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cód.</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produto</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cor</th>
                                        <th scope="col" class="w-24 px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Qtd.</th>
                                        <th scope="col" class="w-32 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">V. Unitário</th>
                                        <th scope="col" class="w-32 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">V. Total</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Obs.</th>
                                        <th scope="col" class="w-20 px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="produtos-selecionados" class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    {{-- Preenchido via JS --}}
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-4 p-4 bg-gray-50 dark:bg-neutral-800 rounded-lg">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total do Pedido: </span>
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-100" id="total-geral">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <x-button type="submit" variant="primary">Criar Pedido de Compra</x-button>
                        <x-button type="reset" variant="ghost">Limpar Tudo</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Quantidade --}}
    <div id="modal-quantidade" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-96 shadow-xl relative">
            <button onclick="fecharModal()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Adicionar Item ao Pedido</h3>
            <p id="modal-produto-nome" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium mb-4"></p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade</label>
                    <input id="modal-quantidade-input" type="number" step="1" value="1" min="0.001"
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor Unitário Estimado (R$)</label>
                    <input id="modal-preco-input" type="number" step="0.01" value="0.00"
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <button onclick="confirmarAdicao()" 
                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                    Confirmar e Adicionar
                </button>
            </div>
        </div>
    </div>

    @if (isset($fornecedorStatus) && $fornecedorStatus)
        @if (strtoupper(trim($fornecedorStatus['descricao_situacao_cadastral'] ?? '')) !== 'ATIVA')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert('⚠️ Atenção: O fornecedor selecionado não está com a situação ATIVA na Receita Federal.');
                });
            </script>
        @endif
    @endif

    @push('scripts')
    <script>
        let produtosNoPedido = [];
        let produtoSendoAdicionado = null;

        // Bridge com o Livewire
        function selecionarProdutoComQuantidade(id, nome, preco, fornecedor, cor, partNumber, liberarDesconto, estoque) {
            produtoSendoAdicionado = { id, nome, preco, cor };
            document.getElementById('modal-produto-nome').textContent = nome + (cor ? ' ('+cor+')' : '');
            document.getElementById('modal-preco-input').value = preco;
            document.getElementById('modal-quantidade-input').value = 1;
            document.getElementById('modal-quantidade').classList.remove('hidden');
            setTimeout(() => document.getElementById('modal-quantidade-input').focus(), 100);
        }

        function fecharModal() {
            document.getElementById('modal-quantidade').classList.add('hidden');
            produtoSendoAdicionado = null;
        }

        function confirmarAdicao() {
            if (!produtoSendoAdicionado) return;

            const qtd = parseFloat(document.getElementById('modal-quantidade-input').value) || 0;
            const preco = parseFloat(document.getElementById('modal-preco-input').value) || 0;

            if (qtd <= 0) {
                alert('A quantidade deve ser maior que zero.');
                return;
            }

            // Verifica se já existe
            const existe = produtosNoPedido.findIndex(p => p.id === produtoSendoAdicionado.id);
            if (existe !== -1) {
                produtosNoPedido[existe].quantidade += qtd;
                produtosNoPedido[existe].valor_unitario = preco; // Atualiza o preço se for diferente?
            } else {
                produtosNoPedido.push({
                    id: produtoSendoAdicionado.id,
                    nome: produtoSendoAdicionado.nome,
                    cor: produtoSendoAdicionado.cor,
                    quantidade: qtd,
                    valor_unitario: preco,
                    observacao: ''
                });
            }

            renderTable();
            fecharModal();
        }

        function removerItem(index) {
            produtosNoPedido.splice(index, 1);
            renderTable();
        }

        function atualizarValor(index, campo, valor) {
            produtosNoPedido[index][campo] = valor;
            renderTable(false); // Renderiza sem resetar inputs se possível, ou apenas recalcula totais
        }

        function renderTable(fullRender = true) {
            const tbody = document.getElementById('produtos-selecionados');
            let totalPedido = 0;

            if (fullRender) {
                tbody.innerHTML = '';
            }

            produtosNoPedido.forEach((p, i) => {
                const totalItem = p.quantidade * p.valor_unitario;
                totalPedido += totalItem;

                if (fullRender) {
                    const row = `
                        <tr class="dark:border-neutral-700">
                            <td class="px-4 py-2 text-sm text-gray-500">
                                ${p.id}
                                <input type="hidden" name="itens[${i}][produto_id]" value="${p.id}">
                            </td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">${p.nome}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">${p.cor || '-'}</td>
                            <td class="px-4 py-2">
                                <input type="number" step="1" name="itens[${i}][quantidade]" value="${p.quantidade}" 
                                    onchange="updateRow(${i}, 'quantidade', this.value)"
                                    class="w-full text-center border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" name="itens[${i}][valor_unitario]" value="${p.valor_unitario.toFixed(2)}" 
                                    onchange="updateRow(${i}, 'valor_unitario', this.value)"
                                    class="w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                            </td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                R$ ${totalItem.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                <input type="hidden" name="itens[${i}][valor_total]" value="${totalItem.toFixed(2)}">
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="itens[${i}][observacao]" value="${p.observacao}" 
                                    onchange="updateRow(${i}, 'observacao', this.value)" placeholder="..."
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
                }
            });

            document.getElementById('total-geral').textContent = 'R$ ' + totalPedido.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        }

        function updateRow(index, field, value) {
            if (field === 'quantidade' || field === 'valor_unitario') {
                produtosNoPedido[index][field] = parseFloat(value) || 0;
            } else {
                produtosNoPedido[index][field] = value;
            }
            renderTable();
        }
        
        // Render inicial - Compatível com wire:navigate
        document.addEventListener('livewire:navigated', () => {
            renderTable();
            initSupplierCheck();
        });
        
        // Fallback para primeiro carregamento
        document.addEventListener('DOMContentLoaded', () => {
            renderTable();
            initSupplierCheck();
        });

        function initSupplierCheck() {
            const select = document.getElementById('fornecedor_id_select');
            if (!select) return;

            select.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const cnpj = option.dataset.cnpj;

                if (!cnpj) return;

                fetch(`/api/cnpj/${cnpj}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.descricao_situacao_cadastral && data.descricao_situacao_cadastral.toUpperCase() !== 'ATIVA') {
                            alert('⚠️ Atenção: A situação do CNPJ deste fornecedor não está ATIVA na Receita Federal (' + data.descricao_situacao_cadastral + ').');
                        }
                    })
                    .catch(error => console.error('Erro ao verificar CNPJ:', error));
            });
        }

        function puxarEstoqueMinimo() {
            const fornecedorId = document.getElementById('fornecedor_id_select').value;
            let url = '{{ route("pedido_compras.estoque_minimo") }}';
            if (fornecedorId) url += '?fornecedor_id=' + fornecedorId;

            fetch(url)
                .then(r => r.json())
                .then(produtos => {
                    if (produtos.length === 0) {
                        alert('Nenhum produto abaixo do estoque mínimo encontrado.');
                        return;
                    }
                    produtos.forEach(p => {
                        const qtdNecessaria = Math.max(0, p.estoque_minimo - p.estoque_atual);
                        adicionarOuAtualizar(p.id, p.nome, p.cor?.nome, qtdNecessaria, p.preco_custo || 0);
                    });
                    renderTable();
                    alert(produtos.length + ' itens de estoque crítico adicionados.');
                });
        }

        function puxarFaltas() {
            fetch('{{ route("faltas.pendentes") }}')
                .then(r => r.json())
                .then(faltas => {
                    if (faltas.length === 0) {
                        alert('Nenhuma falta registrada no sistema.');
                        return;
                    }
                    let count = 0;
                    faltas.forEach(f => {
                        f.itens.forEach(item => {
                            if (item.produto_id) {
                                adicionarOuAtualizar(item.produto_id, item.produto.nome, item.produto.cor?.nome, item.quantidade, item.valor_unitario);
                                count++;
                            }
                        });
                    });
                    renderTable();
                    alert(count + ' itens de faltas pendentes adicionados.');
                });
        }

        function adicionarOuAtualizar(id, nome, cor, qtd, preco) {
            const index = produtosNoPedido.findIndex(p => p.id === id);
            if (index !== -1) {
                produtosNoPedido[index].quantidade += qtd;
            } else {
                produtosNoPedido.push({
                    id: id,
                    nome: nome,
                    cor: cor,
                    quantidade: qtd,
                    valor_unitario: preco,
                    observacao: ''
                });
            }
        }
    </script>
    @endpush
</x-layouts.app>
