<div class="p-6 max-w-5xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
            <a href="{{ route('quality.dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Qualidade</a>
            <flux:icon icon="chevron-right" class="w-3 h-3" />
            <span>Aprovar Devolução</span>
        </div>
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Aprovação de Devolução #{{ $return->nr }}</h1>
            <div class="px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wider
                @if($return->status === 'pendente_supervisor') bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400
                @elseif($return->status === 'pendente_estoque') bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400
                @elseif($return->status === 'finalizado') bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400
                @else bg-rose-100 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 @endif">
                {{ $return->status_label }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detalhes da Devolução --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card class="p-0 overflow-hidden">
                <div class="p-4 border-b bg-zinc-50 dark:bg-zinc-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white">Itens da Devolução</h3>
                    <span class="text-xs text-zinc-500 uppercase tracking-tighter">Budget Orignal: #{{ $return->orcamento_id }}</span>
                </div>
                
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Produto</th>
                            <th class="px-4 py-3 text-right">Quantidade</th>
                            <th class="px-4 py-3 text-right">Preço Un.</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($return->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $item->produto->nome ?? 'Produto não identificado' }}</div>
                                    <div class="text-xs text-zinc-500">Ref: {{ $item->produto->referencia ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-white">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/50 font-bold border-t">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right uppercase text-xs tracking-widest text-zinc-500">Valor Total do Crédito:</td>
                            <td class="px-4 py-4 text-right text-lg text-indigo-600 dark:text-indigo-400">R$ {{ number_format($return->valor_total_credito, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </flux:card>

            {{-- Histórico de Aprovações --}}
            @if ($return->authorizations->count() > 0)
                <div class="space-y-4">
                    <h3 class="font-bold text-zinc-900 dark:text-white ml-2">Histórico de Decisões</h3>
                    @foreach ($return->authorizations as $auth)
                        <flux:card class="p-4 border-l-4 {{ $auth->status === 'aprovado' ? 'border-l-emerald-500' : 'border-l-rose-500' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-400">{{ $auth->role === 'supervisor' ? 'Supervisor de Vendas' : 'Chefia de Estoque' }}</span>
                                    <p class="font-medium mt-1">{{ $auth->user->name }}</p>
                                </div>
                                <div class="text-right text-xs text-zinc-500">
                                    {{ $auth->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            @if ($auth->observacoes)
                                <div class="mt-2 p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded text-sm italic">
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
            <flux:card class="p-6 space-y-6 sticky top-6">
                <h3 class="font-bold text-xl text-zinc-900 dark:text-white">Ação Necessária</h3>
                
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-black/20 border border-zinc-100 dark:border-zinc-800 text-sm space-y-3">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Cliente:</span>
                        <span class="font-medium">{{ $return->cliente->nome }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Vendedor:</span>
                        <span class="font-medium">{{ $return->vendedor->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Ocorrência:</span>
                        <span class="font-medium">{{ $return->data_ocorrencia->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Solicitado por:</span>
                        <span class="font-medium">{{ $return->usuario->name }}</span>
                    </div>
                </div>

                @if (in_array($return->status, ['pendente_supervisor', 'pendente_estoque']))
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Observações / Motivo da Decisão</flux:label>
                            <flux:textarea wire:model="observacoes" rows="4" placeholder="Descreva o motivo da aprovação ou recusa..." />
                            <flux:error name="observacoes" />
                        </flux:field>

                        @if ($return->status === 'pendente_estoque')
                            <flux:card class="p-4 bg-indigo-50/30 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/50">
                                <flux:checkbox 
                                    wire:model="retorno_estoque" 
                                    label="Os itens retornaram ao estoque?" 
                                    description="Se marcado, a quantidade será somada ao estoque atual automaticamente." 
                                />
                            </flux:card>
                        @endif

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <flux:button wire:click="reject" variant="danger" icon="x-mark">Recusar</flux:button>
                            <flux:button wire:click="approve" variant="primary" icon="check">Aprovar</flux:button>
                        </div>
                    </div>
                @else
                    <div class="py-4 text-center text-zinc-500 space-y-4">
                        <flux:icon icon="lock-closed" class="w-12 h-12 mx-auto opacity-20" />
                        <p>Este processo de devolução foi <strong>{{ $return->status_label }}</strong> e não aceita mais alterações.</p>
                        
                        <div class="pt-4 flex flex-col gap-2">
                            <flux:button variant="ghost" size="sm" icon="document-text">Ver PDF Solicitação</flux:button>
                            @if ($return->status === 'finalizado')
                                <flux:button variant="ghost" size="sm" icon="check-badge">Ver PDF Autorização</flux:button>
                                @if ($return->troca_produto)
                                    <flux:button variant="ghost" size="sm" icon="truck">Ver Romaneio de Troca</flux:button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </flux:card>

            @if ($return->observacoes)
                <flux:card class="p-6">
                    <h3 class="font-bold text-zinc-500 uppercase text-xs tracking-widest mb-3">Observação Original</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 italic">"{{ $return->observacoes }}"</p>
                </flux:card>
            @endif
        </div>
    </div>
</div>
