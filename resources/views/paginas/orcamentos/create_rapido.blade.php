<x-layouts.app :title="__('Criar Orçamento')">
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
                    Criar Orçamento para Cliente {{ $cliente->id }} - {{ $cliente->nome_fantasia }}
                </h2>
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
                    <livewire:lista-produto-fixo-orcamento />
                </div>

                <!-- Campos iniciais -->
                <form action="{{ route('orcamentos.store') }}" method="POST" class="space-y-8"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <!-- Token CSRF seria aqui -->

                    <!-- Produtos Selecionados -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 7h9m0 0V10"></path>
                            </svg>
                            Produtos no Orçamento
                        </h3>

                        <div id="produtos-selecionados" class="space-y-4"></div>
                    </div>

                    <!-- Seção de Vidros Corrigida -->
                    <div x-data="{ abertoVidro: false }" class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                </path>
                            </svg>
                            Vidro ou Esteira

                            <!-- Botão toggle -->
                            <button type="button" @click="abertoVidro = !abertoVidro"
                                class="ml-2 p-1 rounded-full border border-neutral-300 hover:bg-neutral-100">
                                <span x-show="!abertoVidro">+</span>
                                <span x-show="abertoVidro">-</span>
                            </button>
                        </h3>

                        <!-- Wrapper dos vidros (só aparece quando aberto) -->
                        <div x-show="abertoVidro" x-transition id="vidros-wrapper" class="space-y-4">
                            <!-- Primeiro vidro -->
                            <div
                                class="space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Descrição do Item</label>
                                        <input type="text" name="vidros[0][descricao]"
                                            placeholder="Ex: Vidro incolor 8mm"
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                                        <input type="number" name="vidros[0][quantidade]" value="1"
                                            placeholder="Digite a quantidade" oninput="calcularVidro(this)"
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Preço do m²</label>
                                        <input type="number" step="0.01" name="vidros[0][preco_m2]"
                                            placeholder="Digite o preço" oninput="calcularVidro(this)"
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Altura (mm)</label>
                                        <input type="number" name="vidros[0][altura]"
                                            placeholder="Digite a altura em mm" oninput="calcularVidro(this)"
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Largura (mm)</label>
                                        <input type="number" name="vidros[0][largura]"
                                            placeholder="Digite a largura em mm" oninput="calcularVidro(this)"
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                </div>

                                <!-- Campos hidden para valores calculados -->
                                <input type="hidden" name="vidros[0][area]" class="area-hidden" />
                                <input type="hidden" name="vidros[0][valor_total]" class="valor-hidden" />
                                <input type="hidden" name="vidros[0][valor_com_desconto]"
                                    class="valor-desconto-hidden" />

                                <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                                    <strong>Área (m²):</strong> <span class="area">0.00</span> |
                                    <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                                    <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
                                </div>
                            </div>

                            <button type="button" onclick="addVidro()"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                + Adicionar Vidro/Esteira
                            </button>
                            <br />
                        </div>
                    </div>

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
                            Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" value="{{ old('entrega_cep') }}" />
                        </div>

                        <!-- Wrapper que será ocultado até o CEP ser válido -->
                        <div id="endereco-entrega-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" value="{{ old('entrega_cidade') }}" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" value="{{ old('entrega_estado') }}" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" value="{{ old('entrega_bairro') }}" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly"
                                    value="{{ old('entrega_logradouro') }}" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    value="{{ old('entrega_numero') }}" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc."
                                    value="{{ old('entrega_compl') }}" />
                            </div>
                        </div>
                    </div>

                    <br />
                    <hr /><br />

                    <!-- Valores e descontos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome da Obra</label>
                            <input type="text" name="nome_obra" placeholder="Digite o nome da obra" required
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor Total dos Itens (R$)</label>
                            <input type="text" id="valor_total" name="valor_total" readonly value="0,00"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Desconto do cliente %</label>
                            <input type="number" name="desconto_aprovado" value="10" readonly
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Desconto na venda %</label>
                            <input type="number" name="desconto" min="0" max="30" value="0"
                                placeholder="Digite a porcentagem de desconto (0 a 30)"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor frete (R$)</label>
                            <input type="number" name="frete" min="0" value="0"
                                placeholder="Digite o valor do frete"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor Final s/ desconto (R$)</label>
                            <input type="text" id="valor_sem_desconto_final" name="valor_sem_desconto_final"
                                readonly value="0,00"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor Final c/ desconto (R$)</label>
                            <input type="text" id="valor_final" name="valor_final" readonly value="0,00"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                        </div>
                    </div>

                    <!-- Ações -->
                    <br />
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

    <script src="{{ asset('js/valida.js') }}"></script>
    <script>
        let vidroIndex = 1;

        function addVidro() {
            const wrapper = document.getElementById('vidros-wrapper');
            const vidroDiv = document.createElement('div');
            vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
            vidroDiv.innerHTML = `
                <button type="button" onclick="removeVidro(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                    Remover
                </button><br/>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição do Item</label>
                        <input type="text" name="vidros[${vidroIndex}][descricao]" placeholder="Ex: Vidro incolor 8mm" 
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                        <input type="number" name="vidros[${vidroIndex}][quantidade]" value="1" placeholder="Digite a quantidade" 
                               oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Preço do m²</label>
                        <input type="number" step="0.01" name="vidros[${vidroIndex}][preco_m2]" placeholder="Digite o preço" 
                               oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Altura (mm)</label>
                        <input type="number" name="vidros[${vidroIndex}][altura]" placeholder="Digite a altura em mm" 
                               oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Largura (mm)</label>
                        <input type="number" name="vidros[${vidroIndex}][largura]" placeholder="Digite a largura em mm" 
                               oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                    </div>                    
                </div>                

                <!-- Campos hidden para valores calculados -->
                <input type="hidden" name="vidros[${vidroIndex}][area]" class="area-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_total]" class="valor-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_com_desconto]" class="valor-desconto-hidden" />

                <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                    <strong>Área (m²):</strong> <span class="area">0.00</span> |
                    <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                    <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
                </div>
                
            `;
            wrapper.appendChild(vidroDiv);
            vidroIndex++;
        }

        function removeVidro(button) {
            button.closest('div.space-y-2').remove();
            atualizarValorFinal();
        }

        function calcularVidro(element) {
            const container = element.closest('div.space-y-2');
            let altura = parseFloat(container.querySelector('[name*="[altura]"]').value) || 0;
            let largura = parseFloat(container.querySelector('[name*="[largura]"]').value) || 0;
            let quantidade = parseInt(container.querySelector('[name*="[quantidade]"]').value) || 0;
            let precoM2 = parseFloat(container.querySelector('[name*="[preco_m2]"]').value) || 0;

            let area = (altura / 1000) * (largura / 1000);
            let valor = area * precoM2 * quantidade;

            // Aplicar desconto
            const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
            let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;
            if (descontoOrcamento < 0) descontoOrcamento = 0;
            if (descontoOrcamento > 30) descontoOrcamento = 30;
            const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

            let valorComDesconto = valor - (valor * (descontoAplicado / 100));

            // Atualizar displays
            container.querySelector('.area').textContent = area.toFixed(2);
            container.querySelector('.valor').textContent = valor.toFixed(2);
            container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2);

            // Atualizar campos hidden para envio
            container.querySelector('.area-hidden').value = area.toFixed(2);
            container.querySelector('.valor-hidden').value = valor.toFixed(2);
            container.querySelector('.valor-desconto-hidden').value = valorComDesconto.toFixed(2);

            // Forçar atualização do valor final
            setTimeout(() => {
                atualizarValorFinal();
            }, 10);
        }

        function calcularTotalVidros() {
            let totalVidros = 0;
            let totalVidrosComDesconto = 0;

            // Percorrer todos os vidros
            document.querySelectorAll('#vidros-wrapper .space-y-2').forEach(container => {
                const valorElement = container.querySelector('.valor-hidden');
                const valorDescontoElement = container.querySelector('.valor-desconto-hidden');

                if (valorElement && valorDescontoElement) {
                    totalVidros += parseFloat(valorElement.value) || 0;
                    totalVidrosComDesconto += parseFloat(valorDescontoElement.value) || 0;
                }
            });

            return {
                totalVidros,
                totalVidrosComDesconto
            };
        }

        let itemIndex = 1;

        const cores = @json($cores);
        const fornecedores = @json($fornecedores);

        function addItem() {
            const wrapper = document.getElementById('itens-wrapper');
            const itemDiv = document.createElement('div');
            itemDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";

            // Monta opções de cores
            let coresOptions = `<option value="">Selecione...</option>`;
            cores.forEach(cor => {
                coresOptions += `<option value="${cor.nome}">${cor.nome}</option>`;
            });

            // Monta opções de fornecedores
            let fornecedoresOptions = `<option value="">Selecione...</option>`;
            fornecedores.forEach(f => {
                fornecedoresOptions += `<option value="${f.id}">${f.nome_fantasia}</option>`;
            });

            itemDiv.innerHTML = ` <button type="button" onclick="removeItem(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                 Remover
            </button>
            <br/>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Descrição do item</label>
                    <input type="text" name="itens[${itemIndex}][nome]" placeholder="Digite a descrição" required
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                    <input type="number" name="itens[${itemIndex}][quantidade]" placeholder="Digite a quantidade"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cor</label>
                    <select name="itens[${itemIndex}][cor]" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        ${coresOptions}
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Fornecedor</label>
                    <select name="itens[${itemIndex}][fornecedor_id]" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        ${fornecedoresOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="itens[${itemIndex}][observacoes]" placeholder="Digite os detalhes adicionais..." rows="2"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>
            </div>

           
        `;

            wrapper.appendChild(itemDiv);
            itemIndex++;
        }


        function removeItem(button) {
            button.closest('div.space-y-2').remove();
        }

        let produtos = [];

        function adicionarProduto(id, nome, preco) {
            if (produtos.find(p => p.id === id)) {
                alert("Produto já adicionado!");
                return;
            }

            const produto = {
                id,
                nome,
                preco: parseFloat(preco),
                quantidade: 1
            };
            produtos.push(produto);
            renderProdutos();
        }

        function alterarQuantidade(index, valor) {
            produtos[index].quantidade = parseInt(valor) || 1;
            renderProdutos();
        }

        function removerProduto(index) {
            produtos.splice(index, 1);
            renderProdutos();
        }

        function renderProdutos() {
            const wrapper = document.getElementById('produtos-selecionados');
            wrapper.innerHTML = '';
            let totalProdutos = 0;
            let totalProdutosComDesconto = 0;

            // Calcular descontos
            const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
            let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;

            if (descontoOrcamento < 0) descontoOrcamento = 0;
            if (descontoOrcamento > 30) descontoOrcamento = 30;

            const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

            produtos.forEach((p, i) => {
                const subtotal = p.preco * p.quantidade;
                const subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));
                totalProdutos += subtotal;
                totalProdutosComDesconto += subtotalComDesconto;

                const div = document.createElement('div');
                div.className = "grid grid-cols-1 md:grid-cols-3 gap-3 mt-2 border rounded-xl p-4 relative";
                div.innerHTML = `
                    <input type="hidden" name="produtos[${i}][id]" value="${p.id}">
                    <div>
                        <label class="text-sm font-medium">Produto</label>
                        <input type="text" value="${p.nome}" readonly class="border rounded-lg px-3 py-2 w-full bg-gray-100" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Preço Unitário (R$)</label>
                        <input type="text" value="${p.preco.toFixed(2)}" readonly class="border rounded-lg px-3 py-2 w-full bg-gray-100" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Quantidade</label>
                        <input type="number" name="produtos[${i}][quantidade]" value="${p.quantidade}" min="1"
                            onchange="alterarQuantidade(${i}, this.value)"
                            class="border rounded-lg px-3 py-2 w-full" />
                    </div>
                    <div class="flex flex-col justify-between">
                        <span class="text-sm font-semibold">
                            Subtotal: R$ ${subtotal.toFixed(2)}<br>
                            <span class="text-green-600">c/ desconto: R$ ${subtotalComDesconto.toFixed(2)}</span>
                        </span>
                        <button type="button" onclick="removerProduto(${i})" class="text-red-600 hover:text-red-800 flex items-center gap-1 mt-2">
                            🗑 Remover
                        </button>
                    </div>
                `;
                wrapper.appendChild(div);
            });

            // Calcular total dos vidros
            const {
                totalVidros,
                totalVidrosComDesconto
            } = calcularTotalVidros();

            // Total geral (produtos + vidros)
            const totalGeral = totalProdutos + totalVidros;
            const totalGeralComDesconto = totalProdutosComDesconto + totalVidrosComDesconto;

            // Atualizar campo total
            document.getElementById('valor_total').value = totalGeral.toFixed(2);

            // Atualizar valores finais
            atualizarValorFinal(totalGeral, totalGeralComDesconto);
        }

        function atualizarValorFinal(total = null, totalComDesconto = null) {
            console.log('Atualizando valor final...'); // Debug

            // Se não veio parâmetros, calcular
            if (total === null) {
                // Total dos produtos
                let totalProdutos = 0;
                let totalProdutosComDesconto = 0;

                const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
                let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;

                if (descontoOrcamento < 0) descontoOrcamento = 0;
                if (descontoOrcamento > 30) descontoOrcamento = 30;

                const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);
                console.log('Desconto aplicado:', descontoAplicado); // Debug

                produtos.forEach(p => {
                    const subtotal = p.preco * p.quantidade;
                    const subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));
                    totalProdutos += subtotal;
                    totalProdutosComDesconto += subtotalComDesconto;
                });

                // Total dos vidros
                const {
                    totalVidros,
                    totalVidrosComDesconto
                } = calcularTotalVidros();
                console.log('Total vidros:', totalVidros, 'Com desconto:', totalVidrosComDesconto); // Debug

                total = totalProdutos + totalVidros;
                totalComDesconto = totalProdutosComDesconto + totalVidrosComDesconto;

                // Atualizar o campo valor_total
                document.getElementById('valor_total').value = total.toFixed(2);
            }

            const frete = parseFloat(document.querySelector('[name="frete"]').value) || 0;

            // Valores finais
            const valorSemDescontoFinal = total + frete;
            const valorFinalComDesconto = totalComDesconto + frete;

            console.log('Valor sem desconto final:', valorSemDescontoFinal); // Debug
            console.log('Valor final com desconto:', valorFinalComDesconto); // Debug

            // Atualizar campos
            document.getElementById('valor_sem_desconto_final').value = valorSemDescontoFinal.toFixed(2);
            document.getElementById('valor_final').value = valorFinalComDesconto.toFixed(2);
        }

        // Event listeners
        document.addEventListener("DOMContentLoaded", () => {
            // Listener para desconto
            document.querySelector('[name="desconto"]').addEventListener("input", function() {
                let val = parseFloat(this.value) || 0;

                if (val < 0) val = 0;
                if (val > 30) val = 30;

                this.value = val;

                // Recalcular todos os vidros
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });

                // Recalcular produtos
                renderProdutos();
            });

            // Listener para frete
            document.querySelector('[name="frete"]').addEventListener("input", () => {
                atualizarValorFinal();
            });

            // Listener para desconto aprovado (caso seja editável)
            document.querySelector('[name="desconto_aprovado"]').addEventListener("input", function() {
                // Recalcular todos os vidros
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });

                // Recalcular produtos
                renderProdutos();
            });
        });
    </script>
</x-layouts.app>
