<x-layouts.app :title="__('Cadastrar cor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Cadastro de NCM
                </h2>
                <form action="{{ route('ncm.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        <x-input name="codigo" label="Código" value="{{ old('codigo') }}" required />
                        <x-input name="numero" label="Número" value="{{ old('numero') }}" />
                        <x-input name="ano" label="Ano" value="{{ old('ano') }}" />
                        <x-input name="ato_legal" label="Ato Legal" value="{{ old('ato_legal') }}" />
                        <x-input type="date" name="data_inicio" label="Data de Início"
                            value="{{ old('data_inicio') }}" />
                        <x-input type="date" name="data_fim" label="Data de Fim" value="{{ old('data_fim') }}" />
                    </div>
                    <x-textarea name="descricao" label="Descrição" required>{{ old('descricao') }}</x-textarea>

                    <div class="flex gap-4">
                        <x-button type="submit">Cadastrar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
