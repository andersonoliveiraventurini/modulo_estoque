<x-layouts.app :title="__('Visualizar NCM')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->

                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                            Informações do NCM
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Aqui estão as informações completas do NCM selecionado.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                       
                    </div>
                </div>

                <!-- Abas -->
                <x-tabs default="basico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Código" :value="$ncm->codigo" />
                            <x-show-field label="Data de Início" :value="$ncm->data_inicio_formatada" />
                            <x-show-field label="Data de Fim" :value="$ncm->data_fim_formatada" />
                            <x-show-field label="Ato legal" :value="$ncm->ato_legal" />
                            <x-show-field label="Número" :value="$ncm->numero" />
                            <x-show-field label="Ano" :value="$ncm->ano" />
                            <x-show-field label="Descrição" :value="$ncm->descricao" />
                        </div>
                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('ncm.edit', $ncm) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                    <a href="{{ route('ncm.index') }}">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                            Voltar para a lista
                        </x-button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
