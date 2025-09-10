<x-layouts.app :title="__('Receber produto')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="truck" class="w-5 h-5 text-primary-600" />
                    Receber produto
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Insira o produto que deseja receber.
                </p>

                <form action="{{ route('movimentacao.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="clipboard" class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="tipo_entrada" label="Tipo de movimentação">
                                <option value="">Selecione</option>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                            </x-select>
                            <x-select name="fornecedor_id" label="Fornecedor">
                                <option value="">Selecione</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="pedido_id" label="Pedido">
                                <option value="">Selecione</option>
                                @foreach ($pedidos as $pedido)
                                    <option value="{{ $pedido->id }}">{{ $pedido->id }}</option>
                                @endforeach
                            </x-select>
                            <x-input name="nota_fiscal_fornecedor" label="Nota Fiscal Fornecedor" placeholder="(opcional)" />
                            <x-input name="romaneiro" label="Romaneiro" placeholder="(opcional)" />
                          
                        </div>
                    </div>


                    <!-- Produtos -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Produtos
                        </h3>

                        <div id="produtos-wrapper" class="space-y-4">
                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                <x-input name="produtos[0][nome]" label="Nome" placeholder="Produto" /> 
                                <x-select name="produtos[0][perecivel]" label="Perecível">
                                    <option value="">Selecione</option>
                                    <option value="sim">Sim</option>
                                    <option value="nao">Não</option>
                                </x-select>
                                <x-input name="produtos[0][validade]" label="Validade" placeholder="Validade" />
                                <x-input name="produtos[0][quantidade]" label="Quantidade" placeholder="Quantidade" />
                                <x-input name="produtos[0][valor]" label="Valor unitário"
                                    placeholder="Valor unitário" />
                                <x-input name="produtos[0][valor_total]" label="Valor total"
                                    placeholder="Valor total" />
                                <x-input name="produtos[0][armazem]" label="Endereço" placeholder="Armazém" />
                                <x-input name="produtos[0][corredor]" label="Corredor" placeholder="Corredor" />
                                <x-input name="produtos[0][posicao]" label="Posição" placeholder="Posição" />
                               
                            </div>
                        </div>

                        <x-button type="button" onclick="addProduto()">
                            + Adicionar Produto
                        </x-button>
                    </div>

                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Cadastrar Movimentação</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        let produtoIndex = 1;

        function addProduto() {
            const wrapper = document.getElementById('produtos-wrapper');
            const div = document.createElement('div');
            div.classList = "grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative";
            div.innerHTML = `
            <x-input name="produtos[\${produtoIndex}][nome]" label="Nome" placeholder="Nome do produto" />
            <x-select name="produtos[\${produtoIndex}][perecivel]" label="Perecível">
                <option value="">Selecione</option>
                <option value="sim">Sim</option>
                <option value="nao">Não</option>
            </x-select>
            <x-input name="produtos[\${produtoIndex}][validade]" label="Validade" placeholder="Validade" />
            <x-input name="produtos[\${produtoIndex}][quantidade]" label="Quantidade" placeholder="Quantidade" />
            <x-input name="produtos[\${produtoIndex}][valor]" label="Valor unitário" placeholder="Valor unitário" />
            <x-input name="produtos[\${produtoIndex}][valor_total]" label="Valor total" placeholder="Valor total" />
            <x-input name="produtos[\${produtoIndex}][armazem]" label="Armazém" placeholder="Armazém" />
            <x-input name="produtos[\${produtoIndex}][corredor]" label="Corredor" placeholder="Corredor" />
            <x-input name="produtos[\${produtoIndex}][posicao]" label="Posição" placeholder="Posição" />
            
            <button type="button" onclick="removeProduto(this)"
                class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                <x-icon name="trash" class="w-5 h-5" />
            </button>`;
            wrapper.appendChild(div);
            produtoIndex++;
        }

        function removeProduto(button) {
            button.closest('div').remove();
        }
    </script>
</x-layouts.app>
