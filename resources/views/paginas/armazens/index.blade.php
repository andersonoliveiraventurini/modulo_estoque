<x-layouts.app title="Armazéns">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-building-office class="w-6 h-6 text-indigo-500" />
                    Listagem de Armazéns
                </h2>
                <a href="{{ route('armazens.create') }}">
                    <x-button icon="plus">Novo Armazém</x-button>
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">ID</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Nome</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Localização</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Descrição</th>
                            <th class="py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($armazens as $armazem)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">#{{ $armazem->id }}</td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $armazem->nome }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $armazem->localizacao ?: '---' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $armazem->descricao ? Str::limit($armazem->descricao, 40) : '---' }}</td>
                                <td class="py-3 px-4 text-right flex justify-end gap-2">
                                    <a href="{{ route('armazens.show', $armazem->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition" title="Ver Detalhes">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </a>
                                    <a href="{{ route('armazens.edit', $armazem->id) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 rounded-lg transition" title="Editar">
                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    </a>
                                    <form action="{{ route('armazens.destroy', $armazem->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este armazém?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition" title="Excluir">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Nenhum armazém cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
