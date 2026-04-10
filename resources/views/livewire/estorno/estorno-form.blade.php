<div>
    <flux:heading size="xl" level="1">Solicitar Estorno</flux:heading>
    <flux:subheading size="lg" class="mb-6">Pagamento #{{ $pagamento->id }} | Valor Pago: R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</flux:subheading>

    @if ($errors->has('geral'))
        <div class="mb-4 text-red-600 dark:text-red-300 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 rounded text-sm">
            {{ $errors->first('geral') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 max-w-2xl bg-white p-6 rounded-lg border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700">
        
        <flux:input wire:model="form.valor" label="Valor a estornar (R$)" type="number" step="0.01" />

        <flux:select wire:model.live="form.forma_estorno" label="Forma de Estorno" placeholder="Selecione a forma de devolução...">
            <flux:select.option value="dinheiro">Dinheiro</flux:select.option>
            <flux:select.option value="pix">PIX</flux:select.option>
            <flux:select.option value="debito">Cartão de Débito</flux:select.option>
            <flux:select.option value="credito">Cartão de Crédito</flux:select.option>
            <flux:select.option value="outro">Outro (Especificar)</flux:select.option>
        </flux:select>

        @if($form->forma_estorno === 'outro')
            <flux:input wire:model="form.forma_estorno_detalhe" label="Detalhamento da forma de estorno" />
        @endif

        <flux:textarea wire:model="form.motivo" label="Motivo do Estorno" rows="4" placeholder="Descreva os motivos para a solicitação do estorno..." />

        <div class="flex items-center gap-4 mt-6">
            <flux:button type="submit" variant="primary">Solicitar Estorno</flux:button>
            <flux:button href="{{ route('pagamentos.show', $pagamento->id) }}" variant="ghost">Cancelar</flux:button>
        </div>
    </form>
</div>
