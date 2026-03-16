<x-layouts.app title="Novo Armazém">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 max-w-2xl">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-6 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-building-office class="w-6 h-6 text-indigo-500" />
                    Novo Armazém
                </h2>

                <form action="{{ route('armazens.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <x-input name="nome" label="Nome do Armazém *" placeholder="Ex: Galpão A, Principal, Frio" value="{{ old('nome') }}" />
                        @error('nome') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-input name="localizacao" label="Localização / Endereço" placeholder="Ex: Rua X, Setor Y" value="{{ old('localizacao') }}" />
                        @error('localizacao') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-input name="descricao" label="Descrição" placeholder="Detalhes adicionais do armazém" value="{{ old('descricao') }}" />
                        @error('descricao') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <x-button type="submit">Salvar Armazém</x-button>
                        <a href="{{ route('armazens.index') }}" class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
