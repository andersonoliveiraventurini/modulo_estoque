<div class="space-y-6">
    <div class="mb-6">
        <flux:heading size="xl">{{ $isEdit ? __('Editar RNC') : __('Nova Não Conformidade (RNC)') }}</flux:heading>
        <flux:subheading>{{ __('Preencha os dados abaixo para registrar a ocorrência.') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Seção de Produto -->
                <div class="space-y-3">
                    <flux:field>
                        <flux:label>{{ __('Produto') }}</flux:label>
                        <div class="relative">
                            <flux:input wire:model.live="produto_nome" 
                                        wire:focus="$set('showProdutoSearch', true)"
                                        placeholder="{{ __('Pesquisar ou digitar nome do produto...') }}" 
                                        icon="magnifying-glass" />
                            <flux:error name="produto_nome" />
                            
                            @if($showProdutoSearch && !empty($produtos))
                                <div class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg dark:bg-zinc-800 dark:border-zinc-700">
                                    <ul class="py-1">
                                        @foreach($produtos as $prod)
                                            <li>
                                                <button type="button" 
                                                        wire:click="selectProduto({{ $prod->id }}, '{{ $prod->nome }}')"
                                                        class="w-full px-4 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center justify-between">
                                                    <span>{{ $prod->nome }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $prod->sku }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Fornecedor') }}</flux:label>
                        <div class="relative">
                            <flux:input wire:model.live="fornecedor_nome" 
                                        wire:focus="$set('showFornecedorSearch', true)"
                                        placeholder="{{ __('Pesquisar ou digitar fornecedor...') }}" 
                                        icon="building-office" />
                            <flux:error name="fornecedor_nome" />

                            @if($showFornecedorSearch && !empty($fornecedores))
                                <div class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg dark:bg-zinc-800 dark:border-zinc-700">
                                    <ul class="py-1">
                                        @foreach($fornecedores as $forn)
                                            <li>
                                                <button type="button" 
                                                        wire:click="selectFornecedor({{ $forn->id }}, '{{ $forn->nome_fantasia ?? $forn->razao_social }}')"
                                                        class="w-full px-4 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                                    {{ $forn->nome_fantasia ?? $forn->razao_social }}
                                                    <div class="text-[10px] text-zinc-500">{{ $forn->cnpj }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </flux:field>
                </div>

                <!-- Seção de Dados da Ocorrência -->
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
                            <flux:error name="nota_fiscal" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Romaneio') }}</flux:label>
                            <flux:input wire:model="romaneio_recebimento" icon="truck" />
                            <flux:error name="romaneio_recebimento" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <flux:field>
                    <flux:label>{{ __('Ações Tomadas') }}</flux:label>
                    <flux:textarea wire:model="acoes_tomadas" placeholder="{{ __('Descreva o que foi feito...') }}" rows="3" />
                    <flux:error name="acoes_tomadas" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Observações') }}</flux:label>
                    <flux:textarea wire:model="observacoes" placeholder="{{ __('Detalhes adicionais...') }}" rows="3" />
                    <flux:error name="observacoes" />
                </flux:field>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button href="{{ route('quality.dashboard') }}" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button type="submit" variant="primary" icon="check">{{ __('Salvar RNC') }}</flux:button>
            </div>
        </flux:card>
    </form>
</div>
