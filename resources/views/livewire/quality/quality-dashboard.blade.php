<div class="space-y-6">
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Painel de Gestão - Qualidade & Devoluções') }}</flux:heading>
        <flux:subheading>{{ __('Acompanhe as não conformidades e processos de devolução em um só lugar.') }}</flux:subheading>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl dark:bg-green-900/20 dark:border-green-800/50 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Estatísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:card class="bg-primary-50/50 dark:bg-primary-900/10">
            <p class="text-xs text-zinc-500 font-bold uppercase">{{ __('Total RNCs') }}</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['total_rnc'] }}</p>
        </flux:card>

        <flux:card class="bg-orange-50/50 dark:bg-orange-900/10">
            <p class="text-xs text-orange-600 font-bold uppercase">{{ __('Aguardando Supervisor') }}</p>
            <p class="text-2xl font-bold mt-1 text-orange-700 dark:text-orange-400">{{ $stats['pending_supervisor'] }}</p>
        </flux:card>

        <flux:card class="bg-indigo-50/50 dark:bg-indigo-900/10">
            <p class="text-xs text-indigo-600 font-bold uppercase">{{ __('Aguardando Estoque') }}</p>
            <p class="text-2xl font-bold mt-1 text-indigo-700 dark:text-indigo-400">{{ $stats['pending_estoque'] }}</p>
        </flux:card>

        <flux:card class="bg-zinc-50/50 dark:bg-zinc-800/50">
            <p class="text-xs text-zinc-500 font-bold uppercase">{{ __('Total Devoluções') }}</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['total_returns'] }}</p>
        </flux:card>
    </div>

    <!-- Filtros -->
    <flux:card class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <flux:field>
                <flux:label>{{ __('Busca') }}</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('NR, Cliente, Orçamento...') }}" icon="magnifying-glass" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model.live="status">
                    <option value="">{{ __('Todos') }}</option>
                    <option value="pendente_supervisor">{{ __('Pendente Supervisor') }}</option>
                    <option value="pendente_estoque">{{ __('Pendente Estoque') }}</option>
                    <option value="finalizado">{{ __('Finalizado') }}</option>
                    <option value="negado">{{ __('Negado') }}</option>
                    <option value="em_troca">{{ __('Em Troca') }}</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Início') }}</flux:label>
                <flux:input type="date" wire:model.live="date_start" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Fim') }}</flux:label>
                <flux:input type="date" wire:model.live="date_end" />
            </flux:field>
        </div>
    </flux:card>

    <!-- Tabelas -->
    <div class="space-y-8">
        <!-- RNCs -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <flux:icon icon="document-text" variant="mini" class="text-zinc-400" />
                    {{ __('Não Conformidades (RNC)') }}
                </h3>
                <flux:button :href="route('rnc.create')" variant="primary" size="sm" icon="plus">{{ __('Nova RNC') }}</flux:button>
            </div>
            
            <flux:card class="p-0 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('NR') }}</flux:table.column>
                        <flux:table.column>{{ __('Produto') }}</flux:table.column>
                        <flux:table.column>{{ __('Fornecedor') }}</flux:table.column>
                        <flux:table.column>{{ __('Data') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Ações') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($rncs as $rnc)
                            <flux:table.row :key="$rnc->id">
                                <flux:table.cell class="font-bold">{{ $rnc->nr }}</flux:table.cell>
                                <flux:table.cell>{{ $rnc->produto_nome }}</flux:table.cell>
                                <flux:table.cell>{{ $rnc->fornecedor_nome }}</flux:table.cell>
                                <flux:table.cell>{{ $rnc->data_ocorrencia->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button icon="printer" variant="ghost" size="sm" wire:click="downloadRnc({{ $rnc->id }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
                    {{ $rncs->links() }}
                </div>
            </flux:card>
        </section>

        <!-- Devoluções -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="mini" class="text-zinc-400" />
                    {{ __('Processos de Devolução') }}
                </h3>
                <flux:button :href="route('product_returns.create')" variant="primary" size="sm" icon="plus">{{ __('Nova Devolução') }}</flux:button>
            </div>

            <flux:card class="p-0 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('NR') }}</flux:table.column>
                        <flux:table.column>{{ __('Orçamento') }}</flux:table.column>
                        <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Pendente') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Ações') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($returns as $ret)
                            <flux:table.row :key="$ret->id">
                                <flux:table.cell class="font-bold">{{ $ret->nr }}</flux:table.cell>
                                <flux:table.cell>#{{ $ret->orcamento_id }}</flux:table.cell>
                                <flux:table.cell>{{ $ret->cliente->nome }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $color = match($ret->status) {
                                            'pendente_supervisor' => 'orange',
                                            'pendente_estoque' => 'indigo',
                                            'finalizado' => 'green',
                                            'negado' => 'red',
                                            'em_troca' => 'blue',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge :color="$color" size="sm">{{ $ret->status_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="text-[10px] text-zinc-500 font-medium">
                                        @if($ret->status === 'pendente_supervisor')
                                            {{ __('Aprovação Supervisor') }}
                                        @elseif($ret->status === 'pendente_estoque')
                                            {{ __('Aprovação Estoque') }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <div class="flex justify-end gap-1">
                                        <!-- Sempre mostrar Visualizar -->
                                        <flux:button icon="eye" variant="ghost" size="sm" :href="route('product_returns.approve', $ret->id)" tooltip="{{ __('Visualizar Detalhes') }}" />

                                        @if($ret->status === 'pendente_supervisor' || $ret->status === 'pendente_estoque')
                                            <!-- Botão de Avaliar/Aprovar (com confirmação) -->
                                            <flux:modal.trigger name="confirm-evaluate-{{ $ret->id }}">
                                                <flux:button icon="check-badge" variant="ghost" size="sm" color="green" tooltip="{{ __('Avaliar / Aprovar') }}" />
                                            </flux:modal.trigger>

                                            <flux:modal name="confirm-evaluate-{{ $ret->id }}" class="md:w-[450px] space-y-6">
                                                <div>
                                                    <flux:heading size="lg">{{ __('Iniciar Avaliação?') }}</flux:heading>
                                                    <flux:subheading>
                                                        {{ __('Você será redirecionado para a tela de decisão para o processo ') }} <b>{{ $ret->nr }}</b>.
                                                    </flux:subheading>
                                                </div>

                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                                                    </flux:modal.close>
                                                    <flux:button :href="route('product_returns.approve', $ret->id)" variant="primary" color="green">
                                                        {{ __('Sim, Avaliar') }}
                                                    </flux:button>
                                                </div>
                                            </flux:modal>
                                            
                                            <!-- Botão de Negar (com confirmação rápida) -->
                                            <flux:modal.trigger name="confirm-deny-{{ $ret->id }}">
                                                <flux:button icon="x-circle" variant="ghost" size="sm" color="red" tooltip="{{ __('Negar Solicitação') }}" />
                                            </flux:modal.trigger>

                                            <flux:modal name="confirm-deny-{{ $ret->id }}" class="md:w-[450px] space-y-6">
                                                <div>
                                                    <flux:heading size="lg">{{ __('Confirmar Início de Negação?') }}</flux:heading>
                                                    <flux:subheading>
                                                        {{ __('Você será redirecionado para a tela de avaliação para inserir a justificativa de negação para o processo ') }} <b>{{ $ret->nr }}</b>.
                                                    </flux:subheading>
                                                </div>

                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                                                    </flux:modal.close>
                                                    <flux:button :href="route('product_returns.approve', ['return' => $ret->id, 'action' => 'deny'])" variant="primary" color="red">
                                                        {{ __('Sim, Prosseguir') }}
                                                    </flux:button>
                                                </div>
                                            </flux:modal>
                                        @endif
                                        
                                        <flux:button icon="printer" variant="ghost" size="sm" wire:click="downloadReturn({{ $ret->id }}, 'solicited')" tooltip="{{ __('Romaneio Solicitação') }}" />
                                        
                                        @if($ret->status === 'finalizado')
                                            <flux:button icon="printer" variant="ghost" size="sm" wire:click="downloadReturn({{ $ret->id }}, 'authorized')" tooltip="{{ __('Comprovante Devolução') }}" />
                                        @elseif($ret->status === 'em_troca')
                                            <flux:button icon="printer" variant="ghost" size="sm" wire:click="downloadReturn({{ $ret->id }}, 'exchange')" tooltip="{{ __('Romaneio Troca') }}" />
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
                    {{ $returns->links() }}
                </div>
            </flux:card>
        </section>
    </div>
</div>
