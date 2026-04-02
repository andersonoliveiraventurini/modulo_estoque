<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Orçamentos do Cliente
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar ..." />
            </div>

            <!-- Itens por página -->
            <div class="flex flex-col w-28">
                <label for="perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Itens por página:
                </label>
                <x-select id="perPage" wire:model.live="perPage">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-select>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('obra')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Obra
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Cliente
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button wire:click="sortBy('status')"
                            class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            Status
                        </button>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        PDF
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Valor Pago
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($orcamentos as $c)
                    @php $pagamento = $c->pagamentos->first(); @endphp
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">

                        {{-- Obra --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('orcamentos.show', $c) }}" class="hover:underline">
                                {{ $c->obra }}
                            </a>
                        </td>

                        {{-- Cliente --}}
                        <td class="px-6 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->cliente->nome }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @php
                                $statusDisplay = $c->status === 'Rejeitado' ? 'Reprovado' : $c->status;
                                $cor = match ($c->status) {
                                    'Pago', 'Concluído', 'Aprovado' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    'Pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'Cancelado', 'Rejeitado', 'Reprovado' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    'Sem estoque' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
                                    'Expirado' => 'bg-zinc-900 text-zinc-50 dark:bg-zinc-950 dark:text-zinc-400',
                                    default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cor }}">
                                {{ $statusDisplay }}
                            </span>
                        </td>

                        {{-- PDF --}}
                        <td class="px-6 py-4">
                            @if ($c->pdf_path)
                                <a href="{{ asset('storage/' . $c->pdf_path) }}" target="_blank">
                                    <x-button size="sm" variant="primary">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                        PDF
                                    </x-button>
                                </a>
                            @endif
                        </td>

                        {{-- Valor Pago + Formas --}}
                        <td class="px-6 py-4">
                            @if ($pagamento)
                                <a href="{{ route('pagamentos.show', $pagamento) }}"
                                    class="inline-flex items-center gap-1.5 font-semibold text-green-700 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 hover:underline transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}
                                </a>
                                @if ($pagamento->formas->isNotEmpty())
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($pagamento->formas as $forma)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                                {{ $forma->condicaoPagamento->nome ?? '—' }}
                                                <span class="ml-1 text-zinc-400 dark:text-zinc-500">
                                                    R$ {{ number_format($forma->valor, 2, ',', '.') }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Ações --}}
                        <td class="px-6 py-4 flex gap-2 flex-wrap">
                            @if ($c->status === 'Pendente' || $c->status === 'Aprovar desconto')
                                <a href="{{ route('orcamentos.edit', $c->id) }}">
                                    <x-button size="sm" variant="secondary">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        Editar
                                    </x-button>
                                </a>
                            @endif

                            <form action="{{ route('orcamentos.duplicar', $c->id) }}" method="POST"
                                onsubmit="return confirm('Deseja duplicar este orçamento?');">
                                @csrf
                                <x-button size="sm" variant="primary">
                                    <x-heroicon-o-document-duplicate class="w-4 h-4" />
                                    Duplicar
                                </x-button>
                            </form>

                            @if ($c->status !== 'Pago')
                                <form action="{{ route('orcamentos.destroy', $c->id) }}" method="POST"
                                    onsubmit="return confirm('Deseja excluir este orçamento?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                        Excluir
                                    </x-button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum orçamento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($orcamentos->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $orcamentos->links() }}
            </div>
        </div>
    @endif
</div>
