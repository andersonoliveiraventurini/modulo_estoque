<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl" level="1">Detalhes do Estorno #{{ $estorno->id }}</flux:heading>
        <flux:button href="{{ route('estornos.index') }}" variant="subtle" icon="arrow-left">Voltar</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Card de Resumo --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase text-zinc-500 mb-4 tracking-wider">Informações Principais</h3>
            
            <div class="space-y-4">
                <div>
                    <span class="block text-xs font-semibold text-zinc-500 uppercase">Status</span>
                    <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $estorno->status === 'aprovado' ? 'green' : ($estorno->status === 'rejeitado' ? 'red' : ($estorno->status === 'concluido' ? 'blue' : 'yellow')) }}-100 text-{{ $estorno->status === 'aprovado' ? 'green' : ($estorno->status === 'rejeitado' ? 'red' : ($estorno->status === 'concluido' ? 'blue' : 'yellow')) }}-800 dark:bg-{{ $estorno->status === 'aprovado' ? 'green' : ($estorno->status === 'rejeitado' ? 'red' : ($estorno->status === 'concluido' ? 'blue' : 'yellow')) }}-900/30 dark:text-{{ $estorno->status === 'aprovado' ? 'green' : ($estorno->status === 'rejeitado' ? 'red' : ($estorno->status === 'concluido' ? 'blue' : 'yellow')) }}-400 capitalize">
                        {{ $estorno->status }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-zinc-500 uppercase">Valor do Estorno</span>
                    <span class="block text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">R$ {{ number_format($estorno->valor, 2, ',', '.') }}</span>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-zinc-500 uppercase">Forma Solicitada</span>
                    <span class="block text-sm text-zinc-800 dark:text-zinc-200 mt-1 uppercase">{{ $estorno->forma_estorno }} {{ $estorno->forma_estorno_detalhe ? "- {$estorno->forma_estorno_detalhe}" : '' }}</span>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-zinc-500 uppercase">Vínculo</span>
                    <span class="block text-sm text-zinc-800 dark:text-zinc-200 mt-1">
                        <a href="{{ route('pagamentos.show', $estorno->pagamento_id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 underline">
                            Pagamento #{{ $estorno->pagamento_id }}
                        </a>
                    </span>
                </div>
            </div>
        </div>

        {{-- Log da Avaliação --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase text-zinc-500 mb-4 tracking-wider">Histórico e Avaliação</h3>
            
            <div class="space-y-6">
                {{-- Solicitante --}}
                <div class="relative pl-6 border-l-2 border-zinc-200 dark:border-zinc-700">
                    <div class="absolute w-3 h-3 bg-zinc-400 rounded-full -left-[7px] top-1"></div>
                    <span class="block text-xs font-semibold text-zinc-500 uppercase">Solicitação ({{ $estorno->created_at->format('d/m/Y H:i') }})</span>
                    <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-1">Por: {{ $estorno->solicitante->name }}</span>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <strong>Motivo alegado:</strong> {{ $estorno->motivo }}
                    </p>
                </div>

                {{-- Decisão --}}
                @if($estorno->status !== 'pendente')
                    <div class="relative pl-6 border-l-2 border-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-500">
                        <div class="absolute w-3 h-3 bg-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-500 rounded-full -left-[7px] top-1"></div>
                        <span class="block text-xs font-semibold text-zinc-500 uppercase">
                            Avaliação ({{ \Carbon\Carbon::parse($estorno->aprovado_em)->format('d/m/Y H:i') }})
                        </span>
                        
                        <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-1">
                            Avaliador: {{ $estorno->aprovador->name ?? 'Sistema' }}
                        </span>

                        @if($estorno->observacao_aprovador)
                            <div class="mt-2 p-3 bg-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-50 dark:bg-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-900/20 text-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-800 dark:text-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-200 rounded-lg border border-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-200 dark:border-{{ $estorno->status === 'aprovado' || $estorno->status === 'concluido' ? 'green' : 'red' }}-800 text-sm">
                                <strong>Parecer:</strong> {{ $estorno->observacao_aprovador }}
                            </div>
                        @else
                            <div class="mt-2 p-3 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-600 dark:text-zinc-400 rounded-lg text-sm italic">
                                Sem observações adicionais.
                            </div>
                        @endif
                    </div>
                @endif
                
                {{-- Conclusão --}}
                @if($estorno->status === 'concluido')
                    <div class="relative pl-6 border-l-2 border-blue-500">
                        <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px] top-1"></div>
                        <span class="block text-xs font-semibold text-zinc-500 uppercase">
                            Conclusão do Repasse ({{ \Carbon\Carbon::parse($estorno->concluido_em)->format('d/m/Y H:i') }})
                        </span>
                        <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mt-1">O valor foi efetivamente estornado ou ressarcido ao cliente.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
