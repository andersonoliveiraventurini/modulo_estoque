<x-layouts.app :title="__('Bloquear cliente')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->

                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                            <x-icon name="user" class="w-5 h-5 text-primary-600" />
                            Bloquear - {{ $cliente->nome }}
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                            Preencha as informações do cliente para realizar o bloqueio.
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

                <form action="{{ route('bloqueios.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-select name="bloqueado" label="Bloquear">
                                <option value="">Selecione</option>
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </x-select>
                            <x-input name="motivo_bloqueio" label="Motivo do Bloqueio" />
                        </div>
                    </div><br />
                    <x-textarea name="observacoes" label="Observações" placeholder="Informações adicionais..." />
                    <!-- Observações -->
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Bloquear Cliente</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
