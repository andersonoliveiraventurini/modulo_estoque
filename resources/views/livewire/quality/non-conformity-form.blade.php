<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
            <a href="{{ route('quality.dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Qualidade</a>
            <flux:icon icon="chevron-right" class="w-3 h-3" />
            <span>{{ $isEdit ? 'Editar RNC' : 'Nova RNC' }}</span>
        </div>
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
            {{ $isEdit ? 'Editar Registro de Não Conformidade' : 'Registrar Não Conformidade (RNC)' }}
        </h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">
            Preencha os detalhes da irregularidade encontrada no produto ou recebimento.
        </p>
    </div>

    <form wire:submit="save" class="space-y-6">
        @if ($errors->has('general'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-400">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Coluna 1 -->
            <div class="space-y-6">
                <flux:card class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <flux:icon icon="cube" class="w-5 h-5 text-zinc-400" />
                        Identificação do Item
                    </h3>
                    
                    <flux:field>
                        <flux:label>Produto / Descrição</flux:label>
                        <flux:input wire:model="produto_nome" placeholder="Ex: Vidro Temperado 8mm" />
                        <flux:error name="produto_nome" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fornecedor</flux:label>
                        <flux:input wire:model="fornecedor_nome" placeholder="Nome do Fornecedor" />
                        <flux:error name="fornecedor_nome" />
                    </flux:field>
                </flux:card>

                <flux:card class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <flux:icon icon="document-text" class="w-5 h-5 text-zinc-400" />
                        Documentação
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Nota Fiscal</flux:label>
                            <flux:input wire:model="nota_fiscal" placeholder="Nº NF" />
                            <flux:error name="nota_fiscal" />
                        </flux:field>

                        <flux:field>
                            <flux:label>ROM / Recebimento</flux:label>
                            <flux:input wire:model="romaneio_recebimento" placeholder="Nº ROM" />
                            <flux:error name="romaneio_recebimento" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Data da Ocorrência</flux:label>
                        <flux:input type="date" wire:model="data_ocorrencia" />
                        <flux:error name="data_ocorrencia" />
                    </flux:field>
                </flux:card>
            </div>

            <!-- Coluna 2 -->
            <div class="space-y-6">
                <flux:card class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <flux:icon icon="exclamation-triangle" class="w-5 h-5 text-zinc-400" />
                        Detalhes da Não Conformidade
                    </h3>

                    <flux:field>
                        <flux:label>Observações / Descrição do Problema</flux:label>
                        <flux:textarea wire:model="observacoes" rows="4" placeholder="Descreva detalhadamente o que ocorreu..." />
                        <flux:error name="observacoes" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Ações Tomadas</flux:label>
                        <flux:textarea wire:model="acoes_tomadas" rows="4" placeholder="Quais medidas foram adotadas?" />
                        <flux:error name="acoes_tomadas" />
                    </flux:field>
                </flux:card>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('quality.dashboard') }}" variant="ghost">Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ $isEdit ? 'Atualizar RNC' : 'Registrar e Gerar PDF' }}
            </flux:button>
        </div>
    </form>
</div>
