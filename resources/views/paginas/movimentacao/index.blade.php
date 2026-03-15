<x-layouts.app :title="__('Movimentações')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <x-heroicon-o-truck class="w-5 h-5" />
                        Histórico de Movimentações
                    </h2>
                    <a href="{{ route('movimentacao.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                        Nova Movimentação
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 text-green-700 bg-green-100 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 text-red-700 bg-red-100 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                            @foreach($movimentacoes as $mov)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">#{{ $mov->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-1">
                                            @if($mov->tipo === 'entrada')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 w-fit">Entrada</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 w-fit">Saída</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $mov->data_movimentacao ? $mov->data_movimentacao->format('d/m/Y') : $mov->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $statusClasses = [
                                                'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500',
                                                'aprovado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-500',
                                                'rejeitado' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-500',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded-md {{ $statusClasses[$mov->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ strtoupper($mov->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-xs text-indigo-600 dark:text-indigo-400 mb-1">
                                                {{ $mov->itens->count() }} @choice('item|itens', $mov->itens->count())
                                            </span>
                                            <div class="flex flex-col gap-1 max-h-20 overflow-y-auto">
                                                @foreach($mov->itens->take(3) as $item)
                                                    <span class="text-[10px] leading-tight text-gray-600 dark:text-gray-400 truncate max-w-[200px]" title="{{ $item->produto->nome ?? '-' }}">
                                                        • {{ $item->produto->nome ?? '-' }}
                                                    </span>
                                                @endforeach
                                                @if($mov->itens->count() > 3)
                                                    <span class="text-[9px] text-gray-400 italic">...e mais {{ $mov->itens->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col gap-1">
                                            @if($mov->usuario)
                                                <span class="text-xs" title="Criado por">
                                                    <x-heroicon-s-user class="w-3 h-3 inline-block" /> {{ $mov->usuario->name }}
                                                </span>
                                            @endif
                                            
                                            @if($mov->supervisor)
                                                <span class="text-emerald-600 text-xs mt-1" title="Aprovado por">
                                                    <x-heroicon-s-check-badge class="w-3 h-3 inline-block" /> {{ $mov->supervisor->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center gap-3">
                                            @if($mov->status === 'pendente')
                                                <form action="{{ route('movimentacao.aprovar', $mov->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 font-bold" title="Aprovar">
                                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                                    </button>
                                                </form>
                                                <form action="{{ route('movimentacao.rejeitar', $mov->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-rose-600 hover:text-rose-900 dark:text-rose-400" title="Rejeitar">
                                                        <x-heroicon-o-x-circle class="w-5 h-5" />
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('movimentacao.show', $mov->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400" title="Ver Detalhes">
                                                <x-heroicon-o-eye class="w-5 h-5" />
                                            </a>

                                            @if($mov->status === 'pendente')
                                                <a href="{{ route('movimentacao.edit', $mov->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" title="Editar">
                                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                                </a>
                                            @endif

                                            <button type="button" onclick="confirmDelete({{ $mov->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400" title="Excluir">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </div>
                                        
                                        <form id="delete-form-{{ $mov->id }}" action="{{ route('movimentacao.destroy', $mov->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($movimentacoes->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">Nenhuma movimentação encontrada.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('Atenção: Tem certeza que deseja excluir esta movimentação?\nO estoque dos produtos será estornado e recalculado.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
</x-layouts.app>
