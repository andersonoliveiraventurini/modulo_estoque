<x-layouts.app :title="__('Visualizar Encomenda')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->

                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                            Dados da Encomenda #{{ $consulta->id }} @if ($consulta->pdf_path)
                                <a href="{{ asset('storage/' . $consulta->pdf_path) }}" target="_blank"
                                    rel="noopener">
                                    <x-button size="sm" variant="primary">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                        PDF
                                    </x-button>
                                </a>
                            @endif
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Informações detalhadas da encomenda selecionada.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        @if ($consulta->orcamento_id != null)
                            <a href="{{ route('orcamentos.mostrar', $consulta->orcamento_id) }}"
                                class="text-primary-600 hover:underline text-sm font-medium">
                                Ver Orçamento
                            </a>
                        @endif
                        @if ($consulta->fornecedor_id)
                            <a href="{{ route('fornecedores.show', $consulta->fornecedor_id ?? null) }}"
                                class="text-secondary-600 hover:underline text-sm font-medium">
                                Ver Fornecedor
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Abas -->
                <x-tabs default="basico">
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Descrição" :value="$consulta->descricao" />
                            <x-show-field label="Cor" :value="$consulta->cor->nome" />
                            <x-show-field label="Quantidade" :value="$consulta->quantidade" />
                        </div>
                    </x-tab>

                    <x-tab name="precos" label="Preços">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Preço de Compra(R$)" :value="number_format($consulta->preco_compra, 2, ',', '.')" />
                            <x-show-field label="Preço de Venda (R$)" :value="number_format($consulta->preco_venda, 2, ',', '.')" />
                        </div>
                    </x-tab>

                    <x-tab name="usuarios" label="Usuários">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Cliente" :value="$consulta->cliente_id . ' - ' . $consulta->cliente?->nome" />
                            <x-show-field label="Vendedor" :value="$consulta->usuario?->name" />
                            <x-show-field label="Comprador (Cadastro)" :value="$consulta->comprador?->name" />
                        </div>
                    </x-tab>

                    <x-tab name="fornecedor" label="Fornecedor">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">                            
                            <x-show-field label="Prazo entrega" :value="$consulta->prazo_entrega" />
                            <x-show-field label="Fornecedor" :value="$consulta->fornecedor?->nome_fantasia" />
                        </div>
                    </x-tab>

                    <x-tab name="observacao" label="Observação">
                        <div class="p-4 border rounded-xl dark:border-neutral-700">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $consulta->observacao ?: 'Nenhuma observação adicionada.' }}
                            </p>
                        </div>
                    </x-tab>
                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('consulta_preco.edit', $consulta) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                    <a href="{{ route('consulta_preco.index') }}">
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
