<div class="p-6 max-w-5xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
            <a href="{{ route('devolucao.dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Devolução</a>
            <flux:icon icon="chevron-right" class="w-3 h-3" />
            <span>Solicitar Devolução</span>
        </div>
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Solicitar Devolução de Mercadoria</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Inicie o processo de devolução vinculado a um orçamento já pago.</p>
    </div>

    <div class="space-y-6">
        {{-- Busca do Orçamento --}}
        <flux:card class="p-6">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <flux:field>
                        <flux:label>Número do Orçamento (Pago)</flux:label>
                        <flux:input wire:model="search_orcamento" placeholder="Ex: 123" wire:keydown.enter="buscarOrcamento" />
                        <flux:error name="search_orcamento" />
                    </flux:field>
                </div>
                <flux:button wire:click="buscarOrcamento" variant="primary" icon="magnifying-glass">Buscar Itens</flux:button>
            </div>
        </flux:card>

        @if ($orcamento)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Dados da Devolução --}}
                <div class="lg:col-span-1 space-y-6">
                    <flux:card class="p-6 space-y-4">
                        <h3 class="font-bold text-zinc-900 dark:text-white border-b pb-2">Informações da Solicitação</h3>
                        
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Cliente</label>
                            <p class="text-sm font-medium">{{ $orcamento->cliente->nome }}</p>
                        </div>

                        <flux:field>
                            <flux:label>Data da Ocorrência</flux:label>
                            <flux:input type="date" wire:model="data_ocorrencia" />
                        </flux:field>

                        <div class="grid grid-cols-2 gap-3">
                            <flux:field>
                                <flux:label>Nota Fiscal</flux:label>
                                <flux:input wire:model="nota_fiscal" placeholder="Nº NF" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Romaneio / Recebimento</flux:label>
                                <flux:input wire:model="romaneio_recebimento" placeholder="Nº Romaneio" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Observações</flux:label>
                            <flux:textarea wire:model="observacoes" rows="3" placeholder="Motivo da devolução..." />
                        </flux:field>

                        <flux:checkbox wire:model="troca_produto" label="Deseja trocar por outro produto?" description="Isso gerará um romaneio de troca após aprovação." />
                    </flux:card>
                </div>

                {{-- Seleção de Itens --}}
                <div class="lg:col-span-2 space-y-4">
                    <flux:card class="p-0 overflow-hidden">
                        <div class="p-4 border-b bg-zinc-50 dark:bg-zinc-800/50">
                            <h3 class="font-bold text-zinc-900 dark:text-white">Selecione os Itens para Devolver</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 uppercase text-[10px] font-bold tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 w-10">Select</th>
                                        <th class="px-4 py-3">Produto</th>
                                        <th class="px-4 py-3 text-right">Qtd Vendida</th>
                                        <th class="px-4 py-3 text-right w-32">Qtd Devolver</th>
                                        <th class="px-4 py-3 text-right">Preço Un.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach ($orcamento->itens as $item)
                                        <tr class="{{ in_array($item->id, $items_selecionados) ? 'bg-indigo-50/30 dark:bg-indigo-950/20' : '' }}">
                                            <td class="px-4 py-3">
                                                <flux:checkbox wire:model.live="items_selecionados" value="{{ $item->id }}" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-zinc-900 dark:text-white">{{ $item->produto->nome ?? 'Produto não identificado' }}</div>
                                                <div class="text-xs text-zinc-500">Ref: {{ $item->produto->referencia ?? '-' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-right">
                                                @if (in_array($item->id, $items_selecionados))
                                                    <flux:input 
                                                        type="number" 
                                                        step="1" 
                                                        wire:model.live="quantidades.{{ $item->id }}" 
                                                        size="sm"
                                                        class="text-right"
                                                    />
                                                    @error('quantidades.' . $item->id)
                                                        <span class="text-[10px] text-rose-600 block mt-1">{{ $message }}</span>
                                                    @enderror
                                                @else
                                                    <span class="text-zinc-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </flux:card>

                    <div class="flex justify-end gap-3 p-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <flux:button href="{{ route('devolucao.dashboard') }}" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="save" variant="primary" icon="paper-airplane">Enviar para Aprovação</flux:button>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-zinc-500 bg-zinc-50 dark:bg-zinc-800/20 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                <flux:icon icon="magnifying-glass" class="w-12 h-12 mb-4 opacity-20" />
                <p>Busque um orçamento para visualizar os itens e iniciar a devolução.</p>
            </div>
        @endif
    </div>
</div>
