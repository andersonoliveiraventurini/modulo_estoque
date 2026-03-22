<div class="p-6 max-w-5xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
            <a href="{{ route('quality.dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Qualidade</a>
            <flux:icon icon="chevron-right" class="w-3 h-3" />
            <span>Aprovar Devolução</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white flex items-center gap-3">
                    <flux:icon icon="arrow-path" class="w-8 h-8 text-indigo-500" />
                    Aprovação de Devolução #{{ $return->nr }}
                </h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1">Revise os itens e as decisões anteriores antes de prosseguir.</p>
            </div>
            <div class="px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wider shadow-sm
                @if($return->status === 'pendente_supervisor') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                @elseif($return->status === 'pendente_estoque') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                @elseif($return->status === 'finalizado') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                @else bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800 @endif">
                {{ $return->status_label }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Detalhes da Devolução --}}
        <div class="lg:col-span-2 space-y-8">
            <flux:card class="p-0 overflow-hidden shadow-md">
                <div class="p-5 border-b bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white flex items-center gap-2 text-lg">
                        <flux:icon icon="shopping-bag" class="w-5 h-5 text-zinc-400" />
                        Itens da Devolução
                    </h3>
                    <div class="flex items-center gap-2 px-3 py-1 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase">Orçamento Original</span>
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ $return->orcamento_id }}</span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-500 uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Produto</th>
                                <th class="px-6 py-4 text-right">Qtd</th>
                                <th class="px-6 py-4 text-right">Preço Un.</th>
                                <th class="px-6 py-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($return->items as $item)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-zinc-900 dark:text-white text-base">{{ $item->produto->nome ?? 'Produto não identificado' }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 rounded text-[10px] uppercase font-bold tracking-tighter">Ref: {{ $item->produto->referencia ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right text-zinc-500">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-zinc-900 dark:text-white">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50/30 dark:bg-zinc-800/30 border-t border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-right uppercase text-xs font-bold tracking-widest text-zinc-500">Valor Total do Crédito:</td>
                                <td class="px-6 py-6 text-right">
                                    <div class="inline-flex flex-col items-end">
                                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">R$ {{ number_format($return->valor_total_credito, 2, ',', '.') }}</span>
                                        <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-tighter mt-1">Crédito a ser gerado</span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </flux:card>

            {{-- Histórico de Aprovações --}}
            @if ($return->authorizations->count() > 0)
                <div class="space-y-4">
                    <h3 class="font-bold text-zinc-900 dark:text-white ml-2 flex items-center gap-2">
                        <flux:icon icon="chat-bubble-left-right" class="w-5 h-5 text-zinc-400" />
                        Histórico de Decisões
                    </h3>
                    @foreach ($return->authorizations as $auth)
                        <flux:card class="p-6 border-l-4 shadow-sm {{ $auth->status === 'aprovado' ? 'border-l-emerald-500' : 'border-l-rose-500' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-500">
                                        {{ substr($auth->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">{{ $auth->role === 'supervisor' ? 'Supervisor de Vendas' : 'Chefia de Estoque' }}</span>
                                            <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                            <span class="text-[10px] font-bold uppercase tracking-widest {{ $auth->status === 'aprovado' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $auth->status }}</span>
                                        </div>
                                        <p class="font-bold text-zinc-900 dark:text-white mt-0.5">{{ $auth->user->name }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <span class="text-xs font-medium text-zinc-500">{{ $auth->created_at->format('d M, Y') }}</span>
                                    <span class="text-[10px] text-zinc-400 font-bold">{{ $auth->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                            @if ($auth->observacoes)
                                <div class="mt-4 p-4 bg-zinc-50 dark:bg-zinc-800/30 rounded-xl text-sm italic text-zinc-600 dark:text-zinc-400 border border-zinc-100 dark:border-zinc-800 relative">
                                    <flux:icon icon="chat-bubble-bottom-center-text" class="w-4 h-4 absolute -top-2 -left-2 text-zinc-300" />
                                    "{{ $auth->observacoes }}"
                                </div>
                            @endif
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Painel de Ações --}}
        <div class="space-y-6">
            <flux:card class="p-6 space-y-6 sticky top-6 shadow-lg border-t-4 border-t-indigo-500">
                <h3 class="font-bold text-xl text-zinc-900 dark:text-white flex items-center gap-2">
                    <flux:icon icon="shield-check" class="w-6 h-6 text-indigo-500" />
                    Ação Necessária
                </h3>
                
                <div class="space-y-4">
                    <div class="div p-1.5 bg-zinc-50 dark:bg-black/20 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div class="p-3 space-y-3">
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <flux:icon icon="user" class="w-4 h-4 text-zinc-400" />
                                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-tighter">Cliente</span>
                                </div>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $return->cliente->nome }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon icon="identification" class="w-4 h-4 text-zinc-400" />
                                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-tighter">Vendedor</span>
                                </div>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $return->vendedor->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon icon="calendar" class="w-4 h-4 text-zinc-400" />
                                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-tighter">Ocorrência</span>
                                </div>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $return->data_ocorrencia->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon icon="pencil" class="w-4 h-4 text-zinc-400" />
                                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-tighter">Solicitado por</span>
                                </div>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $return->usuario->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if (in_array($return->status, ['pendente_supervisor', 'pendente_estoque']))
                    <div class="space-y-5 pt-2">
                        <flux:field>
                            <flux:label class="font-bold text-xs uppercase tracking-widest text-zinc-500 mb-2">Observações / Motivo da Decisão</flux:label>
                            <flux:textarea wire:model="observacoes" rows="4" placeholder="Descreva o motivo da sua decisão..." class="rounded-xl" />
                            <flux:error name="observacoes" />
                        </flux:field>

                        @if ($return->status === 'pendente_estoque')
                            <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-100 dark:border-indigo-900/50 shadow-sm">
                                <flux:checkbox 
                                    wire:model="retorno_estoque" 
                                    label="Confirmar retorno ao estoque?" 
                                    description="Aumenta automaticamente o saldo físico do produto disponível." 
                                />
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <flux:button wire:click="reject" variant="danger" icon="x-mark" class="py-3 shadow-md hover:shadow-lg transition-all">Recusar</flux:button>
                            <flux:button wire:click="approve" variant="primary" icon="check" class="py-3 shadow-md hover:shadow-lg transition-all">Aprovar</flux:button>
                        </div>
                    </div>
                @else
                    <div class="py-10 text-center space-y-6">
                        <div class="relative inline-block">
                            <flux:icon icon="lock-closed" class="w-16 h-16 mx-auto text-zinc-200 dark:text-zinc-800" />
                            <div class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full p-1 border-2 border-white dark:border-zinc-900">
                                <flux:icon icon="check" class="w-3 h-3" />
                            </div>
                        </div>
                        <div class="space-y-2 px-4">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Processo Finalizado</p>
                            <p class="text-xs text-zinc-500">Este processo de devolução foi <strong>{{ strtolower($return->status_label) }}</strong> e está bloqueado para novas alterações.</p>
                        </div>
                        
                        <div class="pt-4 flex flex-col gap-3 px-4">
                            <flux:button variant="ghost" size="sm" icon="document-text" class="justify-start w-full">Ver PDF Solicitação</flux:button>
                            @if ($return->status === 'finalizado')
                                <flux:button variant="ghost" size="sm" icon="check-badge" class="justify-start w-full">Ver PDF Autorização</flux:button>
                                @if ($return->troca_produto)
                                    <flux:button variant="ghost" size="sm" icon="truck" class="justify-start w-full">Ver Romaneio de Troca</flux:button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </flux:card>

            @if ($return->observacoes)
                <flux:card class="p-6 border-dashed bg-zinc-50/30 dark:bg-zinc-800/10">
                    <h3 class="font-bold text-zinc-400 uppercase text-[10px] tracking-widest mb-3">Justificativa da Solicitação</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 italic font-medium leading-relaxed">"{{ $return->observacoes }}"</p>
                </flux:card>
            @endif
        </div>
    </div>
</div>
