<!-- Wrapper Principal -->
<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            
            <!-- Header do Pagamento -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    Pagamento no Balcão - Orçamento #{{ $orcamento->id }}
                </h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Finalize o pagamento do orçamento selecionado
                </p>
            </div>

            <!-- Alertas de Erro -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Atenção aos seguintes erros:</h4>
                            <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mensagens de Sucesso -->
            @if (session()->has('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Informações do Orçamento -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    Informações do Orçamento
                </h3>
                
                <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dados do Orçamento</h4>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-user class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->cliente->nome ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-user-circle class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->vendedor->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-credit-card class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Condição Original</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->condicaoPagamento->nome ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700" />

            <!-- Formas de Pagamento -->
            <div class="space-y-4 mb-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        Formas de Pagamento
                    </h3>
                    <button type="button" wire:click="adicionarFormaPagamento"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium shadow-sm">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Adicionar Forma
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($formasPagamento as $index => $forma)
                        <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden" 
                             wire:key="forma-{{ $index }}">
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma #{{ $index + 1 }}</span>
                                    @if (count($formasPagamento) > 1)
                                        <button type="button" wire:click="removerFormaPagamento({{ $index }})"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm flex items-center gap-1">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            Remover
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-white dark:bg-zinc-900 p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Forma de Pagamento *
                                        </label>
                                        <select wire:model.live="formasPagamento.{{ $index }}.condicao_id" 
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Selecione...</option>
                                            @foreach ($condicoesPagamento as $condicao)
                                                <option value="{{ $condicao->id }}">{{ $condicao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('formasPagamento.' . $index . '.condicao_id')
                                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Valor *
                                        </label>
                                        <input type="number" wire:model.live="formasPagamento.{{ $index }}.valor"
                                               class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               step="0.01" min="0" placeholder="0,00">
                                        @error('formasPagamento.' . $index . '.valor')
                                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <x-heroicon-o-credit-card class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Nenhuma forma de pagamento adicionada</p>
                        </div>
                    @endforelse

                    <!-- Valor Restante -->
                    @if ($valorComDesconto - $valorPago > 0.01)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Valor Restante</p>
                                        <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                            R$ {{ number_format($valorComDesconto - $valorPago, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" wire:click="preencherRestante"
                                        class="flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                    <x-heroicon-o-sparkles class="w-4 h-4" />
                                    <span>Preencher</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Alerta de Desconto com Cartão -->
                    @if ($descontoOriginal > 0 && $this->usandoCartao())
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-yellow-900 dark:text-yellow-200 mb-1">Atenção!</p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">
                                        A condição original era PIX/Dinheiro com desconto. Ao usar cartão, você pode remover o desconto original.
                                    </p>
                                    <button type="button" wire:click="removerDescontoOriginal"
                                            class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                        Remover Desconto Original
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700" />

            <!-- Desconto no Balcão -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-tag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    Desconto no Balcão (até 3%)
                </h3>
                
                <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-white dark:bg-zinc-900 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Valor do Desconto
                                </label>
                                <input type="number" wire:model.live="descontoBalcao"
                                       class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       step="0.01" min="0" max="{{ $orcamento->valor_total_itens * 0.03 }}" placeholder="0,00">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Máximo: R$ {{ number_format($orcamento->valor_total_itens * 0.03, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Percentual
                                </label>
                                <input type="text" readonly
                                       class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                                       value="{{ $orcamento->valor_total_itens > 0 ? number_format(($descontoBalcao / $orcamento->valor_total_itens) * 100, 2, ',', '.') : 0 }}%">
                            </div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mt-4">
                            <div class="flex gap-2">
                                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Desconto disponível apenas para pagamentos em PIX ou Dinheiro, sem desconto prévio.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700" />

            <!-- Documento Fiscal -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    Documento Fiscal
                </h3>
                
                <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-white dark:bg-zinc-900 p-4 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="precisaNotaFiscal"
                                   class="w-4 h-4 text-blue-600 bg-white dark:bg-zinc-800 border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500"
                                   id="precisaNotaFiscal-{{ $orcamentoId }}">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Precisa de Nota Fiscal?</span>
                        </label>

                        @if ($precisaNotaFiscal)
                            <div class="space-y-4 pl-7">
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                    <div class="flex gap-2">
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0" />
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                            Os dados da nota fiscal devem ser os mesmos do pagamento.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                <div class="flex gap-2">
                                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        Será gerado um cupom fiscal.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-200 dark:border-gray-700" />

            <!-- Resumo do Pagamento -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-medium flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-calculator class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    Resumo do Pagamento
                </h3>

                <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-green-600 dark:bg-green-700 px-4 py-3 border-b border-green-700 dark:border-green-800">
                        <h4 class="text-sm font-semibold text-white">Valores Finais</h4>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 p-4 space-y-2">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor Total dos Itens:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                            </span>
                        </div>
                        
                        @if ($descontoAplicado > 0)
                            <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Desconto Original:</span>
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                    - R$ {{ number_format($descontoAplicado, 2, ',', '.') }}
                                </span>
                            </div>
                        @endif
                        
                        @if ($descontoBalcao > 0)
                            <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Desconto no Balcão:</span>
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                    - R$ {{ number_format($descontoBalcao, 2, ',', '.') }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600">
                            <span class="text-base font-bold text-gray-900 dark:text-gray-100">Valor a Pagar:</span>
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                R$ {{ number_format($valorComDesconto, 2, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600">
                            <span class="text-base font-bold text-gray-900 dark:text-gray-100">Valor Pago:</span>
                            <span class="text-xl font-bold {{ $valorPago >= $valorComDesconto ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                R$ {{ number_format($valorPago, 2, ',', '.') }}
                            </span>
                        </div>
                        
                        @if ($troco > 0)
                            <div class="flex justify-between items-center py-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-base font-bold text-gray-900 dark:text-gray-100">Troco:</span>
                                <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                    R$ {{ number_format($troco, 2, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        @if ($valorPago < $valorComDesconto)
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mt-3">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                                    <div>
                                        <p class="text-sm font-medium text-red-900 dark:text-red-200">Falta pagar:</p>
                                        <p class="text-lg font-bold text-red-700 dark:text-red-300">
                                            R$ {{ number_format($valorComDesconto - $valorPago, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex gap-4 mt-6">
                <button type="button" wire:click="$toggle('showModal')"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-3 rounded-lg transition-colors shadow-sm">
                    Cancelar
                </button>
                <button type="button" 
                        wire:click="finalizarPagamento" 
                        wire:loading.attr="disabled"
                        @if ($valorPago < $valorComDesconto) disabled @endif
                        class="flex items-center gap-2 font-medium px-6 py-3 rounded-lg transition-colors shadow-sm
                               {{ $valorPago >= $valorComDesconto 
                                  ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer' 
                                  : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' }}
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="finalizarPagamento" class="flex items-center gap-2">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        <span>Finalizar Pagamento</span>
                    </span>
                    <span wire:loading wire:target="finalizarPagamento" class="flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processando...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>