<x-layouts.app :title="__('Visualizar consulta de preço')">
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
                            Dados da Consulta de Preço #{{ $consultaPreco->id }} @if ($consultaPreco->pdf_path)
                                <a href="{{ asset('storage/' . $consultaPreco->pdf_path) }}" target="_blank"
                                    rel="noopener">
                                    <x-button size="sm" variant="primary">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                        PDF
                                    </x-button>
                                </a>
                            @endif
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Informações detalhadas da consulta de preço selecionada.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        @if ($consultaPreco->orcamento_id != null)
                            <a href="{{ route('orcamentos.mostrar', $consultaPreco->orcamento_id) }}"
                                class="text-primary-600 hover:underline text-sm font-medium">
                                Ver Orçamento
                            </a>
                        @endif
                        @if ($consultaPreco->fornecedor_id)
                            <a href="{{ route('fornecedores.show', $consultaPreco->fornecedor_id ?? null) }}"
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
                            <x-show-field label="Descrição" :value="$consultaPreco->descricao" />
                            <x-show-field label="Cor" :value="$consultaPreco->cor" />
                            <x-show-field label="Quantidade" :value="$consultaPreco->quantidade" />
                        </div>
                    </x-tab>

                    <x-tab name="precos" label="Preços">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Preço de Compra(R$)" :value="number_format($consultaPreco->preco_compra, 2, ',', '.')" />
                            <x-show-field label="Preço de Venda (R$)" :value="number_format($consultaPreco->preco_venda, 2, ',', '.')" />
                        </div>
                    </x-tab>

                    <x-tab name="usuarios" label="Usuários">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Cliente" :value="$consultaPreco->cliente_id . ' - ' . $consultaPreco->cliente?->nome" />
                            <x-show-field label="Vendedor" :value="$consultaPreco->usuario?->name" />
                            <x-show-field label="Comprador (Cadastro)" :value="$consultaPreco->comprador?->name" />
                        </div>
                    </x-tab>

                    <x-tab name="fornecedor" label="Fornecedor">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">                            
                            <x-show-field label="Prazo entrega" :value="$consultaPreco->prazo_entrega" />
                            <x-show-field label="Fornecedor" :value="$consultaPreco->fornecedor?->nome_fantasia" />
                        </div>
                    </x-tab>

                    <x-tab name="observacao" label="Observação">
                        <div class="p-4 border rounded-xl dark:border-neutral-700">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $consultaPreco->observacao ?: 'Nenhuma observação adicionada.' }}
                            </p>
                        </div>
                    </x-tab>
                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('consulta_preco.edit', $consultaPreco) }}">
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
