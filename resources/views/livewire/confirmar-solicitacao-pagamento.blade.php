<!-- Wrapper Principal -->
<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

            @if (!$orcamento)
                <!-- Mensagem quando orçamento não está carregado -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-4">
                        <x-heroicon-o-exclamation-circle class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Orçamento não encontrado</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        O orçamento ID: <span class="font-semibold">{{ $orcamentoId ?? 'N/A' }}</span> não foi localizado.
                    </p>
                    <button type="button" wire:click="$toggle('showModal')"
                        class="inline-flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Voltar
                    </button>
                </div>
            @else
                <!-- Header da Aprovação -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2 mb-2">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        Aprovação de Meio de Pagamento - 
                        <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Orçamento #{{ $orcamento->id }}
                        </a>
                    </h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Revise e aprove ou rejeite a solicitação de meio de pagamento especial
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
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $orcamento->cliente->nome ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-user-circle class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $orcamento->vendedor->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            R$ {{ number_format($orcamento->valor_com_desconto > 0 ? $orcamento->valor_com_desconto : $orcamento->valor_total_itens ?? 0, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200 dark:border-gray-700" />

                <!-- Lista de Solicitações para Aprovação -->
                <div class="space-y-4 mb-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            Solicitações Pendentes de Aprovação
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-sm font-medium rounded-full">
                                {{ is_countable($solicitacoes) ? count($solicitacoes) : 0 }} solicitação(ões)
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($solicitacoes as $index => $solicitacao)
                            <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden"
                                wire:key="solicitacao-{{ $solicitacao->id }}">

                                <!-- Header da Solicitação -->
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 px-4 py-3 border-b border-blue-200 dark:border-blue-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-500 text-white text-sm font-bold rounded-full">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    Solicitação de Meio de Pagamento Especial
                                                </h4>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    Solicitado por: {{ $solicitacao->solicitante->name ?? 'Sistema' }} •
                                                    {{ $solicitacao->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Badge do Status -->
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                            {{ $solicitacao->status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Corpo da Solicitação -->
                                <div class="bg-white dark:bg-zinc-900 p-4">
                                    
                                    <!-- Descrição do Pagamento -->
                                    <div class="mb-4">
                                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                            <div class="flex items-start gap-3">
                                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full flex-shrink-0">
                                                    <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-2">Descrição do Meio de Pagamento:</p>
                                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $solicitacao->descricao_pagamento }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detalhes da Solicitação -->
                                    @if ($solicitacao->numero_parcelas || $solicitacao->valor_entrada || $solicitacao->data_primeiro_vencimento)
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            @if ($solicitacao->numero_parcelas)
                                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                    <div class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full">
                                                        <x-heroicon-o-calculator class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Número de Parcelas</p>
                                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                                            {{ $solicitacao->numero_parcelas }}x
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($solicitacao->valor_entrada)
                                                <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full">
                                                        <x-heroicon-o-currency-dollar class="w-5 h-5 text-green-600 dark:text-green-400" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-xs text-green-600 dark:text-green-400 font-medium">Valor de Entrada</p>
                                                        <p class="text-lg font-bold text-green-700 dark:text-green-300">
                                                            R$ {{ number_format($solicitacao->valor_entrada, 2, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($solicitacao->data_primeiro_vencimento)
                                                <div class="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                                    <div class="flex items-center justify-center w-10 h-10 bg-purple-100 dark:bg-purple-900/40 rounded-full">
                                                        <x-heroicon-o-calendar class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">Primeiro Vencimento</p>
                                                        <p class="text-lg font-bold text-purple-700 dark:text-purple-300">
                                                            {{ $solicitacao->data_primeiro_vencimento->format('d/m/Y') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Justificativa da Solicitação -->
                                    @if ($solicitacao->justificativa_solicitacao)
                                        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-4">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Justificativa da Solicitação:</p>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $solicitacao->justificativa_solicitacao }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Observações -->
                                    @if ($solicitacao->observacoes)
                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                                            <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400 mb-1">Observações:</p>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $solicitacao->observacoes }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Formulário de Aprovação/Rejeição -->
                                    <div class="flex items-center gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Justificativa
                                                <span class="text-red-500">*</span>
                                                <span class="text-gray-500">(obrigatória para rejeitar)</span>
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="justificativas.{{ $solicitacao->id }}"
                                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Digite uma justificativa">
                                        </div>

                                        <div class="flex gap-2 pt-5">
                                            <button 
                                                type="button"
                                                wire:click="aprovarSolicitacao({{ $solicitacao->id }})"
                                                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                                <span>Aprovar</span>
                                            </button>

                                            <button 
                                                type="button"
                                                wire:click="rejeitarSolicitacao({{ $solicitacao->id }})"
                                                wire:confirm="Tem certeza que deseja rejeitar esta solicitação? Esta ação não pode ser desfeita."
                                                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                                <span>Rejeitar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-3 opacity-50 text-green-500" />
                                <p class="text-lg font-medium mb-1">Nenhuma solicitação pendente</p>
                                <p class="text-sm">Todas as solicitações foram avaliadas ou não há solicitações pendentes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            @endif
        </div>
    </div>
</div>