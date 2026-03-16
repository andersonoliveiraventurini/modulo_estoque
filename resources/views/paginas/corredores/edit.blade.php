<x-layouts.app title="Editar Corredor">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 max-w-2xl">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-6 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-indigo-500" /> 
                    Editar Corredor #{{ $corredor->id }}
                </h2>

                <form action="{{ route('corredores.update', $corredor->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-select name="armazem_id" label="Armazém">
                            <option value="">Selecione o Armazém</option>
                            @foreach($armazens as $armazem)
                                <option value="{{ $armazem->id }}" {{ old('armazem_id', $corredor->armazem_id) == $armazem->id ? 'selected' : '' }}>{{ $armazem->nome }}</option>
                            @endforeach
                        </x-select>
                        @error('armazem_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-input name="nome" label="Nome / Identificação do Corredor" placeholder="Ex: A, 01, Corredor Frio" value="{{ old('nome', $corredor->nome) }}" />
                        @error('nome') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-input name="descricao" label="Descrição" placeholder="Detalhes adicionais do corredor" value="{{ old('descricao', $corredor->descricao) }}" />
                        @error('descricao') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <x-button type="submit">Atualizar</x-button>
                        <a href="{{ route('corredores.index') }}" class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">Cancelar</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-layouts.app>
