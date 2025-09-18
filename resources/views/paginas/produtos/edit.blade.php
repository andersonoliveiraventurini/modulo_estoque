<x-layouts.app :title="__('Editar Produto')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-primary-600" />
                    Editar Produto
                </h2>

                <form action="{{ route('produtos.update', $produto) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Dados Básicos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        <x-input name="nome" label="Nome do Produto *" value="{{ old('nome', $produto->nome) }}" required class="col-span-2"/>
                        <x-input name="codigo_barras" label="Código de Barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}" />
                        <x-input name="sku" label="SKU" value="{{ old('sku', $produto->sku) }}" />
                        <!-- ... demais campos iguais ao create ... -->
                    </div>

                    <hr class="my-6" />

                    <!-- Imagens -->
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <x-heroicon-o-photo class="w-5 h-5 text-primary-600" />
                        Imagens do Produto
                    </h3>

                    <!-- Galeria -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($produto->images as $img)
                            <div class="relative border rounded-lg p-2 flex flex-col items-center" >
                                <img src="{{ asset('storage/' . $img->caminho) }}" 
                                     class="w-32 h-32 object-cover rounded shadow" 

                                <div class="mt-2 text-sm">
                                    @if($img->principal)
                                        <span class="text-green-600 font-semibold">Principal</span>
                                    @else
                                        <button type="submit" 
                                            formaction="{{ route('produtos.imagens.principal', [$produto, $img]) }}" 
                                            formmethod="POST"
                                            class="text-blue-600 underline text-xs">
                                            Definir como Principal
                                        </button>
                                        @csrf
                                        @method('PATCH')
                                    @endif
                                </div>

                                <form action="{{ route('produtos.imagens.destroy', [$produto, $img]) }}" method="POST" class="absolute top-2 right-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-bold">✕</button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <!-- Adicionar novas imagens -->
                    <div class="mt-6">
                        <label class="text-sm font-medium">Adicionar Novas Imagens</label>
                        <input type="file" name="images[]" multiple
                               accept="image/png,image/jpeg,image/gif"
                               class="mt-2 block w-full text-sm text-gray-600 border rounded-lg p-2">
                    </div>

                    <hr class="my-6" />

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit">Salvar Alterações</x-button>
                        <x-button href="{{ route('produtos.index') }}">Voltar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
