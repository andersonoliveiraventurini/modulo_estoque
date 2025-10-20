<x-layouts.app :title="__('Editar Orçamento')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Editar Orçamento para Cliente {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
                </h2>
                @if ($cliente->vendedor_interno != null)
                    <p> Vendedor interno: {{ $cliente->vendedor_interno ?? 'Não atribuído' }} </p>
                @endif
                @if ($cliente->vendedor_externo != null)
                    <p> Vendedor externo: {{ $cliente->vendedor_externo ?? 'Não atribuído' }} </p>
                @endif
                @if ($cliente->desconto_aprovado != null)
                    <p> Desconto aprovado: {{ $cliente->desconto_aprovado ?? 'Não atribuído' }} </p>
                @endif
                <input type="hidden" name="desconto_aprovado" id="desconto_aprovado"
                    value="{{ $cliente->desconto_aprovado ?? 0 }}" />

                <!-- Pesquisa de Produtos -->
                <div class="space-y-4">
                    <hr />
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Buscar Produtos
                    </h3>
                    <livewire:lista-produto-orcamento />
                </div>
                <!-- Campos iniciais -->
                <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-8"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <!-- Token CSRF seria aqui -->

                    <!-- Produtos no Orçamento -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 7h9m0 0V10"></path>
                            </svg>
                            Produtos no Orçamento
                        </h3>
                        <div
                            class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Código</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Produto</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Part Number</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Fornecedor</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Cor</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Preço Unit.</th>
                                        <th scope="col"
                                            class="w-20 px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Qtd.</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Subtotal</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            c/ Desconto</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="produtos-originais" class="divide-y">
                                    @foreach ($orcamento->itens as $item)
                                        <tr>
                                            <input type="hidden" name="produtos[{{ $loop->index }}][produto_id]"
                                                value="{{ $item->produto->id }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][valor_unitario]"
                                                class="valor-unitario-hidden" value="{{ $item->valor_unitario }}">

                                            <!-- 🔹 ADICIONAR ESTE CAMPO -->
                                            <input type="hidden" name="produtos[{{ $loop->index }}][part_number]"
                                                value="{{ $item->produto->part_number ?? '' }}">

                                            <input type="hidden" name="produtos[{{ $loop->index }}][quantidade]"
                                                value="{{ $item->quantidade }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][subtotal]"
                                                value="{{ number_format($item->valor_unitario * $item->quantidade, 2, '.', '') }}">
                                            <input type="hidden"
                                                name="produtos[{{ $loop->index }}][subtotal_com_desconto]"
                                                value="{{ number_format($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0), 2, '.', '') }}">
                                            <input type="hidden"
                                                name="produtos[{{ $loop->index }}][preco_unitario_com_desconto]"
                                                value="{{ number_format(($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0)) / $item->quantidade, 2, '.', '') }}">

                                            <td class="px-3 py-2 border">{{ $item->produto->id }}</td>
                                            <td class="px-3 py-2 border">{{ $item->produto->nome }}</td>
                                            <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '' }}</td>
                                            <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome ?? '' }}
                                            </td>
                                            <td class="px-3 py-2 border">{{ $item->produto->cor ?? '' }}</td>
                                            <td class="px-3 py-2 border">R$
                                                {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                            <td class="px-3 py-2 border">
                                                <input type="number" name="produtos[{{ $loop->index }}][quantidade]"
                                                    value="{{ $item->quantidade }}" min="1"
                                                    onchange="alterarQuantidadeOriginal({{ $loop->index }}, this.value)"
                                                    class="w-12 border rounded px-2 py-1 text-center"
                                                    style="max-width: 4rem;" />
                                            </td>
                                            <td class="px-3 py-2 border">R$
                                                {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 border text-green-600">R$
                                                {{ number_format($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0), 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 border text-center">
                                                <button type="button"
                                                    onclick="removerProdutoOriginal({{ $loop->index }})"
                                                    class="text-red-600 hover:text-red-800">🗑</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tbody id="produtos-selecionados" class="divide-y">

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção de Vidros Corrigida -->
                    <div class="space-y-4">
                        <br />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                </path>
                            </svg>
                            Vidros ou Esteiras

                            <button type="button" onclick="addVidro()"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                + Adicionar Vidro/Esteira
                            </button>
                        </h3>
                        <!-- Wrapper dos vidros (só aparece quando aberto) -->
                        <div x-transition id="vidros-wrapper" class="space-y-4">
                        </div>
                    </div>

                    <!-- Endereço de entrega -->
                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Nome da Obra e Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input type="text" name="obra" placeholder="Digite o nome da obra"
                                label="Nome da Obra" required :value="$orcamento->obra" />
                            <x-input type="text" name="prazo_entrega" placeholder="Ex: 15 dias úteis"
                                label="Prazo de Entrega" :value="$orcamento->prazo_entrega" />
                            <x-select name="vendedor_id" label="Atendido por" required>
                                <option value="">Selecione...</option>
                                @foreach ($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" @selected($orcamento->vendedor_id == $vendedor->id)>
                                        {{ $vendedor->name }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="frete" label="Tipo de Frete">
                                <option value="">Selecione...</option>
                                <option value="cif" @selected($orcamento->frete == 'cif')>CIF</option>
                                <option value="fob" @selected($orcamento->frete == 'fob')>FOB</option>
                            </x-select>
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" :value="$orcamento->endereco->cep ?? ''" />
                        </div>
                        <div id="endereco-entrega-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" :value="$orcamento->endereco->cidade ?? ''" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" :value="$orcamento->endereco->estado ?? ''" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" :value="$orcamento->endereco->bairro ?? ''" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly" :value="$orcamento->endereco->logradouro ?? ''" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    :value="$orcamento->endereco->numero ?? ''" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc." :value="$orcamento->endereco->complemento ?? ''" />
                            </div>
                        </div>
                    </div>

                    <!-- Opções de Transporte -->
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2a4 4 0 014-4h4l3 3-3 3h-8zM3 7h13a2 2 0 012 2v2"></path>
                            </svg>
                            Opções de Transporte
                        </h3>

                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach ($opcoesTransporte as $opcao)
                                <label
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 hover:bg-blue-50 cursor-pointer transition">
                                    <input type="checkbox" name="tipos_transporte[]" value="{{ $opcao->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @if ($orcamento->transportes->contains($opcao->id)) checked @endif />
                                    <span class="text-sm text-gray-700">{{ $opcao->nome }}</span>
                                </label>
                            @endforeach
                        </div>

                    </div>

                    <hr />
                    <!-- Valores e descontos -->
                    <div class="overflow-x-auto">
                        <div class="flex gap-4 min-w-max">

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto na vendedor %</label>
                                <input type="text" name="desconto" value="0" min="0" max="100"
                                    placeholder="Digite a porcentagem de desconto (0 a 100)"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto específico R$</label>
                                <input type="text" name="desconto_especifico" value="0.00"
                                    placeholder="Digite o valor do desconto específico"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Guia Recolhimento</label>
                                <input type="text" step="0.01" name="guia_recolhimento" value="0"
                                    value="0.00" oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Valor Total dos Itens s/
                                    desconto (R$)</label>
                                <input type="text" id="valor_total" name="valor_total" readonly value="0,00"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Valor Final c/ desconto
                                    (R$)</label>
                                <input type="text" id="valor_final"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 font-semibold text-green-700"
                                    value="0.00" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Observações Gerais
                        </h3>
                        <x-textarea name="observacoes" placeholder="Digite as observações" label="Observações"
                            rows="4"> {{ old('observacoes', $orcamento->observacoes) }} </x-textarea>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            Salvar Orçamento
                        </button>
                        <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                            Limpar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Quantidade Produto -->
    <div id="modal-quantidade"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-80 shadow-lg relative">
            <button onclick="fecharModal()"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Quantidade do Produto</h3>
            <p id="produto-nome" class="mb-2 font-medium"></p>
            <input id="quantidade-produto" type="number" min="1" value="1"
                class="w-full border rounded px-3 py-2 mb-4" />
            <button onclick="confirmarQuantidade()"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                Adicionar
            </button>
        </div>
    </div>

</x-layouts.app>
<script src="{{ asset('js/valida.js') }}"></script>

<script>
console.log('Script carregando...');

// Variáveis globais
window.vidroIndex = 1;
window.itemIndex = 1;
window.produtos = [];
window.produtoSelecionado = null;

console.log('Variáveis globais criadas');

// ========== MODAL ==========
window.selecionarProdutoComQuantidade = function(id, nome, preco, fornecedor, cor, part_number) {
    console.log('selecionarProdutoComQuantidade chamada com:', id, nome);
    
    window.produtoSelecionado = {
        id: id,
        nome: nome,
        preco: parseFloat(preco),
        quantidade: 1
    };
    
    var produtoNomeEl = document.getElementById('produto-nome');
    var quantidadeEl = document.getElementById('quantidade-produto');
    var modalEl = document.getElementById('modal-quantidade');
    
    console.log('Elementos encontrados:', {
        produtoNome: !!produtoNomeEl,
        quantidade: !!quantidadeEl,
        modal: !!modalEl
    });
    
    if (produtoNomeEl) produtoNomeEl.textContent = nome;
    if (quantidadeEl) quantidadeEl.value = 1;
    if (modalEl) {
        modalEl.classList.remove('hidden');
        console.log('Modal aberto');
    } else {
        console.error('Modal não encontrado!');
    }
};

window.fecharModal = function() {
    console.log('fecharModal chamada');
    var modalEl = document.getElementById('modal-quantidade');
    if (modalEl) modalEl.classList.add('hidden');
    window.produtoSelecionado = null;
};

window.confirmarQuantidade = function() {
    console.log('confirmarQuantidade chamada');
    
    if (!window.produtoSelecionado) {
        console.error('Nenhum produto selecionado');
        alert('Erro: Nenhum produto selecionado');
        return;
    }
    
    var quantidadeInput = document.getElementById('quantidade-produto');
    if (!quantidadeInput) {
        console.error('Campo quantidade não encontrado');
        alert('Erro: Campo de quantidade não encontrado');
        return;
    }
    
    console.log('Quantidade:', quantidadeValor);
    
    window.adicionarProduto(
        window.produtoSelecionado.id,
        window.produtoSelecionado.nome,
        window.produtoSelecionado.preco,
        window.produtoSelecionado.fornecedor,
        window.produtoSelecionado.cor,
        window.produtoSelecionado.part_number,
        quantidadeValor
    );
    
    window.fecharModal();
};

// ========== PRODUTOS ==========
window.adicionarProduto = function(id, nome, preco, fornecedor, cor, part_number, quantidade) {
    console.log('adicionarProduto chamada:', id, nome, quantidade);
    
    
    for (var i = 0; i < window.produtos.length; i++) {
        if (window.produtos[i].id === id) {
            alert("Produto já adicionado!");
            return;
        }
    }

    window.produtos.push({
        id: id,
        nome: nome,
        preco: parseFloat(preco),
        quantidade: parseInt(quantidadeVal),
        fornecedor: fornecedorVal,
        cor: corVal,
        part_number: partNumberVal,
        origem: 'novo'
    });
    
    console.log('Produto adicionado. Total de produtos:', window.produtos.length);
    renderProdutos();
};

window.alterarQuantidade = function(index, valor) {
    console.log('alterarQuantidade:', index, valor);
    if (window.produtos[index]) {
        renderProdutos();
    }
};

window.removerProduto = function(index) {
    console.log('removerProduto:', index);
    window.produtos.splice(index, 1);
    renderProdutos();
};

function renderProdutos() {
    console.log('renderProdutos chamada');
    var wrapper = document.getElementById('produtos-selecionados');
    if (!wrapper) {
        console.error('Elemento produtos-selecionados não encontrado');
        return;
    }
    
    wrapper.innerHTML = '';

    var descontoClienteEl = document.querySelector('[name="desconto_aprovado"]');
    var descontoOrcamentoEl = document.querySelector('[name="desconto"]');
    
    
    descontoOrcamentoValor = Math.min(Math.max(descontoOrcamentoValor, 0), 100);
    var descontoAplicado = Math.max(descontoClienteVal, descontoOrcamentoValor);

    var totalProdutos = 0;
    var totalProdutosComDesconto = 0;

    for (var i = 0; i < window.produtos.length; i++) {
        var p = window.produtos[i];
        var subtotal = p.preco * p.quantidade;
        var subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));

        totalProdutos += subtotal;
        totalProdutosComDesconto += subtotalComDesconto;

        var row = document.createElement('tr');
        var nomeSeguro = String(p.nome).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        
        row.innerHTML = 
            '<td class="px-3 py-2 border"><input type="hidden" name="itens[' + i + '][id]" value="' + p.id + '">' + p.id + '</td>' +
            '<td class="px-3 py-2 border">' + nomeSeguro + '</td>' +
            '<td class="px-3 py-2 border">' + partSeguro + '</td>' +
            '<td class="px-3 py-2 border">' + fornSeguro + '</td>' +
            '<td class="px-3 py-2 border">' + corSegura + '</td>' +
            '<td class="px-3 py-2 border">R$ ' + p.preco.toFixed(2).replace('.', ',') +
            '<input type="hidden" name="itens[' + i + '][preco_unitario]" value="' + p.preco + '"></td>' +
            '<td class="px-3 py-2 border"><input type="number" name="itens[' + i + '][quantidade]" value="' + p.quantidade + '" min="1" onchange="alterarQuantidade(' + i + ', this.value)" class="w-12 border rounded px-2 py-1 text-center" style="max-width:4rem"/></td>' +
            '<td class="px-3 py-2 border">R$ ' + subtotal.toFixed(2).replace('.', ',') +
            '<input type="hidden" name="itens[' + i + '][subtotal]" value="' + subtotal.toFixed(2) + '"></td>' +
            '<td class="px-3 py-2 border text-green-600">R$ ' + subtotalComDesconto.toFixed(2).replace('.', ',') +
            '<input type="hidden" name="itens[' + i + '][subtotal_com_desconto]" value="' + subtotalComDesconto.toFixed(2) + '">' +
            '<input type="hidden" name="itens[' + i + '][preco_unitario_com_desconto]" value="' + (subtotalComDesconto / p.quantidade).toFixed(2) + '"></td>' +
            '<td class="px-3 py-2 border text-center"><button type="button" onclick="removerProduto(' + i + ')" class="text-red-600 hover:text-red-800">🗑</button></td>';
        
        wrapper.appendChild(row);
    }

    var totaisVidros = calcularTotalVidros();
    var totalGeral = totalProdutos + totaisVidros.totalVidros;
    var totalGeralComDesconto = totalProdutosComDesconto + totaisVidros.totalVidrosComDesconto;

    var valorTotalInput = document.getElementById('valor_total');
    if (valorTotalInput) valorTotalInput.value = totalGeral.toFixed(2);

    atualizarValorFinal(totalGeral, totalGeralComDesconto);
}

// ========== VIDROS ==========
window.addVidro = function() {
    var wrapper = document.getElementById('vidros-wrapper');
    if (!wrapper) return;
    
    var vidroDiv = document.createElement('div');
    vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
    vidroDiv.innerHTML = '<button type="button" onclick="removeVidro(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">Remover</button><br/>' +
        '<div class="overflow-x-auto"><div class="flex gap-4 min-w-max">' +
        '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Descrição</label>' +
        '<input type="text" name="vidros[' + window.vidroIndex + '][descricao]" placeholder="Ex: Vidro incolor 8mm" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
        '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Quantidade</label>' +
        '<input type="number" name="vidros[' + window.vidroIndex + '][quantidade]" value="1" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
        '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Preço m²</label>' +
        '<input type="number" step="0.01" name="vidros[' + window.vidroIndex + '][preco_m2]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
        '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Altura (mm)</label>' +
        '<input type="number" name="vidros[' + window.vidroIndex + '][altura]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
        '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Largura (mm)</label>' +
        '<input type="number" name="vidros[' + window.vidroIndex + '][largura]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
        '</div></div>' +
        '<input type="hidden" name="vidros[' + window.vidroIndex + '][area]" class="area-hidden" />' +
        '<input type="hidden" name="vidros[' + window.vidroIndex + '][valor_total]" class="valor-hidden" />' +
        '<input type="hidden" name="vidros[' + window.vidroIndex + '][valor_com_desconto]" class="valor-desconto-hidden" />' +
        '<div class="mt-2 text-sm"><strong>Área:</strong> <span class="area">0.00</span> m² | ' +
        '<strong>Valor:</strong> R$ <span class="valor">0.00</span> | ' +
        '<strong>c/ desc:</strong> R$ <span class="valor-desconto">0.00</span></div>';
    
    wrapper.appendChild(vidroDiv);
    window.vidroIndex++;
};

window.removeVidro = function(button) {
    button.closest('div.space-y-2').remove();
    atualizarValorFinal();
};

window.calcularVidro = function(element) {
    var container = element.closest('div.space-y-2');
    if (!container) return;
    
    var alturaEl = container.querySelector('[name*="[altura]"]');
    var larguraEl = container.querySelector('[name*="[largura]"]');
    var quantidadeEl = container.querySelector('[name*="[quantidade]"]');
    var precoM2El = container.querySelector('[name*="[preco_m2]"]');
    

    var area = (alturaVal / 1000) * (larguraVal / 1000);
    var valor = area * precoM2Val * quantidadeVal;

    var descontoClienteEl = document.querySelector('[name="desconto_aprovado"]');
    var descontoOrcamentoEl = document.querySelector('[name="desconto"]');
    descontoOrcamentoVal = Math.min(Math.max(descontoOrcamentoVal, 0), 100);
    var descontoAplicado = Math.max(descontoClienteVal, descontoOrcamentoVal);
    var valorComDesconto = valor - (valor * (descontoAplicado / 100));

    var areaSpan = container.querySelector('.area');
    var valorSpan = container.querySelector('.valor');
    var valorDescontoSpan = container.querySelector('.valor-desconto');
    
    if (areaSpan) areaSpan.textContent = area.toFixed(2);
    if (valorSpan) valorSpan.textContent = valor.toFixed(2);
    if (valorDescontoSpan) valorDescontoSpan.textContent = valorComDesconto.toFixed(2);

    var areaHidden = container.querySelector('.area-hidden');
    var valorHidden = container.querySelector('.valor-hidden');
    var valorDescontoHidden = container.querySelector('.valor-desconto-hidden');
    
    if (areaHidden) areaHidden.value = area.toFixed(2);
    if (valorHidden) valorHidden.value = valor.toFixed(2);
    if (valorDescontoHidden) valorDescontoHidden.value = valorComDesconto.toFixed(2);

    setTimeout(atualizarValorFinal, 10);
};

function calcularTotalVidros() {
    var totalVidros = 0;
    var totalVidrosComDesconto = 0;
    var wrapper = document.getElementById('vidros-wrapper');
    if (!wrapper) return { totalVidros: 0, totalVidrosComDesconto: 0 };

    var containers = wrapper.querySelectorAll('.space-y-2');
    for (var i = 0; i < containers.length; i++) {
        var valorEl = containers[i].querySelector('.valor-hidden');
        var valorDescEl = containers[i].querySelector('.valor-desconto-hidden');
        if (valorEl && valorDescEl) {
        }
    }
    return { totalVidros: totalVidros, totalVidrosComDesconto: totalVidrosComDesconto };
}

// ========== CÁLCULOS ==========
function atualizarValorFinal(total, totalComDesconto) {
    var totalVal = total;
    var totalComDescontoVal = totalComDesconto;
    
    if (!totalVal) {
        var totalProdutos = 0;
        var totalProdutosComDesconto = 0;
        var descontoClienteEl = document.querySelector('[name="desconto_aprovado"]');
        var descontoOrcamentoEl = document.querySelector('[name="desconto"]');
        descontoOrcamentoVal = Math.min(Math.max(descontoOrcamentoVal, 0), 100);
        var descontoAplicado = Math.max(descontoClienteVal, descontoOrcamentoVal);

        for (var i = 0; i < window.produtos.length; i++) {
            var subtotal = window.produtos[i].preco * window.produtos[i].quantidade;
            var subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));
            totalProdutos += subtotal;
            totalProdutosComDesconto += subtotalComDesconto;
        }

        var totaisVidros = calcularTotalVidros();
        totalVal = totalProdutos + totaisVidros.totalVidros;
        totalComDescontoVal = totalProdutosComDesconto + totaisVidros.totalVidrosComDesconto;

        var valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) valorTotalInput.value = totalVal.toFixed(2);
    }

    var guiaEl = document.querySelector('[name="guia_recolhimento"]');
    var descontoEspecificoInput = document.querySelector('[name="desconto_especifico"]');
    if (!descontoEspecificoInput) return;

    var valorFinal = totalComDescontoVal - descontoEspecificoVal + guiaVal;
    if (valorFinal < 0) valorFinal = 0;

    var maxDesconto = totalComDescontoVal + guiaVal;
    if (descontoEspecificoVal > maxDesconto) {
        descontoEspecificoVal = maxDesconto;
        descontoEspecificoInput.value = maxDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    var valorFinalInput = document.getElementById('valor_final');
    if (valorFinalInput) valorFinalInput.value = valorFinal.toFixed(2);
}

// ========== CARREGAR PRODUTOS ORIGINAIS ==========
function carregarProdutosOriginais() {
    console.log('carregarProdutosOriginais chamada');
    var tbody = document.getElementById('produtos-originais');
    if (!tbody) {
        console.log('Tabela produtos-originais não encontrada');
        return;
    }
    
    var rows = tbody.querySelectorAll('tr');
    console.log('Linhas encontradas:', rows.length);
    
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var produtoIdInput = row.querySelector('[name="produtos[' + i + '][produto_id]"]');
        if (!produtoIdInput) continue;
        
        var valorUnitarioInput = row.querySelector('[name="produtos[' + i + '][valor_unitario]"]');
        var quantidadeInput = row.querySelector('[name="produtos[' + i + '][quantidade]"]');
        var partNumberInput = row.querySelector('[name="produtos[' + i + '][part_number]"]');
        
        var cells = row.querySelectorAll('td');
        
        window.produtos.push({
            id: produtoIdInput.value,
            nome: cells[1] ? cells[1].textContent.trim() : '',
            part_number: partNumberInput ? partNumberInput.value : '',
            fornecedor: cells[3] ? cells[3].textContent.trim() : '',
            cor: cells[4] ? cells[4].textContent.trim() : '',
            origem: 'original'
        });
    }
    
    console.log('Produtos carregados:', window.produtos.length);
    
    var tabelaOriginal = tbody.closest('table');
    if (tabelaOriginal) tabelaOriginal.style.display = 'none';
}

// ========== INICIALIZAÇÃO ==========
console.log('Iniciando...');
if (document.readyState === 'loading') {
    console.log('Aguardando DOMContentLoaded');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded disparado');
        carregarProdutosOriginais();
        renderProdutos();
    });
} else {
    console.log('DOM já carregado, executando imediatamente');
    carregarProdutosOriginais();
    renderProdutos();
}

console.log('Script carregado completamente');
console.log('Função selecionarProdutoComQuantidade existe?', typeof window.selecionarProdutoComQuantidade);
</script>