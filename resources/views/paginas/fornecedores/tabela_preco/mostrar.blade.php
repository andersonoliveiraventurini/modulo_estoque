<x-layouts.app :title="__('Tabela de preços do Fornecedor')" :breadcrumbs="['fornecedores.tabela_preco', $fornecedor]">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">   
           <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-star class="w-5 h-5 text-primary-600" />
                            Tabela de preços do Fornecedor - {{ $fornecedor->nome_fantasia }}
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Produtos relacionados ao fornecedor.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        <a href="{{ route('fornecedores.edit', $fornecedor->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Editar Fornecedor
                        </a>
                        <a href="{{ route('fornecedores.show', $fornecedor->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Mostrar detalhes do fornecedor
                        </a>
                    </div>
                </div>
              </div>          
            <livewire:lista-fornecedor-tabela-preco :fornecedor-id="$fornecedor->id" />
        </div>
    </div>
</x-layouts.app>
