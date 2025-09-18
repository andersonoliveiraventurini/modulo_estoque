<x-layouts.app :title="__('Cadastrar subcategoria')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Cadastro de Subcategoria
                </h2>
                 <form action="{{ route('subcategorias.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <x-input name="nome" label="Nome" value="{{ old('nome') }}" required />
                    <x-select name="categoria_id" label="Categoria" required>
                        <option value="">Selecione uma categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-textarea name="descricao" label="Descrição" value="{{ old('descricao') }}"  />

                    <div class="flex gap-4">
                        <x-button type="submit">Cadastrar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
