{{-- pagamento-rota.blade.php --}}
<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="bg-white p-6 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">

            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-truck class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    Faturamento de Rota — Orçamento #{{ $orcamento->id }}
                </h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Registre o pagamento, revise os comprovantes e emita a decisão do financeiro.
                </p>
            </div>

            {{-- Alertas --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Atenção:</h4>
                            <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-sm text-green-700 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($isBlocked)
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-500 dark:border-amber-600 rounded-xl p-4 animate-pulse">
                    <div class="flex gap-3 items-center">
                        <svg class="w-8 h-8 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <div>
                            <h4 class="font-bold text-amber-900 dark:text-amber-200 text-lg">CLIENTE BLOQUEADO</h4>
                            <p class="text-amber-800 dark:text-amber-300 font-medium text-sm">Pagamento restrito a PIX, Dinheiro ou Cartão de Crédito/Débito.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Grid de 2 colunas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- ─ Coluna esquerda: dados do pedido + comprovantes ──────────────── --}}
                <div class="space-y-6">

                    {{-- Dados do Orçamento --}}
                    <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dados do Pedido</h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4 space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->cliente->nome ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-user-circle class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->vendedor->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-truck class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Transporte / Dia de Carregamento</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $orcamento->transportes->first()?->nome ?? '—' }}
                                        @if($orcamento->loading_day)
                                            <span class="ml-2 px-2 py-0.5 text-xs bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full">
                                                {{ $orcamento->loading_day_formatted }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-credit-card class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Condição de Pagamento</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->condicaoPagamento->nome ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-currency-dollar class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                        R$ {{ number_format($orcamento->valor_total_itens - $orcamento->totalDescontosAprovados(), 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Comprovantes Anexados --}}
                    <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Comprovantes Anexados</h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $orcamento->routeBillingAttachments->count() }} arquivo(s)</span>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4">
                            @forelse($orcamento->routeBillingAttachments as $att)
                                <div class="flex items-center justify-between text-sm p-3 mb-2 rounded-lg border
                                    {{ is_null($att->is_valid) ? 'bg-zinc-50 border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700' : ($att->is_valid ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800') }}">
                                    <div class="flex items-center gap-3">
                                        <x-heroicon-o-paper-clip class="w-4 h-4 text-zinc-500" />
                                        <div>
                                            <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Comprovante #{{ $att->id }}
                                            </a>
                                            <p class="text-xs text-zinc-500">Enviado por {{ $att->user->name ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ is_null($att->is_valid) ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' : ($att->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $att->status_label }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-4">Nenhum comprovante anexado.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Histórico de Aprovações --}}
                    @if($orcamento->routeBillingApprovals->count())
                        <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Histórico de Decisões</h4>
                            </div>
                            <div class="bg-white dark:bg-zinc-900 p-4 space-y-2 max-h-40 overflow-y-auto">
                                @foreach($orcamento->routeBillingApprovals->sortByDesc('created_at') as $app)
                                    <div class="flex items-start gap-2 text-xs">
                                        <span class="px-1.5 py-0.5 rounded font-bold
                                            {{ $app->status === 'approved' ? 'bg-green-100 text-green-800' : ($app->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ match($app->status) { 'approved' => 'Aprovado', 'restrictions' => 'Restrição', 'rejected' => 'Negado', default => $app->status } }}
                                        </span>
                                        <div>
                                            <p class="text-zinc-600 dark:text-zinc-400">{{ $app->user->name ?? '—' }} — {{ $app->created_at->format('d/m H:i') }}</p>
                                            @if($app->comments)
                                                <p class="italic text-zinc-500">{{ $app->comments }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ─ Coluna direita: formas de pagamento + decisão financeiro ─────── --}}
                <div class="space-y-6">

                    {{-- Crédito do Cliente --}}
                    @if ($saldoDisponivel > 0)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <x-heroicon-o-gift class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                    <div>
                                        <h4 class="font-semibold text-blue-900 dark:text-blue-200">Crédito Disponível: R$ {{ number_format($saldoDisponivel, 2, ',', '.') }}</h4>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="abaterCredito" class="sr-only peer" id="abaterCreditoRota">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Abater</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Formas de Pagamento --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Formas de Pagamento</h3>
                            <button type="button" wire:click="adicionarFormaPagamento"
                                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-colors text-sm font-medium">
                                <x-heroicon-o-plus class="w-4 h-4" />
                                Adicionar
                            </button>
                        </div>
                        <div class="space-y-3">
                            @foreach ($formasPagamento as $index => $forma)
                                <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden" wire:key="forma-rota-{{ $index }}">
                                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma #{{ $index + 1 }}</span>
                                        @if (count($formasPagamento) > 1)
                                            <button type="button" wire:click="removerFormaPagamento({{ $index }})"
                                                    class="text-red-500 hover:text-red-700 text-sm flex items-center gap-1">
                                                <x-heroicon-o-trash class="w-4 h-4" /> Remover
                                            </button>
                                        @endif
                                    </div>
                                    <div class="bg-white dark:bg-zinc-900 p-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Método *</label>
                                                <select wire:model.live="formasPagamento.{{ $index }}.condicao_id"
                                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="">Selecione...</option>
                                                    @foreach ($condicoesPagamento as $cond)
                                                        <option value="{{ $cond->id }}">{{ $cond->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Valor *</label>
                                                <input type="number" wire:model.live="formasPagamento.{{ $index }}.valor"
                                                       class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                                       step="0.01" min="0" placeholder="0,00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Valor Restante --}}
                            @if ($valorComDesconto - ($valorPago + $valorCreditoAbatido) > 0.01)
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Valor Restante</p>
                                        <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                            R$ {{ number_format($valorComDesconto - ($valorPago + $valorCreditoAbatido), 2, ',', '.') }}
                                        </p>
                                    </div>
                                    <button type="button" wire:click="preencherRestante"
                                            class="flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <x-heroicon-o-sparkles class="w-4 h-4" />
                                        Preencher
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Resumo do Pagamento --}}
                    <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-indigo-600 dark:bg-indigo-700 px-4 py-3 border-b border-indigo-700">
                            <h4 class="text-sm font-semibold text-white">Resumo</h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4 space-y-2 text-sm">
                            <div class="flex justify-between py-2">
                                <span class="text-gray-700 dark:text-gray-300">Valor a Pagar:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                    R$ {{ number_format($valorComDesconto, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-700 dark:text-gray-300">Total Pago:</span>
                                <span class="font-bold {{ ($valorPago + $valorCreditoAbatido) >= $valorComDesconto ? 'text-green-600' : 'text-red-600' }}">
                                    R$ {{ number_format($valorPago + $valorCreditoAbatido, 2, ',', '.') }}
                                </span>
                            </div>
                            @if ($troco > 0)
                                <div class="flex justify-between py-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Troco:</span>
                                    <span class="font-bold text-green-600">R$ {{ number_format($troco, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Decisão do Financeiro --}}
                    <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <x-heroicon-o-shield-check class="w-4 h-4 text-indigo-500" />
                                Decisão do Financeiro
                            </h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Resultado *</label>
                                <select wire:model.live="billingStatus"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                                    <option value="approved">✅ Aprovar Faturamento</option>
                                    <option value="restrictions">⚠️ Aprovar com Restrição (Receber na Entrega)</option>
                                    <option value="rejected">❌ Negar / Cancelar Faturamento</option>
                                </select>
                            </div>
                            @if($billingStatus === 'restrictions')
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-sm text-amber-700 dark:text-amber-300">
                                    <strong>Atenção:</strong> O PDF será gerado com o aviso <strong>"RECEBER PAGAMENTO NA ENTREGA"</strong>.
                                </div>
                            @endif
                            @if($billingStatus === 'rejected')
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 text-sm text-red-700 dark:text-red-300">
                                    O Vendedor, Supervisor de Vendas, Separação e Conferência serão notificados imediatamente.
                                </div>
                            @endif
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Comentários / Justificativa</label>
                                <textarea wire:model="billingComments" rows="3"
                                          class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                                          placeholder="Justificativa da decisão..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="flex gap-4">
                        <a href="{{ route('orcamentos.rota_pagamento_lista') }}" class="flex-1">
                            <button type="button" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-3 rounded-lg transition-colors">
                                Voltar
                            </button>
                        </a>
                        <button type="button"
                                wire:click="finalizarPagamento"
                                wire:loading.attr="disabled"
                                @if($billingStatus !== 'rejected' && ($valorPago + $valorCreditoAbatido) < $valorComDesconto) disabled @endif
                                class="flex-1 flex items-center justify-center gap-2 font-medium px-6 py-3 rounded-lg transition-colors shadow-sm
                                    {{ $billingStatus === 'rejected' ? 'bg-red-600 hover:bg-red-700 text-white' : (($billingStatus !== 'rejected' && ($valorPago + $valorCreditoAbatido) >= $valorComDesconto) ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed') }}
                                    disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="finalizarPagamento" class="flex items-center gap-2">
                                <x-heroicon-o-check-circle class="w-5 h-5" />
                                @if($billingStatus === 'rejected') Confirmar Negação @else Confirmar Faturamento @endif
                            </span>
                            <span wire:loading wire:target="finalizarPagamento" class="flex items-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processando...
                            </span>
                        </button>
                    </div>
                </div>

            </div>{{-- end grid --}}
        </div>
    </div>
</div>
