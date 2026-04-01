<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Gestão de Valores Residuais</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Orçamento #{{ $orcamento->id }} · Cliente: {{ $orcamento->cliente->nome }}</p>
        </div>
        <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-zinc-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
            Voltar ao Orçamento
        </a>
    </div>

    <div class="grid grid-cols-1 gap-8">
        {{-- (1) REGISTRO DE SOLICITAÇÃO (VALOR A RECEBER) --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-sm overflow-hidden max-w-3xl mx-auto w-full">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-zinc-800 bg-blue-50/50 dark:bg-blue-900/10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Registrar Solicitação de Residual</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Gerar valor pendente para o cliente</p>
                </div>
            </div>

            <form wire:submit.prevent="salvarSolicitacao" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor Residual (R$)</label>
                        <input type="number" step="0.01" wire:model="solicitacao.valor" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        @error('solicitacao.valor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data de Vencimento</label>
                        <input type="date" wire:model="solicitacao.data_vencimento" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        @error('solicitacao.data_vencimento') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meio de Pagamento Sugerido</label>
                    <select wire:model="solicitacao.condicao_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        @foreach($condicoes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                        @endforeach
                    </select>
                    @error('solicitacao.condicao_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição do Serviço / Motivo</label>
                    <textarea wire:model="solicitacao.descricao" rows="3" placeholder="Ex: Ajuste de metragem após medição final..." required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('solicitacao.descricao') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition-all disabled:opacity-50">
                        <x-heroicon-o-plus-circle class="w-4 h-4 mr-2" />
                        Registrar Solicitação
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
                        confirmButtonColor: '#3b82f6'
                    });
                }
            });
        });
    </script>
</div>
