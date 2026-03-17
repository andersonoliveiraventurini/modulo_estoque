<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800 h-full">
    <flux:heading size="lg" class="mb-4">Gerar Novo Bloco K</flux:heading>

    {{-- Feedback Local --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-800 p-3 text-sm shadow-sm border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 p-3 text-sm shadow-sm border border-red-200">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="relative">
        {{-- Overlay de Carregamento --}}
        <div wire:loading wire:target="gerar" class="absolute inset-0 bg-white/50 dark:bg-neutral-800/50 backdrop-blur-sm z-50 flex items-center justify-center rounded-xl">
            <div class="flex flex-col items-center gap-2">
                <flux:icon.arrow-path class="size-8 text-indigo-600 animate-spin" />
                <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Gerando arquivo...</span>
            </div>
        </div>

        <form wire:submit="gerar" class="space-y-4">
            <div>
                <flux:label>Data de Início</flux:label>
                <flux:input type="date" wire:model="data_inicio" />
                @error('data_inicio') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <flux:label>Data de Fim</flux:label>
                <flux:input type="date" wire:model="data_fim" />
                @error('data_fim') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                <flux:button type="submit" variant="filled" color="indigo" class="w-full" wire:loading.attr="disabled">
                    <flux:icon.document-text class="size-4 mr-2" />
                    Gerar Arquivo SPED
                </flux:button>
            </div>
        </form>
    </div>

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
