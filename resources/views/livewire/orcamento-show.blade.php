<div class="flex flex-col gap-6" x-data>
    <!-- Alerta de sucesso -->
    <div 
        x-data="{ show: false, message: '' }"
        x-on:notify.window="show = true; message = $event.detail; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
        <span x-text="message"></span>
    </div>

    <!-- Cabeçalho -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold mb-2">Orçamento #{{ $orcamento->id }}</h2>
                <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? $orcamento->cliente->nome_fantasia }}</p>
                <p><strong>Vendedor:</strong> {{ $orcamento->cliente->vendedor_interno ?? '—' }}</p>
                <p><strong>Data:</strong> {{ $orcamento->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="flex flex-col items-end gap-2">
                <span class="text-sm text-gray-500">Status atual:</span>
                <select wire:model="status" wire:change="atualizarStatus"
                        class="border border-gray-300 rounded px-2 py-1 text-sm bg-white dark:bg-zinc-800">
                    @foreach(['Em análise','Aprovado','Reprovado','Cancelado'] as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Produtos -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h9" />
            </svg>
            Produtos
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-2 border">Código</th>
                        <th class="px-3 py-2 border">Produto</th>
                        <th class="px-3 py-2 border">Qtd</th>
                        <th class="px-3 py-2 border">Preço Unit.</th>
                        <th class="px-3 py-2 border">Subtotal</th>
                        <th class="px-3 py-2 border">c/ Desconto</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($orcamento->itens as $item)
                        <tr>
                            <td class="px-3 py-2 border">{{ $item->produto_codigo }}</td>
                            <td class="px-3 py-2 border">{{ $item->nome }}</td>
                            <td class="px-3 py-2 border text-center">{{ $item->quantidade }}</td>
                            <td class="px-3 py-2 border">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 border">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 border text-green-600 font-medium">
                                R$ {{ number_format($item->subtotal_com_desconto, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vidros e Esteiras -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h3 class="text-lg font-semibold mb-3">Vidros / Esteiras</h3>
        @if($orcamento->vidros->count())
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-2 border">Descrição</th>
                        <th class="px-3 py-2 border">Qtd</th>
                        <th class="px-3 py-2 border">Preço Unit.</th>
                        <th class="px-3 py-2 border">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orcamento->vidros as $v)
                        <tr>
                            <td class="px-3 py-2 border">{{ $v->descricao }}</td>
                            <td class="px-3 py-2 border text-center">{{ $v->quantidade }}</td>
                            <td class="px-3 py-2 border">R$ {{ number_format($v->preco_unitario, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 border">R$ {{ number_format($v->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">Nenhum vidro adicionado.</p>
        @endif
    </div>

    <!-- Transporte -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h3 class="text-lg font-semibold mb-3">Transporte</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Transportadora:</strong> {{ $orcamento->transporte->nome ?? '—' }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($orcamento->transporte_valor ?? 0, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Totais -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-medium mb-2">Descontos e Encargos</h4>
            <p><strong>Desconto Geral:</strong> {{ $orcamento->desconto }}%</p>
            <p><strong>Desconto Específico:</strong> R$ {{ number_format($orcamento->desconto_especifico, 2, ',', '.') }}</p>
            <p><strong>Guia Recolhimento:</strong> R$ {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}</p>
        </div>
        <div>
            <h4 class="font-medium mb-2">Totais</h4>
            <p><strong>Subtotal:</strong> R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</p>
            <p><strong>Valor Final:</strong> 
                <span class="text-green-600 font-semibold">
                    R$ {{ number_format($orcamento->valor_final, 2, ',', '.') }}
                </span>
            </p>
        </div>
    </div>

    <!-- Aprovação de Desconto -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h4 class="font-semibold mb-2">Aprovar Desconto</h4>
        <div class="flex items-center gap-3">
            <input type="number" step="0.01" wire:model="desconto_aprovado" wire:change="aprovarDesconto"
                class="border border-gray-300 rounded px-3 py-2 w-24 text-center">
            <button wire:click="aprovarDesconto"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Salvar
            </button>
        </div>
    </div>

    <!-- Observações -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h4 class="font-medium mb-2">Observações</h4>
        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
            {{ $orcamento->observacoes ?? 'Nenhuma observação registrada.' }}
        </p>
    </div>

    <!-- Ações -->
    <div class="flex justify-between">
        <a href="{{ route('orcamentos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Voltar
        </a>
        <button wire:click="$emit('confirmDelete', {{ $orcamento->id }})"
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Excluir
        </button>
    </div>
</div>
