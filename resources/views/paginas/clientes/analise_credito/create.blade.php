<x-layouts.app :title="__('Análise cliente')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">


                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                            <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                            Análise de {{ $cliente->nome }}
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                            Preencha as informações do cliente para realizar a análise.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        <a href="{{ route('clientes.show', $cliente->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Mostrar informações do cliente
                        </a>
                        <a href="{{ route('bloqueios.mostrar', $cliente->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Bloqueios do cliente
                        </a>
                        <a href="{{ route('analise_creditos.mostrar', $cliente->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Análises de crédito
                        </a>
                    </div>
                </div>
                <form action="{{ route('analise_creditos.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <!-- Informações de Crédito -->
                    <div class="space-y-4">
                        <!-- Documentação -->
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input name="desconto" label="Desconto (%)" type="number" step="0.01" /> <x-input
                                    type="number" step="0.01" name="limite_boleto" label="Limite Boleto (R$)" />
                                <x-input type="number" step="0.01" name="limite_carteira"
                                    label="Limite Carteira (R$)" />
                                <x-input type="date" name="data_ultima_analise" label="Data da Última Análise" />
                                <x-input type="date" name="data_vencimento_analise"
                                    label="Data Vencimento da Análise" />
                            </div>
                        </div>

                        <x-textarea name="historico_credito" label="Histórico de Análise de Crédito" />
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Salvar análise</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
