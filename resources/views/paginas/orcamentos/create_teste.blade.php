<x-layouts.app :title="__('Criar Orçamento')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-600" />
                    Criar Orçamento para {{ $cliente->id }} - {{ $cliente->nome_fantasia }}
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Defina os dados do orçamento, adicione os produtos e acompanhe o valor total em tempo real.
                </p>

                <!-- Pesquisa de Produtos -->
                <div class="space-y-4">
                    <hr />
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-primary-600" />
                        Buscar Produtos
                    </h3>
                    <livewire:lista-produto-orcamento />
                </div>



                <!-- Campos iniciais -->
                <form action="{{ route('orcamentos.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- Produtos Selecionados -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary-600" />
                            Produtos no Orçamento
                        </h3>

                        <div id="produtos-selecionados" class="space-y-4"></div>
                    </div>
                    <div x-data="{ abertoVidro: false }" class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-beaker class="w-5 h-5 text-primary-600" />
                            Vidro Temperado

                            <!-- Botão toggle -->
                            <button type="button" @click="abertoVidro = !abertoVidro"
                                class="ml-2 p-1 rounded-full border border-neutral-300 hover:bg-neutral-100">
                                <span x-show="!abertoVidro">+</span>
                                <span x-show="abertoVidro">-</span>
                            </button>
                        </h3>

                        <!-- Wrapper dos vidros (só aparece quando aberto) -->
                        <div x-show="abertoVidro" x-transition id="vidros-wrapper" class="space-y-4">

                            <!-- Primeiro item de vidro -->
                            <div x-show="abertoVidro" x-transition id="vidros-wrapper" class="space-y-4">
                                <!-- Primeiro vidro -->
                                <div
                                    class="space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                                        <x-input name="vidros[0][altura]" label="Altura (mm)"
                                            placeholder="Digite a altura em mm" oninput="calcularVidro(this)" />
                                        <x-input name="vidros[0][largura]" label="Largura (mm)"
                                            placeholder="Digite a largura em mm" oninput="calcularVidro(this)" />
                                        <x-input name="vidros[0][quantidade]" label="Quantidade" value="1"
                                            placeholder="Digite a quantidade" oninput="calcularVidro(this)" />
                                        <x-input name="vidros[0][preco_m2]" label="Preço do m²"
                                            placeholder="Digite o preço" oninput="calcularVidro(this)" />
                                    </div>
                                    <x-input name="vidros[0][descricao]" label="Descrição do Item"
                                        placeholder="Ex: Vidro incolor 8mm" class="w-full" />

                                    <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                                        <strong>Área (m²):</strong> <span class="area">0.00</span> |
                                        <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                                        <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
                                    </div>

                                    <button type="button" onclick="removeVidro(this)"
                                        class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                                <x-button type="button" onclick="addVidro()">+ Adicionar Vidro</x-button>
                            </div>
                            <br />
                        </div>
                    </div>

                    <div x-data="{ aberto: false }" class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary-600" />
                            Itens para cotação no orçamento

                            <!-- Botão toggle -->
                            <button type="button" @click="aberto = !aberto"
                                class="ml-2 p-1 rounded-full border border-neutral-300 hover:bg-neutral-100">
                                <span x-show="!aberto">+</span>
                                <span x-show="aberto">-</span>
                            </button>
                        </h3>

                        <!-- Wrapper que só aparece quando aberto -->
                        <div x-show="aberto" x-transition id="itens-wrapper" class="space-y-4">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input name="itens[0][nome]" label="Descrição do item"
                                    placeholder="Digite a descrição" class="col-span-2" />
                                <x-input name="itens[0][quantidade]" label="Quantidade"
                                    placeholder="Digite a quantidade" />
                                <x-select name="itens[0][cor]" label="Cor">
                                    <option value="">Selecione...</option>
                                    @foreach ($cores as $cor)
                                        <option value="{{ $cor->id }}">{{ $cor->nome }}</option>
                                    @endforeach
                                </x-select>
                                <x-select name="itens[0][fornecedor_id]" label="Fornecedor"
                                    class="col-span-2 md:col-span-4">
                                    <option value="">Selecione...</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                                    @endforeach
                                </x-select>
                            </div>

                            <x-textarea name="itens[0][observacoes]" label="Observações"
                                placeholder="Digite os detalhes adicionais..." rows="2" class="col-span-4" />

                            <x-button type="button" onclick="addItem()">
                                + Adicionar cotação de item
                            </x-button>
                        </div>
                    </div>


                    <!-- Endereço de entrega -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Endereço de entrega
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                            Preencha o CEP primeiro e aguarde os dados serem preenchidos automaticamente.
                        </p>
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
                        <x-input name="nome_obra" label="Nome da Obra" placeholder="Digite o nome da obra"
                            required />
                        <x-input id="valor_total" name="valor_total" label="Valor Total dos Itens (R$)" readonly
                            value="0,00" class="bg-gray-100" />
                        <x-input name="desconto_aprovado" readonly label="Desconto do cliente %"
                            value="{{ $cliente->desconto ?? 0 }}" />
                        <x-input name="desconto" label="Desconto na venda %" type="number" min="0"
                            max="30" value="0" placeholder="Digite a porcentagem de desconto (0 a 30)" />

                        <x-input name="frete" label="Valor frete (R$)" type="number" min="0"
                            value="0" placeholder="Digite o valor do frete" />

                        <!-- Novos campos -->
                        <x-input id="valor_sem_desconto_final" name="valor_sem_desconto_final"
                            label="Valor Final s/ desconto (R$)" readonly value="0,00" class="bg-gray-100" />
                        <x-input id="valor_final" name="valor_final" label="Valor Final c/ desconto (R$)" readonly
                            value="0,00" class="bg-gray-100" />
                    </div>

                    <!-- Ações -->
                    <br />
                    <div class="flex gap-4">
                        <x-button type="submit">Salvar Orçamento</x-button>
                        <x-button type="reset">Limpar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/valida.js') }}"></script>
    <!-- Script para manipulação de produtos -->
    <script>
        let vidroIndex = 1;

        function addVidro() {
        const wrapper = document.getElementById('vidros-wrapper');
        const vidroDiv = document.createElement('div');
        vidroDiv.classList = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
        vidroDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                <x-input name="vidros[\${vidroIndex}][altura]" label="Altura (mm)" placeholder="Digite a altura em mm" oninput="calcularVidro(this)" />
                <x-input name="vidros[\${vidroIndex}][largura]" label="Largura (mm)" placeholder="Digite a largura em mm" oninput="calcularVidro(this)" />
                <x-input name="vidros[\${vidroIndex}][quantidade]" label="Quantidade" value="1" placeholder="Digite a quantidade" oninput="calcularVidro(this)" />
                <x-input name="vidros[\${vidroIndex}][preco_m2]" label="Preço do m²" placeholder="Digite o preço" oninput="calcularVidro(this)" />
            </div>
            <x-input name="vidros[\${vidroIndex}][descricao]" label="Descrição do Item" placeholder="Ex: Vidro incolor 8mm" class="w-full" />
            <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                <strong>Área (m²):</strong> <span class="area">0.00</span> |
                <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
            </div>
            <button type="button" onclick="removeVidro(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
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

        // desconto
        const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
        let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;
        if (descontoOrcamento < 0) descontoOrcamento = 0;
        if (descontoOrcamento > 30) descontoOrcamento = 30;
        const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

        let valorComDesconto = valor - (valor * (descontoAplicado / 100));

        container.querySelector('.area').textContent = area.toFixed(2);
        container.querySelector('.valor').textContent = valor.toFixed(2);
        container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2);

        atualizarValorFinal();
    }


        let itemIndex = 1;

        function addItem() {
            const wrapper = document.getElementById('itens-wrapper');

            // Cria o container do item
            const itemDiv = document.createElement('div');
            itemDiv.classList = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";

            // Conteúdo do bloco
            itemDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <x-input name="itens[\${itemIndex}][nome]" label="Descrição do item" placeholder="Digite a descrição" required class="col-span-2" />
                <x-input name="itens[\${itemIndex}][quantidade]" label="Quantidade" placeholder="Digite a quantidade" />
                <x-input name="itens[\${itemIndex}][cor]" label="Cor" placeholder="Digite a cor" />
                <x-select name="itens[\${itemIndex}][fornecedor_id]" label="Fornecedor" class="col-span-2 md:col-span-4">
                    <option value="">Selecione...</option>
                    @foreach ($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                    @endforeach
                </x-select>
            </div>

            <x-textarea name="itens[\${itemIndex}][observacoes]" label="Observações" placeholder="Digite os detalhes adicionais..." rows="2" class="col-span-4" />

            <button type="button" onclick="removeItem(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
        `;

            // Adiciona no wrapper
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
            let total = 0;
            let totalComDesconto = 0;

            // pega os descontos
            const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
            let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;

            if (descontoOrcamento < 0) descontoOrcamento = 0;
            if (descontoOrcamento > 30) descontoOrcamento = 30;

            const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

            produtos.forEach((p, i) => {
                const subtotal = p.preco * p.quantidade;
                const subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));
                total += subtotal;
                totalComDesconto += subtotalComDesconto;

                const div = document.createElement('div');
                div.classList =
                    "grid grid-cols-1 md:grid-cols-3 gap-4 mt-2 border rounded-xl dark:border-neutral-700 relative";
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

            // Atualiza campo total sem desconto
            document.getElementById('valor_total').value = total.toFixed(2);

            // Chama atualização final
            atualizarValorFinal(total, totalComDesconto);
        }

        function atualizarValorFinal(total = null, totalComDesconto = null) {
            // Se não veio do renderProdutos, calcula os totais
            if (total === null) {
                total = parseFloat(document.getElementById('valor_total').value) || 0;
            }
            if (totalComDesconto === null) {
                const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
                let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;

                if (descontoOrcamento < 0) descontoOrcamento = 0;
                if (descontoOrcamento > 30) descontoOrcamento = 30;

                const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);
                totalComDesconto = total - (total * (descontoAplicado / 100));
            }

            let frete = parseFloat(document.querySelector('[name="frete"]').value) || 0;

            // valor final com e sem desconto
            const valorSemDescontoFinal = total + frete;
            const valorFinalComDesconto = totalComDesconto + frete;

            // atualiza inputs
            document.getElementById('valor_sem_desconto_final').value = valorSemDescontoFinal.toFixed(2);
            document.querySelector('[name="valor_final"]').value = valorFinalComDesconto.toFixed(2);
        }

        // Listeners
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelector('[name="desconto"]').addEventListener("input", function() {
                let val = parseFloat(this.value) || 0;

                if (val < 0) val = 0;
                if (val > 30) val = 30;

                this.value = val;
                renderProdutos(); // recalcula com valor corrigido
            });
            document.querySelector('[name="frete"]').addEventListener("input", renderProdutos);
        });
    </script>
</x-layouts.app>
