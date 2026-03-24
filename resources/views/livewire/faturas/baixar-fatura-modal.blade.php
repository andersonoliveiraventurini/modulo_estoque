<div>
    <flux:modal wire:model="show" name="baixar-fatura" class="md:w-96">
        @if($form->fatura)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Baixar Fatura</flux:heading>
                    <flux:subheading>
                        FAT-{{ str_pad($form->fatura->id, 5, '0', STR_PAD_LEFT) }} - Cliente: {{ $form->fatura->cliente->nome }}
                    </flux:subheading>
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-neutral-500">Valor Total:</span>
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">R$ {{ number_format($form->fatura->valor_total, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-neutral-500">Já Pago:</span>
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">R$ {{ number_format($form->fatura->valor_pago, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1 border-t border-zinc-200 dark:border-zinc-700 pt-1">
                        <span class="font-semibold text-neutral-700 dark:text-neutral-300">Restante:</span>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                            R$ {{ number_format($form->fatura->valor_total - $form->fatura->valor_pago, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <form wire:submit="salvar" class="space-y-4">
                    
                    <flux:input 
                        wire:model="form.valor_pago" 
                        label="Valor a Pagar (R$)" 
                        type="number" 
                        step="1" 
                        min="0.01" 
                        required 
                    />

                    <flux:input 
                        wire:model="form.data_pagamento" 
                        label="Data do Pagamento" 
                        type="date" 
                        required 
                    />

                    <flux:select wire:model="form.metodo_pagamento_id" label="Método de Pagamento" required>
                        <flux:select.option value="" disabled>Selecione...</flux:select.option>
                        @foreach($metodosDisponiveis as $metodo)
                            <flux:select.option value="{{ $metodo->id }}">{{ $metodo->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea 
                        wire:model="form.observacoes" 
                        label="Observações" 
                        placeholder="Opcional..." 
                        rows="2"
                    />

                    <div class="flex gap-2 pt-2 justify-end">
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancelar</flux:button>
                        </flux:modal.close>
                        
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="salvar">Confirmar Baixa</span>
                            <span wire:loading wire:target="salvar">Processando...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif
    </flux:modal>
</div>
