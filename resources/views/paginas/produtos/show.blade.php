<x-layouts.app :title="__('Visualizar produto')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-primary-600" />
                    Detalhes do Produto
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Informações completas do produto selecionado.
                </p>

                <!-- Abas -->
                <x-tabs default="basico">
                    <!-- Informações básicas -->
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Código Interno (BRCom)" :value="$produto->codigo_brcom" />
                            <x-show-field label="SKU" :value="$produto->sku" />
                            <x-show-field label="Nome" :value="$produto->nome" />
                            <x-show-field label="Tipo Produto SPED" :value="$produto->tipo_produto_sped" />
                            <x-show-field label="NCM" :value="$produto->ncm" />
                            <x-show-field label="Código de Barras" :value="$produto->codigo_barras" />
                            <x-show-field label="Fornecedor" :value="$produto->fornecedor?->razao_social" />
                            <x-show-field label="Unidade de Medida" :value="$produto->unidade_medida" />
                            <x-show-field label="Marca" :value="$produto->marca" />
                            <x-show-field label="Modelo" :value="$produto->modelo" />
                            <x-show-field label="Cor" :value="$produto->cor" />
                            <x-show-field label="Peso" :value="$produto->peso" />
                            <x-show-field label="Ativo" :value="$produto->ativo ? 'Sim' : 'Não'" />
                        </div>
                    </x-tab>

                    <!-- Preços e Estoque -->
                    <x-tab name="financeiro" label="Preço - Estoque">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Preço de Custo (R$)" :value="number_format($produto->preco_custo, 2, ',', '.')" />
                            <x-show-field label="Preço de Venda (R$)" :value="number_format($produto->preco_venda, 2, ',', '.')" />
                            <x-show-field label="Estoque Mínimo" :value="number_format($produto->estoque_minimo, 2, ',', '.')" />
                            <x-show-field label="Estoque Atual" :value="number_format($produto->estoque_atual, 2, ',', '.')" />
                        </div>
                    </x-tab>

                    <!-- Descrição e Observações -->
                    <x-tab name="descricao" label="Descrição">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium">Descrição</h3>
                                <p class="text-neutral-700 dark:text-neutral-300">
                                    {{ $produto->descricao ?? 'Não informada.' }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium">Observações</h3>
                                <p class="text-neutral-700 dark:text-neutral-300">
                                    {{ $produto->observacoes ?? 'Não informada.' }}
                                </p>
                            </div>
                        </div>
                    </x-tab>

                    <!-- Imagem -->
                    <x-tab name="imagem" label="Imagem">
                        <div class="flex flex-col items-center">
                            @if($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}"
                                     alt="Imagem do Produto"
                                     class="rounded-2xl shadow-md max-w-xs border border-neutral-200 dark:border-neutral-700">
                            @else
                                <p class="text-sm text-neutral-500">Nenhuma imagem cadastrada.</p>
                            @endif
                        </div>
                    </x-tab>
                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <x-button href="{{ route('produtos.edit', $produto) }}" class="bg-primary-600 text-white">
                        Editar
                    </x-button>
                    <x-button href="{{ route('produtos.index') }}">
                        Voltar
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
