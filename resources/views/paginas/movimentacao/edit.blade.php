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
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5" /> 
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="tipo_entrada" label="Tipo de movimentação">
                                <option value="entrada" {{ (old('tipo_entrada') ?? $movimentacao->tipo) == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ (old('tipo_entrada') ?? $movimentacao->tipo) == 'saida' ? 'selected' : '' }}>Saída</option>
                            </x-select>
                            <x-select name="pedido_id" label="Pedido">
                                <option value="">Selecione</option>
                                @foreach ($pedidos as $pedido)
                                    <option value="{{ $pedido->id }}" {{ (old('pedido_id') ?? $movimentacao->pedido_id) == $pedido->id ? 'selected' : '' }}>{{ $pedido->id }}</option>
                                @endforeach
                            </x-select>
                            <x-input name="nota_fiscal_fornecedor" label="Nota Fiscal Fornecedor" placeholder="(opcional)" value="{{ old('nota_fiscal_fornecedor') ?? $movimentacao->nota_fiscal_fornecedor }}" />
                            <x-input name="romaneiro" label="Romaneiro" placeholder="(opcional)" value="{{ old('romaneiro') ?? $movimentacao->romaneiro }}" />
                            <x-input name="observacao" label="Observação" placeholder="..." value="{{ old('observacao') ?? $movimentacao->observacao }}" />
                        </div>
                    </div>

                    <!-- Produtos -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-archive-box class="w-5 h-5" />                             
                            Produtos (Itens vinculados)
                        </h3>
                        <p class="text-sm text-neutral-500">Ao salvar, o estoque atual será refeito substituindo os itens pelas informações definidas abaixo.</p>

                        <div id="produtos-wrapper" class="space-y-4">
                            @php
                                $itensAnteriores = old('produtos') ? old('produtos') : $movimentacao->itens->toArray();
                            @endphp
                            
                            @foreach($itensAnteriores as $index => $item)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative produto-item mt-4">
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Fornecedor *</label>
                                        <select name="produtos[{{ $index }}][fornecedor_id]" onchange="filtrarProdutos(this)" class="fornecedor-select border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                            <option value="">Selecione um Fornecedor</option>
                                            @foreach($fornecedores as $fornecedor)
                                                <option value="{{ $fornecedor->id }}" {{ ($item['fornecedor_id'] ?? '') == $fornecedor->id ? 'selected' : '' }}>{{ $fornecedor->nome_fantasia }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Produto *</label>
                                        <select name="produtos[{{ $index }}][produto_id]" class="produto-select border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                            <option value="">Selecione um Produto</option>
                                            @foreach($produtos as $produto)
                                                <option value="{{ $produto->id }}" data-fornecedor="{{ $produto->fornecedor_id }}" {{ ($item['produto_id'] ?? '') == $produto->id ? 'selected' : '' }}>
                                                    {{ $produto->nome }} ({{ $produto->sku }}) @if(optional($produto->cor)->nome) - Cor: {{ $produto->cor->nome }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Quantidade *</label>
                                        <input name="produtos[{{ $index }}][quantidade]" value="{{ $item['quantidade'] ?? '' }}" type="number" step="0.01" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Unitário</label>
                                        <input name="produtos[{{ $index }}][valor]" value="{{ $item['valor_unitario'] ?? ($item['valor'] ?? '') }}" type="number" step="0.01" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Total</label>
                                        <input name="produtos[{{ $index }}][valor_total]" value="{{ $item['valor_total'] ?? '' }}" type="number" step="0.01" readonly placeholder="Calculado auto." class="bg-gray-100 dark:bg-neutral-800 cursor-not-allowed border-gray-300 dark:border-neutral-700 dark:text-neutral-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Armazém</label>
                                        <select name="produtos[{{ $index }}][armazem]" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                            <option value="">Selecione um Armazém</option>
                                            @foreach($armazens as $armazem)
                                                <option value="{{ $armazem->nome }}" {{ ($item['armazem'] ?? ($item['endereco'] ?? '')) == $armazem->nome ? 'selected' : '' }}>{{ $armazem->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Corredor</label>
                                        <input name="produtos[{{ $index }}][corredor]" value="{{ $item['corredor'] ?? '' }}" type="text" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    
                                    <div class="w-full">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Posição</label>
                                        <input name="produtos[{{ $index }}][posicao]" value="{{ $item['posicao'] ?? '' }}" type="text" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>

                                    <div class="w-full md:col-span-3">
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Observação (Ex: pacote, em caixa, defeito leve)</label>
                                        <input name="produtos[{{ $index }}][observacao]" value="{{ $item['observacao'] ?? '' }}" type="text" placeholder="Opcional..." class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    
                                    @if(count($itensAnteriores) > 1)
                                    <button type="button" onclick="removeProduto(this)" class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold text-red-600 bg-red-100 rounded hover:bg-red-200">
                                        Remover
                                    </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <x-button type="button" onclick="addProduto()">
                            + Adicionar mais um Produto
                        </x-button>
                    </div>

                    <div class="flex gap-4 pt-4">
                         <x-button type="submit">Salvar Alterações</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    
    <!-- Template oculto do Blade para clonar com JS -->
    <template id="produto-template">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative mt-4 produto-item">
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Fornecedor *</label>
                <select name="produtos[__INDEX__][fornecedor_id]" onchange="filtrarProdutos(this)" class="fornecedor-select border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">Selecione um Fornecedor</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Produto *</label>
                <select name="produtos[__INDEX__][produto_id]" class="produto-select border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">Selecione um Produto</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}" data-fornecedor="{{ $produto->fornecedor_id }}">
                            {{ $produto->nome }} ({{ $produto->sku }}) @if(optional($produto->cor)->nome) - Cor: {{ $produto->cor->nome }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Quantidade *</label>
                <input name="produtos[__INDEX__][quantidade]" type="number" step="0.01" placeholder="Ex. 10" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Unitário</label>
                <input name="produtos[__INDEX__][valor]" type="number" step="0.01" placeholder="Ex. 15.50" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Total</label>
                <input name="produtos[__INDEX__][valor_total]" type="number" step="0.01" placeholder="Calculado auto." readonly class="bg-gray-100 dark:bg-neutral-800 cursor-not-allowed border-gray-300 dark:border-neutral-700 dark:text-neutral-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Armazém</label>
                <select name="produtos[__INDEX__][armazem]" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">Selecione um Armazém</option>
                    @foreach($armazens as $armazem)
                        <option value="{{ $armazem->nome }}">{{ $armazem->nome }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Corredor</label>
                <input name="produtos[__INDEX__][corredor]" type="text" placeholder="Corredor" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>
            
            <div class="w-full">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Posição</label>
                <input name="produtos[__INDEX__][posicao]" type="text" placeholder="Posição" class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>
            
            <div class="w-full md:col-span-3">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Observação (Ex: pacote, em caixa, defeito leve)</label>
                <input name="produtos[__INDEX__][observacao]" type="text" placeholder="Opcional..." class="border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
            </div>

            <button type="button" onclick="removeProduto(this)" class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold text-red-600 bg-red-100 rounded hover:bg-red-200">
                Remover
            </button>
        </div>
    </template>

    <script>
        let produtoIndex = {{ max(1, count($itensAnteriores)) }};

        function addProduto() {
            const wrapper = document.getElementById('produtos-wrapper');
            const template = document.getElementById('produto-template').innerHTML;
            
            const novoElemento = template.replace(/__INDEX__/g, produtoIndex);
            wrapper.insertAdjacentHTML('beforeend', novoElemento);
            produtoIndex++;
            const recemCriado = wrapper.lastElementChild;
            const selectFornecedor = recemCriado.querySelector('.fornecedor-select'); 
            filtrarProdutos(selectFornecedor);
        }

        function removeProduto(button) {
            button.closest('.produto-item').remove();
        }

        function filtrarProdutos(fornecedorSelect) {
            if(!fornecedorSelect) return;
            const container = fornecedorSelect.closest('.produto-item');
            const produtoSelect = container.querySelector('.produto-select');
            const fornecedorId = fornecedorSelect.value;
            
            Array.from(produtoSelect.options).forEach(option => {
                if(option.value === "") {
                    option.style.display = 'block'; 
                } else {
                    const dataFornecedor = option.getAttribute('data-fornecedor');
                    if(fornecedorId === "" || dataFornecedor === fornecedorId) {
                        option.hidden = false;
                        option.disabled = false;
                    } else {
                        option.hidden = true;
                        option.disabled = true;
                    }
                }
            });

            const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
            if(selectedOption && selectedOption.hidden === true) {
                produtoSelect.value = "";
            }
        }

        // Executar filtro inicial nos selects que já estão na tela quando carregar a pag
        document.addEventListener('DOMContentLoaded', () => {
            const boxes = document.querySelectorAll('.fornecedor-select');
            boxes.forEach(item => filtrarProdutos(item));
        });

        // Automatizar o cálculo do Valor Total baseado na Quantidade e Valor
        document.addEventListener('input', function(e) {
            if(e.target.name && (e.target.name.includes('[quantidade]') || e.target.name.includes('[valor]'))) {
                const container = e.target.closest('.produto-item');
                if(container) {
                    const qtdInput = container.querySelector('input[name*="[quantidade]"]');
                    const valorInput = container.querySelector('input[name*="[valor]"]:not([name*="[valor_total]"])');
                    const totalInput = container.querySelector('input[name*="[valor_total]"]');
                    
                    if(qtdInput && valorInput && totalInput) {
                        const q = parseFloat(qtdInput.value);
                        const v = parseFloat(valorInput.value);
                        if(!isNaN(q) && !isNaN(v)) {
                            totalInput.value = (q * v).toFixed(2);
                        } else {
                            totalInput.value = '';
                        }
                    }
                }
            }
        });
    </script>
    
    @push('scripts')
    <!-- jQuery Necessário ao Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- JS Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Função para instanciar Select2 pesquisáveis nos combos de "Produto"
        function initSelect2() {
            $('.produto-select:not(.select2-hidden-accessible)').select2({
                placeholder: "Digite para pesquisar o Produto (Nome, SKU ou Cor)",
                language: "pt-BR",
                width: '100%',
                matcher: function(params, data) {
                    // Ignora options vazios
                    if ($.trim(params.term) === '') return data;
                    if (typeof data.text === 'undefined') return null;
                    // Ignora filtragem em options escondidos/desativados pelo JS pai
                    if (data.element && (data.element.hidden || data.element.disabled)) return null;

                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();
                    if (text.indexOf(term) > -1) {
                        return data;
                    }
                    return null;
                }
            });
        }
        
        $(document).ready(function() {
            initSelect2();
        });

        // Adicionar chamadas de initSelect2 no addProduto pra pegar o novo combo
        const _addProdutoOrig = addProduto;
        addProduto = function() {
            _addProdutoOrig();
            initSelect2();
        }

        // Corrigir filtragem: como o Select2 altera a view, precisamos avisar ele pra se redesenhar
        const _filtrarProdutosOrig = filtrarProdutos;
        filtrarProdutos = function(fornecedorSelect) {
            _filtrarProdutosOrig(fornecedorSelect);
            const container = fornecedorSelect.closest('.produto-item');
            if(container) {
                const pSelect = container.querySelector('.produto-select');
                if($(pSelect).hasClass('select2-hidden-accessible')) {
                    // Dispara evento pro Select2 redesenhar os resultados
                    $(pSelect).select2('destroy');
                    initSelect2();
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
