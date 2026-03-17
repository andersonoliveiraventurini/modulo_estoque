<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800 h-full">
    <flux:heading size="lg" class="mb-4">Gerar Novo Bloco K</flux:heading>
    
    <form wire:submit="gerar" class="space-y-4">
        <div>
            <flux:label>Data de Início</flux:label>
            <flux:input type="date" wire:model="data_inicio" />
            @error('data_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        <div>
            <flux:label>Data de Fim</flux:label>
            <flux:input type="date" wire:model="data_fim" />
            @error('data_fim') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        <div class="pt-2">
            <flux:button type="submit" variant="filled" color="indigo" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Gerar Arquivo SPED</span>
                <span wire:loading>Processando...</span>
            </flux:button>
        </div>
    </form>

    <div class="mt-6 p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/50">
        <div class="flex gap-3">
            <flux:icon.information-circle class="size-5 text-indigo-600 dark:text-indigo-400" />
            <div class="text-xs text-indigo-700 dark:text-indigo-300">
                <p class="font-bold mb-1">Dica Fiscal:</p>
                <p>O Bloco K (estoque escriturado) deve ser gerado preferencialmente no último dia de cada mês para conformidade com o SPED Fiscal.</p>
            </div>
        </div>
    </div>
</div>
