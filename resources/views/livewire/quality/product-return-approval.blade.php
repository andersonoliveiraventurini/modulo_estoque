<div class="space-y-6">
    <div class="mb-6">
        <flux:heading size="xl">
            {{ ($return->status === 'pendente_supervisor' || $return->status === 'pendente_estoque') ? __('Autorização de Devolução') : __('Detalhes da Devolução') }} #{{ $return->nr }}
        </flux:heading>
        <flux:subheading>
            @if($return->status === 'pendente_supervisor')
                {{ __('Etapa 1/2: Autorização do Supervisor de Vendas') }}
            @elseif($return->status === 'pendente_estoque')
                {{ __('Etapa 2/2: Autorização do Chefe do Estoque') }}
            @endif
        </flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Detalhes da Solicitação -->
        <div class="md:col-span-2 space-y-6">
            <flux:card>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg">{{ __('Dados do Pedido Original') }}</h3>
                    <flux:badge color="zinc">#{{ $return->orcamento_id }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div class="space-y-1">
                        <p class="text-zinc-500 font-bold text-[10px] uppercase">{{ __('Cliente') }}</p>
                        <p class="font-medium">{{ $return->cliente->nome }}</p>
                        <p class="text-xs text-zinc-400 font-mono">{{ $return->cliente->cnpj ?? $return->cliente->cpf }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-zinc-500 font-bold text-[10px] uppercase">{{ __('Vendedor') }}</p>
                        <p class="font-medium">{{ $return->vendedor->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-8 border-t border-zinc-100 dark:border-zinc-700 pt-6">
                    <h4 class="font-bold text-sm mb-4">{{ __('Itens Solicitados para Devolução') }}</h4>
                    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Produto') }}</th>
                                    <th class="px-3 py-2 text-center">{{ __('Qtd') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                                @foreach($return->items as $item)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="font-medium">{{ $item->produto->nome ?? __('Produto não identificado') }}</div>
                                            <div class="text-[9px] text-zinc-400">{{ $item->produto->sku ?? '---' }}</div>
                                        </td>
                                        <td class="px-3 py-2 text-center">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-zinc-50 dark:bg-zinc-800 font-bold">
                                <tr>
                                    <td colspan="2" class="px-3 py-2 text-right uppercase text-[9px]">{{ __('Total de Crédito Previsto:') }}</td>
                                    <td class="px-3 py-2 text-right text-primary-600">R$ {{ number_format($return->valor_total_credito, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-600">
                    <p class="text-[10px] text-zinc-500 font-bold uppercase mb-2">{{ __('Motivo da Solicitação') }}</p>
                    <p class="text-sm italic">"{{ $return->observacoes ?: __('Sem observações.') }}"</p>
                </div>
            </flux:card>

            <!-- Histórico de Autorizações -->
            @if($return->authorizations->count() > 0)
                <flux:card class="space-y-4">
                    <h3 class="font-bold text-lg">{{ __('Histórico de Autorizações') }}</h3>
                    <div class="space-y-3">
                        @foreach($return->authorizations as $auth)
                            <div class="flex items-start gap-3 p-3 rounded-lg {{ $auth->status === 'aprovado' ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10' }}">
                                <flux:icon icon="{{ $auth->status === 'aprovado' ? 'check-circle' : 'x-circle' }}" class="w-5 h-5 {{ $auth->status === 'aprovado' ? 'text-green-600' : 'text-red-600' }}" />
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-bold uppercase">{{ $auth->role }}</span>
                                        <span class="text-[10px] text-zinc-500">{{ $auth->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-xs font-medium">{{ $auth->user->name }}</p>
                                    @if($auth->observacoes)
                                        <p class="text-xs mt-1 text-zinc-600 dark:text-zinc-400 italic">"{{ $auth->observacoes }}"</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif
        </div>

        <!-- Formulário de Aprovação Atual (Apenas se Pendente) -->
        <div class="space-y-6">
            @if($return->status === 'pendente_supervisor' || $return->status === 'pendente_estoque')
                <form wire:submit="approve">
                    <flux:card class="space-y-6 border-2 {{ $return->status === 'pendente_supervisor' ? 'border-orange-200 dark:border-orange-900/30' : 'border-indigo-200 dark:border-indigo-900/30' }}">
                        <flux:heading size="lg">
                            {{ $return->status === 'pendente_supervisor' ? __('Decisão do Supervisor') : __('Decisão do Estoque') }}
                        </flux:heading>

                        <div class="space-y-4">
                            <flux:radio.group wire:model.live="status_approval" variant="cards" class="flex-col">
                                <flux:radio value="aprovado" label="{{ __('Autorizar Devolução') }}" description="{{ __('Deseja prosseguir com o processo.') }}" />
                                <flux:radio value="negado" label="{{ __('Negar Solicitação') }}" description="{{ __('O processo será encerrado imediatamente.') }}" />
                            </flux:radio.group>

                            @if($status_approval === 'aprovado' && $return->status === 'pendente_estoque')
                                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/10 rounded-xl space-y-3">
                                    <flux:field>
                                        <flux:label>{{ __('Retorno ao estoque físico?') }}</flux:label>
                                        <flux:checkbox wire:model="retorno_estoque" label="{{ __('Sim, o material está pronto para venda.') }}" />
                                        <flux:description>{{ __('Se marcado, a quantidade será somada ao produto automaticamente.') }}</flux:description>
                                    </flux:field>
                                    
                                    <div class="p-2 bg-yellow-100 text-yellow-800 text-[10px] rounded font-bold flex gap-2 items-center uppercase">
                                        <flux:icon icon="information-circle" variant="mini" />
                                        {{ __('A finalização gerará R$') }} {{ number_format($return->valor_total_credito, 2, ',', '.') }} {{ __(' de crédito para o cliente.') }}
                                    </div>
                                </div>
                            @endif

                            <flux:field>
                                <flux:label>{{ __('Observações / Justificativa') }}</flux:label>
                                <flux:textarea wire:model="observacoes" placeholder="{{ __('Opcional...') }}" rows="3" />
                            </flux:field>
                        </div>

                        <div class="pt-4 flex flex-col gap-2">
                            <flux:modal.trigger name="confirm-decision">
                                <flux:button variant="primary" icon="shield-check" :color="$status_approval === 'negado' ? 'red' : 'primary'" class="w-full">
                                    {{ $status_approval === 'negado' ? __('Negar Solicitação') : __('Autorizar Devolução') }}
                                </flux:button>
                            </flux:modal.trigger>

                            <flux:button href="{{ route('quality.dashboard') }}" variant="ghost">
                                {{ __('Voltar') }}
                            </flux:button>
                        </div>

                        <!-- Modal de Confirmação -->
                        <flux:modal name="confirm-decision" class="md:w-[450px] space-y-6">
                            <div>
                                <flux:heading size="lg">{{ $status_approval === 'negado' ? __('Confirmar Negação?') : __('Confirmar Aprovação?') }}</flux:heading>
                                <flux:subheading>
                                    {{ $status_approval === 'negado' 
                                        ? __('Tem certeza que deseja NEGAR esta devolução? Esta ação não pode ser desfeita.') 
                                        : __('Deseja confirmar a AUTORIZAÇÃO desta devolução e prosseguir com o processo?') }}
                                </flux:subheading>
                            </div>

                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                                </flux:modal.close>
                                <flux:button type="submit" variant="primary" :color="$status_approval === 'negado' ? 'red' : 'primary'">
                                    {{ __('Sim, Confirmar') }}
                                </flux:button>
                            </div>
                        </flux:modal>
                    </flux:card>
                </form>
            @else
                <flux:card class="space-y-6">
                    <div class="flex flex-col items-center text-center p-4">
                        @php
                            $statusInfo = match($return->status) {
                                'finalizado' => ['icon' => 'check-circle', 'color' => 'text-green-600', 'label' => 'Finalizado'],
                                'negado' => ['icon' => 'x-circle', 'color' => 'text-red-600', 'label' => 'Negado'],
                                'em_troca' => ['icon' => 'arrow-path', 'color' => 'text-blue-600', 'label' => 'Em Troca'],
                                default => ['icon' => 'information-circle', 'color' => 'text-zinc-600', 'label' => $return->status],
                            };
                        @endphp
                        <flux:icon :icon="$statusInfo['icon']" class="w-12 h-12 {{ $statusInfo['color'] }} mb-2" />
                        <flux:heading size="lg">{{ __('Solicitação') }} {{ $statusInfo['label'] }}</flux:heading>
                        <flux:subheading>{{ __('Este processo já foi concluído e não permite novas alterações.') }}</flux:subheading>
                    </div>

                    <div class="pt-4 flex flex-col gap-2">
                        @if($return->status === 'finalizado')
                            <flux:button wire:click="downloadReturn({{ $return->id }}, 'authorized')" variant="primary" icon="printer">
                                {{ __('Imprimir Comprovante') }}
                            </flux:button>
                        @endif
                        <flux:button href="{{ route('quality.dashboard') }}" variant="ghost">
                            {{ __('Voltar ao Painel') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>
</div>
