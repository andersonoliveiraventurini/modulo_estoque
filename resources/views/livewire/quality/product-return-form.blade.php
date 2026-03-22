<div class="space-y-6">
    <div class="mb-6">
        <flux:heading size="xl">{{ $isEdit ? __('Editar Solicitação') : __('Nova Solicitação de Devolução') }}</flux:heading>
        <flux:subheading>{{ __('As devoluções devem ser iniciadas a partir de um orçamento pago.') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Seleção de Orçamento -->
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Buscar Orçamento Pago') }}</flux:label>
                        <div class="relative">
                            <flux:input wire:model.live="searchOrcamento" 
                                        wire:focus="$set('showOrcamentoSearch', true)"
                                        placeholder="{{ __('Pesquisar por ID ou Cliente...') }}" 
                                        icon="magnifying-glass" />
                            <flux:error name="orcamento_id" />
                            
                            @if($showOrcamentoSearch && !empty($orcamentos))
                                <div class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg dark:bg-zinc-800 dark:border-zinc-700 max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        @foreach($orcamentos as $orc)
                                            <li>
                                                <button type="button" 
                                                        wire:click="selectOrcamento({{ $orc->id }})"
                                                        class="w-full px-4 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 border-b last:border-0 border-zinc-100 dark:border-zinc-700">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-bold">#{{ $orc->id }}</span>
                                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">PAGO</span>
                                                    </div>
                                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $orc->cliente->nome }}</div>
                                                    <div class="text-[10px] text-zinc-500">{{ $orc->created_at->format('d/m/Y') }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </flux:field>

                    @if($orcamento_id)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500 uppercase font-bold">{{ __('Orçamento Selecionado') }}</span>
                                <span class="text-xs font-bold font-mono">#{{ $orcamento_nr }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ $cliente_nome }}</p>
                                <p class="text-xs text-zinc-500">{{ __('Vendedor:') }} {{ $vendedor_nome }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Datas e Documentos -->
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Data da Ocorrência') }}</flux:label>
                        <flux:input type="date" wire:model="data_ocorrencia" icon="calendar" />
                        <flux:error name="data_ocorrencia" />
                    </flux:field>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Nota Fiscal') }}</flux:label>
                            <flux:input wire:model="nota_fiscal" icon="document-text" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Romaneio') }}</flux:label>
                            <flux:input wire:model="romaneio_recebimento" icon="truck" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-zinc-100 dark:border-zinc-700" />

            <!-- Seleção de Itens -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-bold text-lg text-zinc-800 dark:text-zinc-200">{{ __('Itens para Devolução') }}</h3>
                    <flux:checkbox wire:model="troca_produto" label="{{ __('Solicitar TROCA de material') }}" />
                </div>
                
                @if(empty($orcamento_items))
                    <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 text-zinc-400 italic">
                        {{ __('Selecione um orçamento para visualizar os itens.') }}
                    </div>
                @else
                    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Produto') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('Qtd Comprada') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('Prc Original') }}</th>
                                    <th class="px-4 py-3 w-32 text-center">{{ __('Retornar') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                                @foreach($orcamento_items as $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $item->produto->nome ?? __('Produto não identificado') }}</div>
                                            <div class="text-[10px] text-zinc-500">{{ $item->produto->sku ?? '---' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-400">
                                            {{ number_format($item->quantidade, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-zinc-600 dark:text-zinc-400">
                                            R$ {{ number_format($item->valor_unitario_com_desconto ?? $item->valor_unitario, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <flux:input type="number" step="0.01" 
                                                        wire:model.live="items_to_return.{{ $item->id }}" 
                                                        class="text-center" 
                                                        max="{{ $item->quantidade }}" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <flux:error name="items_to_return" />
                    <flux:error name="items" />
                @endif
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Observações / Motivo da Devolução') }}</flux:label>
                    <flux:textarea wire:model="observacoes" placeholder="{{ __('Descreva o motivo detalhado...') }}" rows="3" />
                    <flux:error name="observacoes" />
                </flux:field>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <flux:button href="{{ route('quality.dashboard') }}" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Enviar para Aprovação') }}</flux:button>
            </div>
        </flux:card>
    </form>
</div>
