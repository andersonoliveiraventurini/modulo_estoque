@if($mostrarFormulario)
    <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900" id="card-residual" x-data>

        {{-- Título da seção --}}
        <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100 mb-4">
            <x-heroicon-o-currency-dollar class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Cobrança Residual
        </h3>

        {{-- Flash de erro --}}
        @if(session()->has('residual_erro'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 animate-bounce">
                <div class="flex gap-3">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-red-700 dark:text-red-300">{{ session('residual_erro') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash de sucesso --}}
        @if(session()->has('residual_sucesso'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 animate-pulse">
                <div class="flex gap-3">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-green-700 dark:text-green-300">{{ session('residual_sucesso') }}</p>
                </div>
            </div>
        @endif

        {{-- Formulário --}}
        <form wire:submit.prevent="salvarResidual">
            <div class="grid grid-cols-12 gap-4 items-start">
                {{-- Campo: Valor (2/12) --}}
                <div class="col-span-12 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Valor (R$)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        wire:model="valor"
                        required
                        placeholder="0,00"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    @error('valor')
                        <p class="text-[10px] text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo: Observações (10/12) --}}
                <div class="col-span-12 md:col-span-10">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Motivo da Cobrança *
                    </label>
                    <input
                        type="text"
                        wire:model="observacoes"
                        required
                        placeholder="Ex: Diferença de frete, acréscimo de item..."
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    @error('observacoes')
                        <p class="text-[10px] text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Botão --}}
            <div class="flex justify-end mt-4">
                <button
                    type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="salvarResidual"
                >
                    <span wire:loading.remove wire:target="salvarResidual" class="flex items-center gap-2">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>Registrar Cobrança Residual</span>
                    </span>
                    <span wire:loading wire:target="salvarResidual" class="flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Salvando...</span>
                    </span>
                </button>
            </div>
        </form>

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
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        // Fallback caso SweetAlert não esteja carregado
                        alert(data.title + ": " + data.text);
                    }
                });
            });
        </script>
    </div>
@endif
