<!-- Wrapper Principal -->
<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            
            @if(!$orcamento)
                <!-- Mensagem quando orçamento não está carregado -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-4">
                        <x-heroicon-o-exclamation-circle class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Orçamento não encontrado</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        O orçamento ID: <span class="font-semibold">{{ $orcamentoId ?? 'N/A' }}</span> não foi localizado.
                    </p>
                    <button type="button" 
                            wire:click="$toggle('showModal')"
                            class="inline-flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Voltar
                    </button>
                </div>
            @else
                <!-- Header da Aprovação -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 mb-2">
                        <x-heroicon-o-receipt-percent class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        Aprovação de Descontos - Orçamento #{{ $orcamento->id }}
                    </h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Revise e aprove ou rejeite cada desconto aplicado ao orçamento
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

                <!-- Informações do Orçamento -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        Informações do Orçamento
                    </h3>
                    
                    <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dados Gerais</h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total Original</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            R$ {{ number_format($orcamento->valor_total_itens ?? 0, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200 dark:border-gray-700" />

                <!-- Lista de Descontos para Aprovação -->
                <div class="space-y-4 mb-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-tag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            Descontos Pendentes de Aprovação
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-sm font-medium rounded-full">
                                {{ is_countable($descontos) ? count($descontos) : 0 }} desconto(s)
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($descontos as $index => $desconto)
                            <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden" 
                                 wire:key="desconto-{{ $desconto->id }}">
                                
                                <!-- Header do Desconto -->
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 px-4 py-3 border-b border-blue-200 dark:border-blue-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-500 text-white text-sm font-bold rounded-full">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $desconto->motivo }}
                                                </h4>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    Solicitado por: {{ $desconto->user->name ?? 'Sistema' }} • 
                                                    {{ $desconto->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <!-- Badge do Tipo -->
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            {{ $desconto->tipo === 'fixo' 
                                                ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' 
                                                : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' }}">
                                            {{ $desconto->tipo === 'fixo' ? 'Valor Fixo' : 'Percentual' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Corpo do Desconto -->
                                <div class="bg-white dark:bg-zinc-900 p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        
                                        <!-- Valor do Desconto -->
                                        <div class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <div class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full">
                                                <x-heroicon-o-currency-dollar class="w-5 h-5 text-red-600 dark:text-red-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">Valor do Desconto</p>
                                                <p class="text-lg font-bold text-red-700 dark:text-red-300">
                                                    R$ {{ number_format($desconto->valor, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Percentual (se aplicável) -->
                                        @if ($desconto->tipo === 'percentual' && $desconto->porcentagem)
                                            <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full">
                                                    <x-heroicon-o-percent-badge class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Percentual</p>
                                                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                                        {{ number_format($desconto->porcentagem, 2, ',', '.') }}%
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Valor com Desconto Aplicado -->
                                        <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full">
                                                <x-heroicon-o-calculator class="w-5 h-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-green-600 dark:text-green-400 font-medium">Novo Valor Total</p>
                                                <p class="text-lg font-bold text-green-700 dark:text-green-300">
                                                    R$ {{ number_format(($orcamento->valor_total_itens ?? 0) - $desconto->valor, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informações Adicionais -->
                                    @if ($desconto->observacao)
                                        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-4">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Observação:</p>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $desconto->observacao }}</p>
                                        </div>
                                    @endif

                                    <!-- Ações de Aprovação/Rejeição -->
                                    <div class="flex items-center gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Justificativa (opcional)
                                            </label>
                                            <input type="text" 
                                                   wire:model="justificativas.{{ $desconto->id }}"
                                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Digite uma justificativa (se necessário)">
                                        </div>
                                        
                                        <div class="flex gap-2 pt-5">
                                            <button type="button" 
                                                    wire:click="aprovarDesconto({{ $desconto->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="aprovarDesconto({{ $desconto->id }})"
                                                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                                <span wire:loading.remove wire:target="aprovarDesconto({{ $desconto->id }})">Aprovar</span>
                                                <span wire:loading wire:target="aprovarDesconto({{ $desconto->id }})">...</span>
                                            </button>
                                            
                                            <button type="button" 
                                                    wire:click="rejeitarDesconto({{ $desconto->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rejeitarDesconto({{ $desconto->id }})"
                                                    class="flex items-center gap-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                                <span wire:loading.remove wire:target="rejeitarDesconto({{ $desconto->id }})">Rejeitar</span>
                                                <span wire:loading wire:target="rejeitarDesconto({{ $desconto->id }})">...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h2> Novo envio </h2>

                            <form method="POST"
     action="{{ route('descontos.avaliar', $desconto->id) }}"
      class="flex items-center gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
    @csrf

    <div class="flex-1">
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
            Justificativa (opcional)
        </label>
        <input type="text"
               name="justificativa"
               class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2"
               placeholder="Informe o motivo (opcional)">
    </div>

    <div class="flex gap-2 pt-5">
        <button type="submit"
                name="acao"
                value="aprovar"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
            Aprovar
        </button>

        <button type="submit"
                name="acao"
                value="rejeitar"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
            Rejeitar
        </button>
    </div>
</form>
                        @empty
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <x-heroicon-o-check-circle class="w-16 h-16 mx-auto mb-3 opacity-50 text-green-500" />
                                <p class="text-lg font-medium mb-1">Nenhum desconto pendente</p>
                                <p class="text-sm">Todos os descontos foram avaliados ou não há descontos aplicados.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Ações em Lote (se houver múltiplos descontos) -->
                    @if (is_countable($descontos) && count($descontos) > 1)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                                        Ações em lote para todos os descontos:
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" 
                                            wire:click="aprovarTodos"
                                            class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                        Aprovar Todos
                                    </button>
                                    <button type="button" 
                                            wire:click="rejeitarTodos"
                                            class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                        <x-heroicon-o-x-circle class="w-4 h-4" />
                                        Rejeitar Todos
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="my-6 border-gray-200 dark:border-gray-700" />

                <!-- Resumo Financeiro -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <x-heroicon-o-calculator class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        Resumo Financeiro
                    </h3>

                    <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-4 py-3 border-b border-blue-700 dark:border-blue-800">
                            <h4 class="text-sm font-semibold text-white">Impacto dos Descontos</h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4 space-y-2">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor Original:</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    R$ {{ number_format($orcamento->valor_total_itens ?? 0, 2, ',', '.') }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total de Descontos Solicitados:</span>
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                    - R$ {{ number_format(collect($descontos)->sum('valor'), 2, ',', '.') }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600">
                                <span class="text-base font-bold text-gray-900 dark:text-gray-100">Valor Final (se todos aprovados):</span>
                                <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ number_format(($orcamento->valor_total_itens ?? 0) - collect($descontos)->sum('valor'), 2, ',', '.') }}
                                </span>
                            </div>

                            @if(($orcamento->valor_total_itens ?? 0) > 0)
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mt-3">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-yellow-900 dark:text-yellow-200 mb-1">Percentual Total de Desconto:</p>
                                            <p class="text-lg font-bold text-yellow-700 dark:text-yellow-300">
                                                {{ number_format((collect($descontos)->sum('valor') / $orcamento->valor_total_itens) * 100, 2, ',', '.') }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Ações Finais -->
                <div class="flex gap-4 mt-6">
                    <button type="button" 
                            wire:click="$toggle('showModal')"
                            class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        Fechar
                    </button>
                    <button type="button" 
                            wire:click="finalizarAnalise"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed dark:disabled:bg-gray-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="finalizarAnalise" class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="w-5 h-5" />
                            <span>Finalizar Análise</span>
                        </span>
                        <span wire:loading wire:target="finalizarAnalise" class="flex items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processando...</span>
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>