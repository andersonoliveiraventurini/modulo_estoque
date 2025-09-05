<x-layouts.app :title="__('Análise cliente')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="user" class="w-5 h-5 text-primary-600" />
                    Análise de {{ $cliente[0]->nome }}
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha as informações do cliente para realizar a análise.
                </p>

                <form action="{{ route('analise_creditos.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- Informações de Crédito -->
                    <div class="space-y-4">
                        <!-- Documentação -->
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input name="desconto" label="Desconto (%)" type="number" step="0.01" /> <x-input
                                    type="number" step="0.01" name="limite_boleto" label="Limite Boleto (R$)" />
                                <x-input type="number" step="0.01" name="limite_carteira"
                                    label="Limite Carteira (R$)" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input type="date" name="data_ultima_analise" label="Data da Última Análise" />
                            <x-input type="date" name="data_vencimento_analise" label="Data Vencimento da Análise" />
                        </div>
                        <x-textarea name="historico_credito" label="Histórico de Análise de Crédito" />
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Salvar análise</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
