<x-layouts.app :title="__('Visualizar produto')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-primary-600" />
                    Detalhes do Produto
                    @if ($produto->status === 'ativo')
                        <a href="{{ route('produto.inativar', $produto->id) }}">
                            <x-button size="sm" variant="danger">
                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                Desativar
                            </x-button>
                        </a>
                    @else
                        <a href="{{ route('produto.ativar', $produto->id) }}">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                Ativar
                            </x-button>
                        </a>
                    @endif
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Informações completas do produto selecionado.
                </p>

                <!-- Abas -->
                <x-tabs default="basico">
                    <!-- Informações básicas -->
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Código Interno (BRCom)" :value="$produto->id" />
                            <x-show-field label="SKU" :value="$produto->sku" />
                            <x-show-field label="Nome" :value="$produto->nome" />
                            <x-show-field label="Tipo Produto SPED" :value="$produto->tipo_produto_sped" />
                            <x-show-field label="NCM" :value="$produto->ncm" />
                            <x-show-field label="Código de Barras" :value="$produto->codigo_barras" />
                            <x-show-field label="Fornecedor" :value="$produto->fornecedor?->razao_social" />
                            <x-show-field label="Unidade de Medida" :value="$produto->unidade_medida" />
                            <x-show-field label="Marca" :value="$produto->marca" />
                            <x-show-field label="Modelo" :value="$produto->modelo" />
                            @if ($produto->cor)
                                <x-show-field label="Cor" :value="$produto->cor->nome" />
                            @else
                                <x-show-field label="Cor" :value="'Sem cor'" />
                            @endif
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
                    <x-tab name="imagem" label="Imagens">
                        @if ($produto->images->count())
                            <div x-data="{ lightboxAberto: false, imagemSelecionada: '' }" @keydown.escape.window="lightboxAberto = false"
                                class="space-y-6 w-full max-w-6xl mx-auto">
                                @php
                                    $imagemPrincipal =
                                        $produto->images->where('principal', true)->first() ??
                                        $produto->images->first();
                                    $secundarias = $produto->images->where('id', '!=', $imagemPrincipal->id);
                                @endphp

                                <!-- Imagem principal -->
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold mb-2">Imagem Principal</h3>
                                    <img src="{{ asset('storage/' . $imagemPrincipal->caminho) }}"
                                        alt="Imagem Principal"
                                        class="max-w-[30vw] max-h-[30vw] object-contain rounded-xl shadow-md cursor-pointer mx-auto border border-neutral-200 dark:border-neutral-700"
                                        @click="lightboxAberto = true; imagemSelecionada = '{{ asset('storage/' . $imagemPrincipal->caminho) }}'">
                                </div>

                                <!-- Imagens secundárias -->
                                @if ($secundarias->count())
                                    <div class="text-center">
                                        <h3 class="text-lg font-semibold mb-2">Imagens Secundárias</h3>
                                        <div class="flex flex-wrap justify-center gap-4">
                                            @foreach ($secundarias as $img)
                                                <img src="{{ asset('storage/' . $img->caminho) }}"
                                                    alt="Imagem Secundária"
                                                    class="max-w-[30vw] max-h-[30vw] object-contain rounded-lg shadow cursor-pointer border border-neutral-200 dark:border-neutral-700"
                                                    @click="lightboxAberto = true; imagemSelecionada = '{{ asset('storage/' . $img->caminho) }}'">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Lightbox -->
                                <div x-show="lightboxAberto" x-transition
                                    class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                                    <div class="relative w-full max-w-5xl">
                                        <!-- Botão fechar -->
                                        <button @click="lightboxAberto = false"
                                            class="absolute top-4 right-4 text-white text-3xl font-bold">
                                            ✕
                                        </button>

                                        <!-- Imagem em destaque -->
                                        <img :src="imagemSelecionada" alt="Visualização"
                                            class="max-w-full max-h-[90vh] mx-auto object-contain rounded-xl shadow-lg">
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-neutral-500">Nenhuma imagem cadastrada.</p>
                        @endif
                    </x-tab>


                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('produtos.edit', $produto) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                    <a href="{{ route('produtos.index') }}">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                            Voltar para a lista
                        </x-button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
