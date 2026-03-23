<div>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Adicionar Crédito Manual</h3>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200 dark:bg-green-900/30 dark:border-green-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-check-circle class="h-5 w-5 text-green-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="salvar" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="valor" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Valor (R$)</label>
                        <input type="number" wire:model.live="valor" id="valor" step="0.01" min="0.01" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-zinc-800 dark:text-white" placeholder="0.00">
                        @error('valor') <span class="text-sm text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="tipo" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Tipo de Crédito</label>
                        <select wire:model.live="tipo" id="tipo" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-zinc-800 dark:text-white">
                            <option value="ajuste">Ajuste</option>
                            <option value="bonificacao">Bonificação</option>
                            <option value="outro">Outro</option>
                        </select>
                        @error('tipo') <span class="text-sm text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="motivo" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Motivo / Justificativa</label>
                    <textarea wire:model.live="motivo" id="motivo" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-zinc-800 dark:text-white" placeholder="Informe o motivo da adição de crédito..."></textarea>
                    @error('motivo') <span class="text-sm text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="salvar">Salvar Crédito</span>
                        <span wire:loading wire:target="salvar">Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
