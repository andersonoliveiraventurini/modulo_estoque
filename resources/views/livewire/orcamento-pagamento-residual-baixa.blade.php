<div class="p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Baixar Pagamento Residual</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cobrança #{{ $pagamento->id }} · Orçamento #{{ $pagamento->orcamento_id }}
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                    Motivo: {{ $pagamento->observacoes }}
                </p>
            </div>
            <a href="{{ route('orcamentos.show', $pagamento->orcamento_id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-zinc-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                Cancelar
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-zinc-800 bg-emerald-50/50 dark:bg-emerald-900/10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <x-heroicon-o-check-badge class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Registrar Pagamento</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Confirmar recebimento do valor residual</p>
                </div>
            </div>

            <form wire:submit.prevent="registrarPagamento" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Valor Pago (R$)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400">R$</span>
                            <input type="number" step="0.01" wire:model="registro.valor_pago" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-3 py-2.5 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 shadow-sm">
                        </div>
                        @error('registro.valor_pago') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Data do Pagamento</label>
                        <input type="date" wire:model="registro.data_pagamento" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 shadow-sm">
                        @error('registro.data_pagamento') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Forma de Pagamento Utilizada</label>
                    <select wire:model="registro.condicao_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 shadow-sm">
                        <option value="">Selecione...</option>
                        @foreach($condicoes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                        @endforeach
                    </select>
                    @error('registro.condicao_id') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Comprovante de Pagamento</label>
                    <input type="file" wire:model="registro.comprovante" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
                    <div wire:loading wire:target="registro.comprovante" class="mt-2 text-[10px] text-blue-500 font-bold animate-pulse flex items-center gap-2">
                        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando arquivo...
                    </div>
                    @error('registro.comprovante') <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50" wire:loading.attr="disabled">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                        Finalizar e Baixar Residual
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify-swal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || '',
                        text: data.text || '',
                        timer: 4000,
                        timerProgressBar: true,
                        confirmButtonColor: '#10b981'
                    });
                }
            });
        });
    </script>
</div>
