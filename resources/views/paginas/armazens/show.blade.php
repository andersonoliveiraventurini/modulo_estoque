<x-layouts.app title="Detalhes do Armazém">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Header do Armazém -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-building-office class="w-6 h-6 text-indigo-500" />
                        Armazém: {{ $armazem->nome }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <x-heroicon-o-map-pin class="w-4 h-4 inline-block mr-1 text-gray-400" />
                        {{ $armazem->localizacao ?: 'Localização não informada' }}
                    </p>
                    @if($armazem->descricao)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 italic">
                        {{ $armazem->descricao }}
                    </p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('armazens.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-zinc-700 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-left class="w-4 h-4" /> Voltar
                    </a>
                    <a href="{{ route('armazens.edit', $armazem->id) }}">
                        <x-button icon="pencil-square" color="indigo">Editar Armazém</x-button>
                    </a>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-zinc-800 my-6">

            <!-- Relacionamento: Corredores -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <x-heroicon-s-bars-4 class="w-5 h-5 text-gray-500" />
                    Corredores neste Armazém
                </h3>
                <a href="{{ route('corredores.create', ['armazem_id' => $armazem->id]) }}" class="px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-medium hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 dark:hover:bg-green-900/50 transition flex items-center gap-1">
                    <x-heroicon-o-plus class="w-4 h-4" /> Novo Corredor
                </a>
            </div>

            @php
                // Carrega corredores do armazém e já traz as posições associadas
                $corredores = App\Models\Corredor::withCount('posicoes')->where('armazem_id', $armazem->id)->get();
            @endphp

            @if($corredores->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($corredores as $corredor)
                        <div class="border border-gray-200 dark:border-zinc-700 rounded-xl p-4 hover:border-indigo-300 dark:hover:border-indigo-600 transition bg-gray-50 dark:bg-zinc-800/50 relative group">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-indigo-700 dark:text-indigo-400 text-lg flex items-center gap-1.5">
                                    <x-heroicon-o-bars-3-bottom-left class="w-5 h-5" />
                                    {{ $corredor->nome }}
                                </h4>
                                <div class="flex gap-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition">
                                    <a href="{{ route('corredores.show', $corredor->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-md transition" title="Ver Corredor">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                            
                            @if($corredor->descricao)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 truncate" title="{{ $corredor->descricao }}">
                                {{ $corredor->descricao }}
                            </p>
                            @endif

                            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ $corredor->posicoes_count }} posição(ões)
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border-2 border-dashed border-gray-300 dark:border-zinc-700 rounded-xl p-8 text-center bg-gray-50 dark:bg-zinc-800/50">
                    <x-heroicon-o-bars-4 class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Nenhum corredor cadastrado</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece criando corredores para organizar este armazém.</p>
                    <div class="mt-4">
                        <a href="{{ route('corredores.create', ['armazem_id' => $armazem->id]) }}" class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <x-heroicon-o-plus class="-ml-0.5 h-4 w-4" aria-hidden="true" />
                            Adicionar Corredor
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
