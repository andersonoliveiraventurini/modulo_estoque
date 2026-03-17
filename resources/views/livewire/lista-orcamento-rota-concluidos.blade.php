<div class="flex flex-col gap-4">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Pedidos de Rota — Conferidos / Finalizados
        </h2>
        <div class="flex items-end gap-4">
            <div class="flex flex-col flex-[2]">
                <label for="rota-search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pesquisar</label>
                <x-input id="rota-search" wire:model.live.debounce.300ms="search" placeholder="Buscar por obra, cliente..." />
            </div>
            <div class="flex flex-col w-28">
                <label for="rota-perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Por página</label>
                <x-select id="rota-perPage" wire:model.live="perPage">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-select>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-end gap-4 px-6 py-4">
        <div class="flex flex-col flex-[2] min-w-[170px]">
            <label for="rota-cliente" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cliente</label>
            <x-input id="rota-cliente" wire:model.live.debounce.300ms="cliente" placeholder="Nome do cliente..." />
        </div>
        <div class="flex flex-col flex-[2] min-w-[170px]">
            <label for="rota-vendedor" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vendedor</label>
            <x-input id="rota-vendedor" wire:model.live.debounce.300ms="vendedor" placeholder="Nome do vendedor..." />
        </div>
        <div class="flex flex-col flex-[2] min-w-[160px]">
            <label for="rota-loadingDay" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Dia de Carregamento</label>
            <x-select id="rota-loadingDay" wire:model.live="loadingDay">
                <option value="">Todos</option>
                <option value="monday">Segunda-feira</option>
                <option value="tuesday">Terça-feira</option>
                <option value="wednesday">Quarta-feira</option>
                <option value="thursday">Quinta-feira</option>
                <option value="friday">Sexta-feira</option>
                <option value="express">Express</option>
                <option value="sedex">Sedex</option>
                <option value="carrier">Transportadora</option>
            </x-select>
        </div>
        <div class="flex flex-col flex-[2] min-w-[160px]">
            <label for="rota-billingStatus" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status Financeiro</label>
            <x-select id="rota-billingStatus" wire:model.live="billingStatus">
                <option value="">Todos</option>
                <option value="pending">Pendente</option>
                <option value="approved">Aprovado</option>
                <option value="restrictions">Aprovado c/ Restrição</option>
                <option value="rejected">Negado</option>
            </x-select>
        </div>
        <div class="flex flex-col w-40">
            <label for="rota-dataInicio" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data início</label>
            <x-input type="date" id="rota-dataInicio" wire:model.live="dataInicio" />
        </div>
        <div class="flex flex-col w-40">
            <label for="rota-dataFim" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data fim</label>
            <x-input type="date" id="rota-dataFim" wire:model.live="dataFim" />
        </div>
        <div class="flex flex-col">
            <label class="text-sm text-transparent select-none mb-1">.</label>
            <x-button wire:click="limparFiltros" variant="secondary">
                <x-heroicon-o-x-mark class="w-4 h-4" />
                Limpar
            </x-button>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('id')" class="flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                            ID
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Obra / Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Día Carreg.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pagamento</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Aprovação Fin.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Comprovantes</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vendedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($orcamentos as $o)
                    @php
                        $ultimaAprovacao = $o->routeBillingApprovals->first();
                        $anexosPendentes = $o->routeBillingAttachments->whereNull('is_valid')->count();
                        $statusAprovBg = match($ultimaAprovacao?->status) {
                            'approved'     => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'restrictions' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                            'rejected'     => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            default        => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
                        };
                        $statusAprovLabel = match($ultimaAprovacao?->status) {
                            'approved'     => 'Aprovado',
                            'restrictions' => 'Restrição',
                            'rejected'     => 'Negado',
                            default        => 'Pendente',
                        };
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                        {{-- ID --}}
                        <td class="px-4 py-4 text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('orcamentos.show', $o) }}" class="font-semibold hover:underline text-indigo-600 dark:text-indigo-400">#{{ $o->id }}</a>
                        </td>

                        {{-- Obra / Cliente --}}
                        <td class="px-4 py-4">
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $o->obra }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $o->cliente->nome ?? '—' }}</p>
                        </td>

                        {{-- Dia de Carregamento --}}
                        <td class="px-4 py-4">
                            @if($o->loading_day)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    {{ $o->loading_day_formatted }}
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </td>

                        {{-- Pagamento --}}
                        <td class="px-4 py-4">
                            @if($o->pagamento)
                                <a href="{{ route('pagamentos.show', $o->pagamento->id) }}"
                                   class="inline-flex items-center gap-1.5 font-semibold text-green-700 dark:text-green-400 hover:underline text-sm">
                                    <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
                                    R$ {{ number_format($o->pagamento->valor_pago, 2, ',', '.') }}
                                </a>
                                @if($o->pagamento->formas->isNotEmpty())
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($o->pagamento->formas as $forma)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                                {{ $forma->condicaoPagamento->nome ?? '—' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-zinc-400">Sem pagamento</span>
                            @endif
                        </td>

                        {{-- Aprovação Financeiro --}}
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusAprovBg }}">
                                {{ $statusAprovLabel }}
                            </span>
                        </td>

                        {{-- Comprovantes --}}
                        <td class="px-4 py-4">
                            @if($o->routeBillingAttachments->count())
                                <span class="inline-flex items-center gap-1 text-xs {{ $anexosPendentes > 0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-green-700 dark:text-green-400' }}">
                                    <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                                    {{ $o->routeBillingAttachments->count() }} anexo(s)
                                    @if($anexosPendentes > 0)
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30">{{ $anexosPendentes }} pendente(s)</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">Sem comprovantes</span>
                            @endif
                        </td>

                        {{-- Vendedor --}}
                        <td class="px-4 py-4 text-zinc-700 dark:text-zinc-300 text-sm">{{ $o->vendedor->name ?? '—' }}</td>

                        {{-- Ações --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                @can('viewBilling', App\Models\Orcamento::class)
                                    <a href="{{ route('orcamentos.rota_pagamento', $o) }}">
                                        <x-button size="sm" variant="primary">
                                            <x-heroicon-o-banknotes class="w-4 h-4" />
                                            Faturar
                                        </x-button>
                                    </a>
                                @endcan
                                <a href="{{ route('orcamentos.show', $o) }}">
                                    <x-button size="sm" variant="secondary">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                        Ver
                                    </x-button>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-40" />
                            Nenhum pedido de rota encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($orcamentos->hasPages())
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700">
            {{ $orcamentos->links() }}
        </div>
    @endif
</div>
