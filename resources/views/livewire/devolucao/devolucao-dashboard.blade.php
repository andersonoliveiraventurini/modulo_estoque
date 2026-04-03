<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Gestão de Devolução</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Acompanhamento de Devoluções e Não Conformidades (RNC).</p>
        </div>
        <div class="flex items-center gap-3">
            @can('create', App\Models\NonConformity::class)
            <flux:button href="{{ route('rnc.create') }}" variant="ghost" icon="plus">Nova RNC</flux:button>
            @endcan
            @can('create', App\Models\ProductReturn::class)
            <flux:button href="{{ route('product_returns.create') }}" variant="primary" icon="arrow-path">Solicitar Devolução</flux:button>
            @endcan
        </div>
    </div>

    <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por Nr, Cliente ou Produto..." icon="magnifying-glass" />
        </div>
        <div class="w-full md:w-48">
            <flux:select wire:model.live="status_filter" placeholder="Status">
                <flux:select.option value="">Todos os Status</flux:select.option>
                <flux:select.option value="pendente_supervisor">Pendente Supervisor</flux:select.option>
                <flux:select.option value="pendente_estoque">Pendente Estoque</flux:select.option>
                <flux:select.option value="finalizado">Finalizado</flux:select.option>
                <flux:select.option value="negado">Negado</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="mb-6 border-b border-zinc-200 dark:border-zinc-800">
        <nav class="-mb-px flex space-x-8">
            <button 
                wire:click="$set('tab', 'returns')" 
                class="{{ $tab === 'returns' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all"
            >
                <flux:icon icon="arrow-path" class="w-4 h-4" />
                Devoluções de Vendas
            </button>
            <button 
                wire:click="$set('tab', 'rnc')" 
                class="{{ $tab === 'rnc' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-all"
            >
                <flux:icon icon="exclamation-triangle" class="w-4 h-4" />
                RNC (Não Conformidades)
            </button>
        </nav>
    </div>

    <div class="mt-6">
        @if ($tab === 'returns')
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nr</flux:table.column>
                        <flux:table.column>Cliente / Orçamento</flux:table.column>
                        <flux:table.column>Vendedor</flux:table.column>
                        <flux:table.column>Data</flux:table.column>
                        <flux:table.column>Valor Crédito</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="right">Ações</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($returns as $ret)
                            <flux:table.row>
                                <flux:table.cell class="font-bold">#{{ $ret->nr }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $ret->cliente->nome }}</div>
                                    <div class="text-xs text-zinc-500">Orçamento: #{{ $ret->orcamento_id }}</div>
                                </flux:table.cell>
                                <flux:table.cell>{{ $ret->vendedor->name }}</flux:cell>
                                <flux:table.cell>{{ $ret->data_ocorrencia->format('d/m/Y') }}</flux:cell>
                                <flux:table.cell class="font-medium">R$ {{ number_format($ret->valor_total_credito, 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        @if($ret->status === 'pendente_supervisor') bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400
                                        @elseif($ret->status === 'pendente_estoque') bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400
                                        @elseif($ret->status === 'finalizado') bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400
                                        @else bg-rose-100 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 @endif">
                                        {{ $ret->status_label }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell align="right">
                                    <div class="flex justify-end gap-2">
                                        @if(($ret->status === 'pendente_supervisor' && auth()->user()->hasAnyRole(['supervisor', 'admin'])) || 
                                            ($ret->status === 'pendente_estoque' && auth()->user()->hasAnyRole(['estoquista', 'admin'])))
                                            <flux:button href="{{ route('product_returns.approve', $ret->id) }}" variant="primary" size="sm" icon="shield-check">Aprovar</flux:button>
                                        @else
                                            <flux:button href="{{ route('product_returns.approve', $ret->id) }}" variant="ghost" size="sm" icon="eye">Ver Detalhes</flux:button>
                                        @endif
                                        
                                        @if ($ret->status === 'finalizado')
                                            <flux:button variant="ghost" size="sm" icon="document-text" title="Ver Autorização"></flux:button>
                                        @else
                                            <flux:button variant="ghost" size="sm" icon="printer" title="Imprimir Solicitação"></flux:button>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7" class="py-10 text-center text-zinc-500">Nenhuma devolução encontrada.</flux:cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $returns->links() }}
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nr</flux:table.column>
                        <flux:table.column>Produto / Fornecedor</flux:table.column>
                        <flux:table.column>NF / ROM</flux:table.column>
                        <flux:table.column>Data Ocorrência</flux:table.column>
                        <flux:table.column>Registrado por</flux:table.column>
                        <flux:table.column align="right">Ações</flux:column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($rncs as $rnc)
                            <flux:table.row>
                                <flux:table.cell class="font-bold">#{{ $rnc->nr }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $rnc->produto_nome }}</div>
                                    <div class="text-xs text-zinc-500">{{ $rnc->fornecedor_nome }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="text-xs">NF: {{ $rnc->nota_fiscal ?? '-' }}</div>
                                    <div class="text-xs">ROM: {{ $rnc->romaneio_recebimento ?? '-' }}</div>
                                </flux:table.cell>
                                <flux:table.cell>{{ $rnc->data_ocorrencia->format('d/m/Y') }}</flux:cell>
                                <flux:table.cell>{{ $rnc->usuario->name }}</flux:cell>
                                <flux:table.cell align="right">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $rnc)
                                        <flux:button href="{{ route('rnc.edit', $rnc->id) }}" variant="ghost" size="sm" icon="pencil-square"></flux:button>
                                        @endcan
                                        <flux:button variant="ghost" size="sm" icon="printer"></flux:button>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="py-10 text-center text-zinc-500">Nenhuma Não Conformidade registrada.</flux:cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $rncs->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
