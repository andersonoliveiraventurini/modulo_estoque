<x-layouts.app :title="__('Detalhes da Venda')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('vendas.index') }}" />
                <flux:heading size="xl">Venda #{{ $venda->id }}</flux:heading>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $color = match($venda->status) {
                        'Efetuada', 'Paga' => 'lime',
                        'Cancelada' => 'red',
                        default => 'zinc'
                    };
                @endphp
                <flux:badge :color="$color" size="md" variant="solid">{{ $venda->status }}</flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:card>
                <flux:heading size="sm" class="mb-2">Informações Gerais</flux:heading>
                <div class="space-y-2 text-sm">
                    <p><strong>Data:</strong> {{ $venda->data_venda?->format('d/m/Y H:i') ?? '---' }}</p>
                    <p>
                        <strong>Orçamento:</strong> 
                        <a href="{{ route('orcamentos.gerenciar', $venda->orcamento_id) }}" class="text-accent hover:underline">
                            #{{ $venda->orcamento_id }}
                        </a>
                    </p>
                    <p><strong>Valor Total:</strong> R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</p>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">Cliente</flux:heading>
                <div class="space-y-2 text-sm">
                    <p><strong>Nome:</strong> {{ $venda->cliente?->nome ?? '---' }}</p>
                    <p><strong>CPF/CNPJ:</strong> {{ $venda->cliente?->cpf_cnpj ?? '---' }}</p>
                    <p><strong>Telefone:</strong> {{ $venda->cliente?->telefone ?? '---' }}</p>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">Vendedor</flux:heading>
                <div class="space-y-2 text-sm">
                    <p><strong>Nome:</strong> {{ $venda->vendedor?->name ?? '---' }}</p>
                    <p><strong>Email:</strong> {{ $venda->vendedor?->email ?? '---' }}</p>
                </div>
            </flux:card>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <flux:heading size="md" class="p-4 bg-neutral-50 dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700">
                Produtos
            </flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Código</flux:table.column>
                    <flux:table.column>Produto</flux:table.column>
                    <flux:table.column align="center">Qtd</flux:table.column>
                    <flux:table.column>Preço Unit.</flux:table.column>
                    <flux:table.column>Subtotal</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($venda->orcamento->itens as $item)
                        <flux:table.row :key="$item->id">
                            <flux:table.cell>{{ $item->produto_codigo }}</flux:table.cell>
                            <flux:table.cell>{{ $item->nome }}</flux:table.cell>
                            <flux:table.cell align="center">{{ $item->quantidade }}</flux:table.cell>
                            <flux:table.cell>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>R$ {{ number_format($item->subtotal_com_desconto, 2, ',', '.') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-4 text-neutral-500">
                                Nenhum produto nesta venda.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        @if($venda->orcamento->vidros->count() > 0)
            <flux:card class="p-0 overflow-hidden">
                <flux:heading size="md" class="p-4 bg-neutral-50 dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700">
                    Vidros / Esteiras
                </flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Descrição</flux:table.column>
                        <flux:table.column align="center">Qtd</flux:table.column>
                        <flux:table.column>Preço Unit.</flux:table.column>
                        <flux:table.column>Subtotal</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($venda->orcamento->vidros as $vidro)
                            <flux:table.row :key="$vidro->id">
                                <flux:table.cell>{{ $vidro->descricao }}</flux:table.cell>
                                <flux:table.cell align="center">{{ $vidro->quantidade }}</flux:table.cell>
                                <flux:table.cell>R$ {{ number_format($vidro->preco_unitario, 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>R$ {{ number_format($vidro->subtotal, 2, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif

        <flux:card class="p-0 overflow-hidden">
            <flux:heading size="md" class="p-4 bg-neutral-50 dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700">
                Pagamentos
            </flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Formas / Métodos</flux:table.column>
                    <flux:table.column>Valor Pago</flux:table.column>
                    <flux:table.column>Data</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($venda->orcamento->pagamentos as $pagamento)
                        <flux:table.row :key="$pagamento->id">
                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    @foreach($pagamento->formas as $forma)
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" variant="outline">{{ $forma->condicaoPagamento?->nome ?? '---' }}</flux:badge>
                                            <span class="text-xs text-neutral-500">R$ {{ number_format($forma->valor, 2, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="font-bold">R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>{{ $pagamento->data_pagamento?->format('d/m/Y H:i') ?? $pagamento->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center py-4 text-neutral-500">
                                Nenhum pagamento registrado.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

    </div>
</x-layouts.app>