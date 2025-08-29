<x-layouts.app :title="__('Bloquear cliente')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="user" class="w-5 h-5 text-primary-600" />
                    Bloquear Cliente
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha as informações do cliente para realizar o bloqueio.
                </p>

                <form action="{{ route('bloqueios.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="space-y-4">           
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-select name="cliente_id" label="Cliente">
                                <option value="">Selecione</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="bloqueado" label="Bloqueado">
                                <option value="">Selecione</option>
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </x-select>
                            <x-input name="motivo_bloqueio" label="Motivo do Bloqueio" />
                        </div>
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-textarea name="observacoes" label="Observações" placeholder="Informações adicionais..." />
                    </div>
                    
                   
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
