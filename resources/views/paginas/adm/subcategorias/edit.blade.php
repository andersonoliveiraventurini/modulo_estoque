<x-layouts.app :title="__('Editar subcategoria')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-primary-600" />
                    Editar Subcategoria
                </h2>
                 <form action="{{ route('subcategorias.update', $subcategoria->id) }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $subcategoria->id }}">

                    <x-input name="nome" label="Nome" value="{{ old('nome', $subcategoria->nome) }}" required />
                    
                    <x-select name="categoria_id" label="Categoria" required>
                        <option value="">Selecione uma categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" 
                                {{ old('categoria_id', $subcategoria->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-textarea name="descricao" label="Descrição" >{{ old('descricao', $subcategoria->descricao) }}</x-textarea>

                    <div class="flex gap-4">
                        <x-button type="submit">Salvar Alterações</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
