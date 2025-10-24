<x-layouts.app :title="__('Editar NCM')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-primary-600" />
                    Editar NCM
                </h2>

                <form action="{{ route('ncm.update', $ncm->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        <x-input name="codigo" label="Código" value="{{ old('codigo', $ncm->codigo) }}" required />

                        <x-input name="numero" label="Número" value="{{ old('numero', $ncm->numero) }}" />

                        <x-input name="ano" label="Ano" value="{{ old('ano', $ncm->ano) }}" />

                        <x-input name="ato_legal" label="Ato Legal" value="{{ old('ato_legal', $ncm->ato_legal) }}" />

                        <x-input type="date" name="data_inicio" label="Data de Início"
                            value="{{ old('data_inicio', $ncm->data_inicio) }}" />

                        <x-input type="date" name="data_fim" label="Data de Fim"
                            value="{{ old('data_fim', $ncm->data_fim) }}" />
                    </div>
                    <x-textarea name="descricao" label="Descrição"
                        required>{{ old('descricao', $ncm->descricao) }}</x-textarea>

                    <div class="flex gap-4">
                        <x-button type="submit">Atualizar</x-button>
                        <a href="{{ route('ncm.index') }}">
                            <x-button type="button" variant="secondary">Cancelar</x-button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
