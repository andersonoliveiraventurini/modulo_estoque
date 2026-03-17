<x-layouts.app :title="'Requisições de Compra'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Requisições de Compra</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Solicitações internas de materiais pendentes de aprovação.</p>
            </div>
            @can('criar requisicao')
            <a href="{{ route('requisicao_compras.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <x-heroicon-o-plus class="mr-2 h-5 w-5" />
                Nova Requisição
            </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            <table class="w-full text-left text-sm text-neutral-500 dark:text-neutral-400">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Solicitante</th>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3 text-center">Nível</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($requisicoes as $req)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="px-6 py-4 font-bold">#{{ $req->id }}</td>
                            <td class="px-6 py-4">{{ $req->solicitante->name }}</td>
                            <td class="px-6 py-4">{{ $req->data_requisicao->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $req->nivel_aprovacao == 1 ? 'Supervisor' : ($req->nivel_aprovacao == 2 ? 'Gerente' : 'Diretor') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold 
                                    {{ $req->status == 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $req->status == 'aprovada' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $req->status == 'rejeitada' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $req->status == 'convertida' ? 'bg-blue-100 text-blue-800' : '' }}
                                ">
                                    {{ $req->status == 'pendente' ? 'Aguardando ' . ($req->nivel_aprovacao == 1 ? 'Supervisor' : ($req->nivel_aprovacao == 2 ? 'Gerente' : 'Diretor')) : ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('requisicao_compras.show', $req->id) }}" class="text-indigo-600 hover:underline">Ver Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">Nenhuma requisição encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $requisicoes->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
