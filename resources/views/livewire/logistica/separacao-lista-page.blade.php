<x-layouts.app :title="__('Itens em Separação')">
    <div class="mx-auto max-w-7xl px-4 py-6 space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-4 shadow">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">
                Itens em Separação
            </h1>

            {{-- Filtros --}}
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Cliente</label>
                    <input type="text" wire:model.debounce.500ms="f_cliente"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="Nome/Razão" />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">SKU/Código/Produto</label>
                    <input type="text" wire:model.debounce.500ms="f_sku"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="SKU, código de barras ou nome" />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Status do Item</label>
                    <select wire:model="f_status_item"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="parcial">Parcial</option>
                        <option value="separado">Separado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Status do Lote</label>
                    <select wire:model="f_status_lote"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="em_separacao">Em Separação</option>
                        <option value="aberto">Aberto</option>
                    </select>
                </div>

                {{-- Se usar armazéns, descomente e popule as opções
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Armazém</label>
                    <select wire:model="f_armazem_id"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Armazem::orderBy('nome')->get() as $arm)
                            <option value="{{ $arm->id }}">{{ $arm->nome }}</option>
                        @endforeach
                    </select>
                </div>
                --}}

                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Busca geral</label>
                    <input type="text" wire:model.debounce.500ms="f_busca"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        placeholder="Produto, SKU ou #Orçamento" />
                </div>

                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400">Por página</label>
                    <select wire:model="perPage"
                        class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabela --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-0 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#Orçamento</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cliente</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Produto</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Solicitada</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Separada</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status Item</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Lote</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Iniciado</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estoque</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse($itens as $it)
                            @php
                                $orc = $it->batch?->orcamento;
                                $cli = $orc?->cliente;
                                $prod = $it->produto;
                                $reservado = (float) ($reservasPorProduto[$it->produto_id] ?? 0);
                                $estoqueAtual = (float) ($prod->estoque_atual ?? 0);
                                $disponivel = $estoqueAtual - $reservado;
                                $min = (float) ($prod->estoque_minimo ?? 0);
                                $risco = $disponivel - (float) $it->qty_solicitada < $min;
                            @endphp
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    #{{ $orc?->id ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ $cli?->nome ?? $cli?->nome_fantasia ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    <div class="font-medium">{{ $prod?->nome ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        SKU: {{ $prod?->sku ?? '—' }} • CB: {{ $prod?->codigo_barras ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right text-sm text-gray-800 dark:text-gray-200">
                                    {{ rtrim(rtrim(number_format($it->qty_solicitada,3,',','.'),'0'),',') }}
                                </td>
                                <td class="px-3 py-2 text-right text-sm text-gray-800 dark:text-gray-200">
                                    {{ rtrim(rtrim(number_format($it->qty_separada,3,',','.'),'0'),',') }}
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @php
                                        $map = [
                                            'pendente' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                            'parcial'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                            'separado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                            'cancelado'=> 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
                                        ];
                                        $cls = $map[$it->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $cls }}">
                                        {{ ucfirst($it->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    #{{ $it->picking_batch_id }} • {{ $it->batch?->status ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">
                                    {{ optional($it->batch?->started_at)->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-right text-sm">
                                    <div class="text-gray-800 dark:text-gray-200">
                                        <span class="text-xs">Atual:</span> {{ number_format($estoqueAtual,2,',','.') }}
                                    </div>
                                    <div class="text-gray-800 dark:text-gray-200">
                                        <span class="text-xs">Reservado:</span> {{ number_format($reservado,2,',','.') }}
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs
                                            {{ $risco ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' }}">
                                            Disp: {{ number_format($disponivel,2,',','.') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        @if($orc?->id)
                                            <a href="{{ route('orcamentos.separacao.show', $orc->id) }}"
                                               class="inline-flex items-center px-2 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-xs">
                                                Ir para Separação
                                            </a>
                                            <a href="{{ route('orcamentos.gerenciar', $orc->id) }}"
                                               class="inline-flex items-center px-2 py-1 rounded bg-gray-600 hover:bg-gray-700 text-white text-xs">
                                                Ver Orçamento
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-8 text-center text-sm text-gray-600 dark:text-gray-300">
                                    Nenhum item encontrado com os filtros atuais.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $itens->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>