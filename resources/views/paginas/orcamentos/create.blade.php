<x-layouts.app :title="__('Criar Orçamento')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-600" />
                    Criar Orçamento
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

                        <div id="produtos-selecionados" class="space-y-4">
                            <!-- Produtos adicionados aparecerão aqui -->
                        </div>
                    </div>
                    <br />
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input name="nome_obra" label="Nome da Obra" placeholder="Digite o nome da obra" required />
                        <x-input id="valor_total" name="valor_total" label="Valor Total (R$)" readonly value="0,00"
                            class="bg-gray-100" />
                        <x-input name="endereco_entrega" label="Endereço de Entrega (opcional)"
                            placeholder="Digite o endereço" />
                    </div>

                    <!-- Ações -->
                    <br />
                    <div class="flex gap-4">
                        <x-button type="submit" >Salvar Orçamento</x-button>
                        <x-button type="reset">Limpar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para manipulação de produtos -->
    <script>
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

            produtos.forEach((p, i) => {
                const subtotal = p.preco * p.quantidade;
                total += subtotal;

                const div = document.createElement('div');
                div.classList =
                    "grid grid-cols-1 md:grid-cols-3 gap-4 mt-2 border rounded-xl dark:border-neutral-700 relative";
                div.innerHTML = `
                    <input type="hidden" name="produtos[${i}][id]" value="${p.id}">
                    <x-input label="Produto" value="${p.nome}" readonly class="border rounded-lg px-3 py-2 w-full bg-gray-100" />
                    <x-input label="Preço Unitário (R$)" value="${p.preco.toFixed(2)}" readonly class="border rounded-lg px-3 py-2 w-full bg-gray-100" />
                    <x-input label="Quantidade" name="produtos[${i}][quantidade]" value="${p.quantidade}" min="1"
                            onchange="alterarQuantidade(${i}, this.value)" class="border rounded-lg px-3 py-2 w-full bg-gray-100" />

                    <div class="flex flex-col justify-between">
                        <span class="text-sm font-semibold">Subtotal: R$ ${subtotal.toFixed(2)}</span>
                        <button type="button" onclick="removerProduto(${i})" class="text-red-600 hover:text-red-800 flex items-center gap-1 mt-2">
                            <x-heroicon-o-trash class="w-5 h-5" />
                            Remover
                        </button>
                    </div>
                `;
                wrapper.appendChild(div);
            });

            document.getElementById('valor_total').value = total.toFixed(2);
        }
    </script>
</x-layouts.app>
