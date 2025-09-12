<x-layouts.app :title="__('Classificar Fornecedor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">


                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-star class="w-5 h-5 text-primary-600" />
                            Classificação de Fornecedor
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Avalie o fornecedor nos critérios abaixo e registre a classificação.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        <a href="{{ route('fornecedores.edit', $fornecedor->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Editar Fornecedor
                        </a>
                        <a href="{{ route('fornecedores.precos', $fornecedor->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Tabela de preços do fornecedor
                        </a>
                        <a href="{{ route('fornecedores.show', $fornecedor->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Mostrar detalhes do fornecedor
                        </a>
                    </div>
                </div>

                <form action="{{ route('fornecedores.classificacao.store', $fornecedor->id ?? null) }}" method="POST"
                    class="space-y-8">
                    @csrf

                    <!-- Dados da Classificação -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-primary-600" />
                            Critérios de Avaliação
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="prazo_entrega" label="Prazo de Entrega">
                                <option value="">Selecione</option>
                                <option value="1">Muito Ruim</option>
                                <option value="2">Ruim</option>
                                <option value="3">Regular</option>
                                <option value="4">Bom</option>
                                <option value="5">Excelente</option>
                            </x-select>

                            <x-select name="produto_defeitos" label="Produto com Defeitos">
                                <option value="">Selecione</option>
                                <option value="1">Frequente</option>
                                <option value="2">Ocasional</option>
                                <option value="3">Raro</option>
                                <option value="4">Muito Raro</option>
                                <option value="5">Nunca</option>
                            </x-select>

                            <x-select name="suporte_tecnico" label="Suporte Técnico">
                                <option value="">Selecione</option>
                                <option value="1">Muito Insatisfatório</option>
                                <option value="2">Insatisfatório</option>
                                <option value="3">Regular</option>
                                <option value="4">Bom</option>
                                <option value="5">Excelente</option>
                            </x-select>

                            <x-select name="pos_vendas" label="Pós-vendas">
                                <option value="">Selecione</option>
                                <option value="1">Muito Insatisfatório</option>
                                <option value="2">Insatisfatório</option>
                                <option value="3">Regular</option>
                                <option value="4">Bom</option>
                                <option value="5">Excelente</option>
                            </x-select>


                            <x-select name="condicao_comercial" label="Condição Comercial">
                                <option value="">Selecione</option>
                                <option value="1">Muito Ruim</option>
                                <option value="2">Ruim</option>
                                <option value="3">Regular</option>
                                <option value="4">Boa</option>
                                <option value="5">Excelente</option>
                            </x-select>

                            <x-select name="tempo_resposta" label="Tempo de Resposta">
                                <option value="">Selecione</option>
                                <option value="1">Muito Lento</option>
                                <option value="2">Lento</option>
                                <option value="3">Razoável</option>
                                <option value="4">Rápido</option>
                                <option value="5">Muito Rápido</option>
                            </x-select>
                            

                            <x-select name="pontualidade" label="Pontualidade">
                                <option value="">Selecione</option>
                                <option value="1">Muito Ruim</option>
                                <option value="2">Ruim</option>
                                <option value="3">Regular</option>
                                <option value="4">Boa</option>
                                <option value="5">Excelente</option>
                            </x-select>
                        </div>
                        <x-textarea name="historico_nao_conformidades" label="Histórico de Não Conformidades"
                            placeholder="Descreva as ocorrências registradas" rows="4" />
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Salvar classificação</x-button>
                        <x-button type="reset">Limpar</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
