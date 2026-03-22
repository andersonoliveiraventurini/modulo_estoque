<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Gestão de Qualidade</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Acompanhamento de Devoluções e Não Conformidades (RNC).</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('rnc.create') }}" variant="ghost" icon="plus">Nova RNC</flux:button>
            <flux:button href="{{ route('product_returns.create') }}" variant="primary" icon="arrow-path">Solicitar Devolução</flux:button>
        </div>
    </div>

    <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por Nr, Cliente ou Produto..." icon="magnifying-glass" />
        </div>
        <div class="w-full md:w-48">
            <flux:select wire:model.live="status_filter" placeholder="Status">
                <flux:option value="">Todos os Status</flux:option>
                <flux:option value="pendente_supervisor">Pendente Supervisor</flux:option>
                <flux:option value="pendente_estoque">Pendente Estoque</flux:option>
                <flux:option value="finalizado">Finalizado</flux:option>
                <flux:option value="negado">Negado</flux:option>
            </flux:select>
        </div>
    </div>

    <flux:tabs wire:model="tab">
        <flux:tab name="returns" icon="arrow-path">Devoluções de Vendas</flux:tab>
        <flux:tab name="rnc" icon="exclamation-triangle">RNC (Não Conformidades)</flux:tab>
    </flux:tabs>

    <div class="mt-6">
        @if ($tab === 'returns')
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:table>
                    <flux:columns>
                        <flux:column>Nr</flux:column>
                        <flux:column>Cliente / Orçamento</flux:column>
                        <flux:column>Vendedor</flux:column>
                        <flux:column>Data</flux:column>
                        <flux:column>Valor Crédito</flux:column>
                        <flux:column>Status</flux:column>
                        <flux:column align="right">Ações</flux:column>
                    </flux:columns>

                    <flux:rows>
                        @forelse ($returns as $ret)
                            <flux:row>
                                <flux:cell class="font-bold">#{{ $ret->nr }}</flux:cell>
                                <flux:cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $ret->cliente->nome }}</div>
                                    <div class="text-xs text-zinc-500">Orçamento: #{{ $ret->orcamento_id }}</div>
                                </flux:cell>
                                <flux:cell>{{ $ret->vendedor->name }}</flux:cell>
                                <flux:cell>{{ $ret->data_ocorrencia->format('d/m/Y') }}</flux:cell>
                                <flux:cell class="font-medium">R$ {{ number_format($ret->valor_total_credito, 2, ',', '.') }}</flux:cell>
                                <flux:cell>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        @if($ret->status === 'pendente_supervisor') bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400
                                        @elseif($ret->status === 'pendente_estoque') bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400
                                        @elseif($ret->status === 'finalizado') bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400
                                        @else bg-rose-100 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 @endif">
                                        {{ $ret->status_label }}
                                    </span>
                                </flux:cell>
                                <flux:cell align="right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button href="{{ route('product_returns.approve', $ret->id) }}" variant="ghost" size="sm" icon="eye">Ver / Aprovar</flux:button>
                                        @if ($ret->status === 'finalizado')
                                            <flux:button variant="ghost" size="sm" icon="document-text" title="Ver Autorização"></flux:button>
                                        @else
                                            <flux:button variant="ghost" size="sm" icon="printer" title="Imprimir Solicitação"></flux:button>
                                        @endif
                                    </div>
                                </flux:cell>
                            </flux:row>
                        @empty
                            <flux:row>
                                <flux:cell colspan="7" class="py-10 text-center text-zinc-500">Nenhuma devolução encontrada.</flux:cell>
                            </flux:row>
                        @endforelse
                    </flux:rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $returns->links() }}
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:table>
                    <flux:columns>
                        <flux:column>Nr</flux:column>
                        <flux:column>Produto / Fornecedor</flux:column>
                        <flux:column>NF / ROM</flux:column>
                        <flux:column>Data Ocorrência</flux:column>
                        <flux:column>Registrado por</flux:column>
                        <flux:column align="right">Ações</flux:column>
                    </flux:columns>

                    <flux:rows>
                        @forelse ($rncs as $rnc)
                            <flux:row>
                                <flux:cell class="font-bold">#{{ $rnc->nr }}</flux:cell>
                                <flux:cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $rnc->produto_nome }}</div>
                                    <div class="text-xs text-zinc-500">{{ $rnc->fornecedor_nome }}</div>
                                </flux:cell>
                                <flux:cell>
                                    <div class="text-xs">NF: {{ $rnc->nota_fiscal ?? '-' }}</div>
                                    <div class="text-xs">ROM: {{ $rnc->romaneio_recebimento ?? '-' }}</div>
                                </flux:cell>
                                <flux:cell>{{ $rnc->data_ocorrencia->format('d/m/Y') }}</flux:cell>
                                <flux:cell>{{ $rnc->usuario->name }}</flux:cell>
                                <flux:cell align="right">
                                    <div class="flex justify-end gap-2">
                                        <flux:button href="{{ route('rnc.edit', $rnc->id) }}" variant="ghost" size="sm" icon="pencil-square"></flux:button>
                                        <flux:button variant="ghost" size="sm" icon="printer"></flux:button>
                                    </div>
                                </flux:cell>
                            </flux:row>
                        @empty
                            <flux:row>
                                <flux:cell colspan="6" class="py-10 text-center text-zinc-500">Nenhuma Não Conformidade registrada.</flux:cell>
                            </flux:row>
                        @endforelse
                    </flux:rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $rncs->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
