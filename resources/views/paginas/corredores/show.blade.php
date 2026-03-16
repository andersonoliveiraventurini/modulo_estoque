<x-layouts.app title="Detalhes do Corredor">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Header do Corredor -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-bars-3-bottom-left class="w-6 h-6 text-indigo-500" />
                        Corredor: {{ $corredor->nome }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                        <x-heroicon-o-building-office class="w-4 h-4 text-gray-400" />
                        Armazém: <a href="{{ route('armazens.show', $corredor->armazem_id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ optional($corredor->armazem)->nome }}</a>
                    </p>
                    @if($corredor->descricao)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 italic">
                        {{ $corredor->descricao }}
                    </p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('armazens.show', $corredor->armazem_id) }}" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-zinc-700 transition flex items-center gap-2">
                        <x-heroicon-o-arrow-left class="w-4 h-4" /> Voltar ao Armazém
                    </a>
                    <a href="{{ route('corredores.edit', $corredor->id) }}">
                        <x-button icon="pencil-square" color="indigo">Editar Corredor</x-button>
                    </a>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-zinc-800 my-6">

            <!-- Relacionamento: Posições -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <x-heroicon-s-bars-4 class="w-5 h-5 text-gray-500" />
                    Posições neste Corredor
                </h3>
                <a href="{{ route('posicoes.create', ['corredor_id' => $corredor->id]) }}" class="px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-medium hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 dark:hover:bg-green-900/50 transition flex items-center gap-1">
                    <x-heroicon-o-plus class="w-4 h-4" /> Nova Posição
                </a>
            </div>

            @php
                // Carrega posições do corredor
                $posicoes = \App\Models\Posicao::where('corredor_id', $corredor->id)->get();
            @endphp

            @if($posicoes->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($posicoes as $posicao)
                        <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 hover:border-indigo-300 dark:hover:border-indigo-600 transition bg-white dark:bg-zinc-800 relative group flex flex-col justify-center">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-800 dark:text-gray-200 text-md flex items-center gap-1.5">
                                    <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                                    {{ $posicao->nome }}
                                </h4>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition absolute right-2 top-2 bg-white dark:bg-zinc-800 rounded pl-1">
                                    <a href="{{ route('posicoes.edit', $posicao->id) }}" class="p-1 text-indigo-600 hover:bg-indigo-100 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded transition" title="Editar Posição">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                            @if($posicao->descricao)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 truncate" title="{{ $posicao->descricao }}">
                                {{ $posicao->descricao }}
                            </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border-2 border-dashed border-gray-300 dark:border-zinc-700 rounded-xl p-8 text-center bg-gray-50 dark:bg-zinc-800/50">
                    <x-heroicon-o-cube class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Nenhuma posição cadastrada</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece criando posições para organizar este corredor.</p>
                    <div class="mt-4">
                        <a href="{{ route('posicoes.create', ['corredor_id' => $corredor->id]) }}" class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <x-heroicon-o-plus class="-ml-0.5 h-4 w-4" aria-hidden="true" />
                            Adicionar Posição
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
