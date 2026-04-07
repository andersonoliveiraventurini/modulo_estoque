<x-layouts.app :title="'Histórico - Estoque Mínimo'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Histórico de Relatórios - Estoque Mínimo</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Rastreabilidade das gerações de relatórios.</p>
            </div>
            <a href="{{ route('relatorios.estoque_minimo') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <x-heroicon-o-arrow-left class="mr-2 h-5 w-5" />
                Voltar para o Relatório
            </a>
        </div>

        <!-- Filtros -->
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <form action="{{ route('relatorios.estoque_minimo.historico') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-5 items-end">
                <div>
                    <label for="inicio" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Inicial</label>
                    <input type="date" name="inicio" id="inicio" value="{{ request('inicio') }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
                <div>
                    <label for="fim" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data Final</label>
                    <input type="date" name="fim" id="fim" value="{{ request('fim') }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Usuário</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Todos</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                        <option value="erro" {{ request('status') == 'erro' ? 'selected' : '' }}>Erro</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <x-heroicon-o-magnifying-glass class="mr-2 h-5 w-5" />
                        Filtrar
                    </button>
                    <a href="{{ route('relatorios.estoque_minimo.historico') }}" class="inline-flex items-center rounded-lg border border-neutral-300 bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm hover:bg-neutral-50 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">ID Relatório</th>
                        <th class="px-6 py-3">Data/Hora</th>
                        <th class="px-6 py-3">Usuário</th>
                        <th class="px-6 py-3">Parâmetros</th>
                        <th class="px-6 py-3 text-center">Itens</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($solicitacoes as $item)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ $item->codigo }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->user->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li><strong>Período:</strong> {{ \Carbon\Carbon::parse($item->parametros['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->parametros['fim'])->format('d/m/Y') }}</li>
                                    <li><strong>Apenas abaixo:</strong> {{ $item->parametros['apenas_abaixo'] ? 'Sim' : 'Não' }}</li>
                                    <li><strong>Sem giro:</strong> {{ $item->parametros['incluir_sem_giro'] ? 'Sim' : 'Não' }}</li>
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center font-medium">
                                {{ $item->total_itens }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->status == 'concluido')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Concluído
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        Erro
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                {{ $solicitacoes->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
