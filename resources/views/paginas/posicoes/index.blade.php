<x-layouts.app title="Posições">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-5 h-5 text-indigo-500" /> 
                        Posições (Prateleiras / Boxes)
                    </h2>
                    <a href="{{ route('posicoes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">
                        Nova Posição
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 text-green-600 bg-green-100 p-4 rounded text-sm">{{ session('success') }}</div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Armazém</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Corredor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posição</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                            @forelse ($posicoes as $posicao)
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $posicao->id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ optional(optional($posicao->corredor)->armazem)->nome }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ optional($posicao->corredor)->nome }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-bold">{{ $posicao->nome }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $posicao->descricao ? Str::limit($posicao->descricao, 30) : '---' }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('posicoes.edit', $posicao->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Editar</a>
                                        <form action="{{ route('posicoes.destroy', $posicao->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza? Isso pode afetar estoques.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Deletar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Nenhuma posição cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
