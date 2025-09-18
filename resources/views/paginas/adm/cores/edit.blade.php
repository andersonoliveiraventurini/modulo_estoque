<x-layouts.app :title="__('Editar cor')"> 
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-primary-600" />
                    Editar cor
                </h2>
                 <form action="{{ route('cores.update', $cor->id) }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <x-input name="nome" label="Nome" value="{{ old('nome', $cor->nome) }}" required />
                    <x-input name="codigo_hex" label="Código Hexadecimal" value="{{ old('codigo_hex', $cor->codigo_hex) }}"  />

                    <div class="flex gap-4">
                        <x-button type="submit">Salvar Alterações</x-button>
                        <a href="{{ route('cores.index') }}" class="text-gray-600 hover:underline">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
