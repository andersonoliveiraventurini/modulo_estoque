<x-layouts.app :title="__('Editar Movimentação')">
    <!-- Injeção de CSS do Select2 -->
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 42px;
            border-color: #d1d5db;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
        }
        .dark .select2-container .select2-selection--single {
            background-color: #171717;
            border-color: #404040;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #d4d4d8;
        }
        .dark .select2-container--default .select2-results__option--selected {
            background-color: #262626; 
        }
        .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #4f46e5;
            color: white;
        }
        .dark .select2-dropdown {
            background-color: #171717;
            border-color: #404040;
        }
        .dark .select2-search input {
            background-color: #262626;
            color: white;
            border-color: #404040;
        }
    </style>
    @endpush

    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                         <x-heroicon-o-truck class="w-5 h-5" /> 
                        Editar Movimentação #{{ $movimentacao->id }}
                    </h2>
                    <a href="{{ route('movimentacao.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        &larr; Voltar
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded text-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('movimentacao.update', $movimentacao->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-clipboard class="w-5 h-5" /> 
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="tipo_entrada" label="Tipo de movimentação">
                                <option value="">Selecione</option>
                                <option value="entrada" {{ (old('tipo_entrada') ?? $movimentacao->tipo) == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ (old('tipo_entrada') ?? $movimentacao->tipo) == 'saida' ? 'selected' : '' }}>Saída</option>
                            </x-select>
                            <x-input name="data_movimentacao" label="Data da Movimentação" type="date" value="{{ old('data_movimentacao', optional($movimentacao->data_movimentacao)->format('Y-m-d') ?? $movimentacao->created_at->format('Y-m-d')) }}" />
                            <x-input name="nota_fiscal_fornecedor" label="Nota Fiscal Fornecedor" placeholder="(opcional)" value="{{ old('nota_fiscal_fornecedor') ?? $movimentacao->nota_fiscal_fornecedor }}" />
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Arquivo NF (PDF/Imagem)</label>
                                @if($movimentacao->arquivo_nota_fiscal)
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">
                                        Arquivo atual: <a href="{{ Storage::url($movimentacao->arquivo_nota_fiscal) }}" target="_blank" class="underline">Ver NF atual</a>
                                    </div>
                                @endif
                                <input type="file" name="arquivo_nota_fiscal" accept=".pdf,.jpg,.jpeg,.png"
                                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-neutral-700 rounded-md shadow-sm bg-white dark:bg-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300" />
                            </div>
                            <x-input name="romaneiro" label="Romaneiro" placeholder="(opcional)" value="{{ old('romaneiro') ?? $movimentacao->romaneiro }}" />
                            
                            @if($movimentacao->pedido_compra_id)
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Pedido de Compra Vinculado</label>
                                <div class="mt-1 p-2 bg-gray-100 dark:bg-neutral-800 rounded border border-gray-200 dark:border-neutral-700 text-sm font-medium">
                                    #{{ $movimentacao->pedido_compra_id }} – {{ optional($movimentacao->pedidoCompra->fornecedor)->nome_fantasia }}
                                </div>
                                <input type="hidden" name="pedido_compra_id" value="{{ $movimentacao->pedido_compra_id }}">
                            </div>
                            @endif

                            <div class="md:col-span-3">
                                <x-input name="observacao" label="Observação Geral" placeholder="..." value="{{ old('observacao') ?? $movimentacao->observacao }}" />
                            </div>
                        </div>
                    </div>

                    {{-- === Pesquisa de Produtos (Livewire) === --}}
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700" id="secao-busca-produtos">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-blue-600" />
                            Buscar Produtos Extras
                        </h3>
                        <livewire:lista-produto-orcamento :showStock="false" :showDiscount="false" />
                    </div>

                    <!-- Tabela de Itens Selecionados -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-archive-box class="w-5 h-5 text-indigo-600" />                             
                            Itens da Movimentação
                        </h3>

                        <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produto</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-20">Qtd.</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-32">V. Unit.</th>
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
                         <x-button type="submit">Salvar Alterações</x-button>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade *</label>
                    <input id="modal-quantidade-input" type="number" step="0.001" value="1"
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor Unitário (R$)</label>
                    <input id="modal-preco-input" type="number" step="0.01" value="0.00" readonly
                        class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-500 rounded-md px-3 py-2 cursor-not-allowed" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Armazém</label>
                    <select id="modal-armazem-select" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2">
                        <option value="">Selecione</option>
                        @foreach($armazens as $az)
                            <option value="{{ $az->nome }}">{{ $az->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Corredor</label>
                        <input id="modal-corredor-input" type="text" placeholder="Ex: A" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posição</label>
                        <input id="modal-posicao-input" type="text" placeholder="Ex: 01" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observação do Item</label>
                    <input id="modal-obs-input" type="text" placeholder="Ex: pacote aberto, cor alterada..." class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2" />
                </div>
            </div>

            <button onclick="confirmarAdicao()" 
                class="w-full mt-6 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                Adicionar Item
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        @php
            // Se houver dados de falha de validação (old), use-os. Senão, use os itens da movimentação.
            $rawItems = old('produtos');
            if ($rawItems) {
                $initialItems = $rawItems;
            } else {
                $initialItems = $movimentacao->itens->map(function($i) {
                    return [
                        'produto_id' => $i->produto_id,
                        'nome' => $i->produto->nome,
                        'cor' => optional($i->produto->cor)->nome,
                        'fornecedor' => optional($i->fornecedor)->name_fantasia ?? optional($i->fornecedor)->nome_fantasia,
                        'fornecedor_id' => $i->fornecedor_id,
                        'quantidade' => $i->quantidade,
                        'valor_unitario' => $i->valor_unitario,
                        'armazem' => $i->endereco,
                        'corredor' => $i->corredor,
                        'posicao' => $i->posicao,
                        'observacao' => $i->observacao,
                    ];
                })->toArray();
            }
        @endphp

        let produtosSelecionados = @json($initialItems);
        let produtoSendoAdicionado = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (produtosSelecionados.length > 0) {
                produtosSelecionados.forEach(p => {
                    p.quantidade = parseFloat(p.quantidade) || 0;
                    // Tratar divergência de nomes de campos entre Request e Model
                    p.valor_unitario = parseFloat(p.valor) || parseFloat(p.valor_unitario) || 0;
                    p.nome = p.nome || '';
                    p.cor = p.cor || '';
                    p.fornecedor = p.fornecedor || p.fornecedor_nome || '';
                    p.armazem = p.armazem || 'HUB';
                });
                renderTable();
            }
        });

        // Bridge com o Livewire lista-produto-orcamento
        function selecionarProdutoComQuantidade(id, nome, preco, fornecedor, cor, partNumber, liberarDesconto, estoque) {
            produtoSendoAdicionado = { 
                id: id, 
                nome: nome, 
                preco: preco, 
                fornecedor: fornecedor, 
                cor: cor,
                maxQtd: null 
            };

            document.getElementById('modal-produto-nome').textContent = nome + (cor ? ' ('+cor+')' : '');
            document.getElementById('modal-preco-input').value = preco || 0;
            document.getElementById('modal-quantidade-input').value = 1;
            document.getElementById('modal-armazem-select').value = 'HUB';
            document.getElementById('modal-corredor-input').value = '';
            document.getElementById('modal-posicao-input').value = '';
            document.getElementById('modal-obs-input').value = '';
            
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

            const item = {
                produto_id: produtoSendoAdicionado.id,
                nome: produtoSendoAdicionado.nome,
                cor: produtoSendoAdicionado.cor,
                fornecedor: produtoSendoAdicionado.fornecedor,
                quantidade: qtd,
                valor_unitario: parseFloat(document.getElementById('modal-preco-input').value) || 0,
                armazem: document.getElementById('modal-armazem-select').value || 'HUB',
                corredor: document.getElementById('modal-corredor-input').value,
                posicao: document.getElementById('modal-posicao-input').value,
                observacao: document.getElementById('modal-obs-input').value,
                maxQtd: produtoSendoAdicionado.maxQtd,
                locked: produtoSendoAdicionado.locked || false 
            };

            produtosSelecionados.push(item);
            renderTable();
            fecharModal();
        }

        function removerItem(index) {
            produtosSelecionados.splice(index, 1);
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('produtos-selecionados');
            if(!tbody) return;
            tbody.innerHTML = '';

            produtosSelecionados.forEach((p, i) => {
                const totalItem = p.quantidade * p.valor_unitario;
                let alertClass = "";
                if (p.maxQtd && p.quantidade > p.maxQtd) {
                    alertClass = "bg-red-50 text-red-700 border-red-200";
                }

                const row = `
                    <tr class="${alertClass} dark:border-neutral-700">
                        <td class="px-4 py-2">
                            <span class="font-medium text-sm block">${p.nome}</span>
                            <small class="text-xs text-gray-400">${p.cor || 'Sem cor'} / ${p.fornecedor || 'S/F'}</small>
                            <input type="hidden" name="produtos[${i}][produto_id]" value="${p.produto_id}">
                            <input type="hidden" name="produtos[${i}][valor_total]" value="${totalItem.toFixed(2)}">
                            <input type="hidden" name="produtos[${i}][fornecedor_id]" value="${p.fornecedor_id || ''}">
                            <input type="hidden" name="produtos[${i}][nome]" value="${p.nome || ''}">
                            <input type="hidden" name="produtos[${i}][cor]" value="${p.cor || ''}">
                            <input type="hidden" name="produtos[${i}][fornecedor_nome]" value="${p.fornecedor || ''}">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" step="0.001" name="produtos[${i}][quantidade]" value="${p.quantidade}" 
                                onchange="updateItem(${i}, 'quantidade', this.value)"
                                class="w-20 text-center border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 rounded text-sm p-1">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" name="produtos[${i}][valor]" value="${p.valor_unitario.toFixed(2)}" 
                                readonly
                                class="w-full border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-500 rounded text-sm p-1 cursor-not-allowed">
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">${p.armazem || 'HUB'}</span>
                                <span class="text-xs text-gray-400">${p.corredor || '-'}/${p.posicao || '-'}</span>
                                <input type="hidden" name="produtos[${i}][armazem]" value="${p.armazem || 'HUB'}">
                                <input type="hidden" name="produtos[${i}][corredor]" value="${p.corredor || ''}">
                                <input type="hidden" name="produtos[${i}][posicao]" value="${p.posicao || ''}">
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
            } else {
                produtosSelecionados[index][field] = value;
            }
            renderTable();
        }
    </script>
    @endpush
</x-layouts.app>
