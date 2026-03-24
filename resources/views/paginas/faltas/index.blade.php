<x-layouts.app title="Faltas sem Pedido">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-queue-list class="w-5 h-5 text-indigo-500" />
                    Faltas sem Pedido
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('faltas.relatorio') }}" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-700 rounded-md text-sm font-medium hover:bg-gray-50 transition">
                        <x-heroicon-o-document-chart-bar class="w-4 h-4" />
                        Relatório
                    </a>
                    <a href="{{ route('faltas.create') }}" class="flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Nova Falta
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="mb-6 p-4 bg-gray-50 dark:bg-neutral-800 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <x-input name="cliente" label="Cliente" placeholder="Nome do cliente..." value="{{ request('cliente') }}" />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                        <select name="vendedor_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos vendedores</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(request('vendedor_id') == $v->id)>
                                    {{ $v->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-input name="data_inicio" label="De" type="date" value="{{ request('data_inicio') }}" />
                    <x-input name="data_fim" label="Até" type="date" value="{{ request('data_fim') }}" />

                    <div class="flex items-end">
                        <x-button type="submit" variant="primary" class="w-full text-sm">Filtrar</x-button>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Número</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Itens</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                        @forelse($faltas as $falta)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-bold bg-amber-100 text-amber-800 rounded-full dark:bg-amber-900 dark:text-amber-200">
                                    {{ $falta->numero_falta }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $falta->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ $falta->cliente?->nome ?? $falta->nome_cliente ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $falta->vendedor?->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $falta->itens->count() }}</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($falta->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('faltas.show', $falta) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500 italic">Nenhuma falta registrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($faltas->hasPages())
            <div class="mt-4">
                {{ $faltas->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
