<x-layouts.app :title="__('Gerenciar Orçamento #' . $orcamento->id)">
    <div class="flex flex-col gap-6">

        <!-- Cabeçalho -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Gerenciar Orçamento #{{ $orcamento->id }}
            </h2>

            <div class="flex flex-wrap justify-between items-center">
                <div>
                    <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? $orcamento->cliente->nome_fantasia }}
                    </p>
                    <p><strong>Vendedor Interno:</strong> {{ $orcamento->cliente->vendedor_interno ?? '—' }}</p>
                    <p><strong>Vendedor Externo:</strong> {{ $orcamento->cliente->vendedor_externo ?? '—' }}</p>
                    <p><strong>Data:</strong> {{ $orcamento->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="text-right">
                    <p><strong>Status:</strong></p>
                    <form action="{{ route('orcamentos.atualizar-status', $orcamento->id) }}" method="POST"
                        class="inline-flex gap-2">
                        @csrf
                        @method('PUT')
                        <select name="status" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            @foreach (['Aprovar desconto', 'Pendente', 'Aprovado', 'Cancelado', 'Rejeitado', 'Expirado'] as $s)
                                <option value="{{ $s }}" @selected($orcamento->status == $s)>{{ $s }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            Atualizar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Produtos -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h9M15.5 20V10" />
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
                            <th class="px-3 py-2 border">Subtotal c/ Desc.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($orcamento->itens as $item)
                            <tr>
                                <td class="px-3 py-2 border">{{ $item->produto_codigo }}</td>
                                <td class="px-3 py-2 border">{{ $item->nome }}</td>
                                <td class="px-3 py-2 border text-center">{{ $item->quantidade }}</td>
                                <td class="px-3 py-2 border">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                                </td>
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
        @if ($orcamento->vidros->isNotEmpty())
            <!-- Vidros e Esteiras -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Vidros / Esteiras
                </h3>

                <div class="overflow-x-auto">
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
                            @foreach ($orcamento->vidros as $vidro)
                                <tr>
                                    <td class="px-3 py-2 border">{{ $vidro->descricao }}</td>
                                    <td class="px-3 py-2 border text-center">{{ $vidro->quantidade }}</td>
                                    <td class="px-3 py-2 border">R$
                                        {{ number_format($vidro->preco_unitario, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 border">R$ {{ number_format($vidro->subtotal, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Transporte -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h18v13H3zM5 16h14v2H5zM8 20h8v1H8z" />
                </svg>
                Transporte
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><strong>Transportadora:</strong> {{ $orcamento->transporte->nome ?? '—' }}</div>
                <div><strong>Valor:</strong> R$ {{ number_format($orcamento->transporte_valor ?? 0, 2, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Descontos e Totais -->
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium mb-2">Descontos e Encargos</h4>
                <p><strong>Desconto Geral:</strong> {{ $orcamento->desconto }}%</p>
                <p><strong>Desconto Específico:</strong> R$
                    {{ number_format($orcamento->desconto_especifico, 2, ',', '.') }}</p>
                <p><strong>Guia de Recolhimento:</strong> R$
                    {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}</p>
            </div>
            <div>
                <h4 class="font-medium mb-2">Totais</h4>
                <p><strong>Subtotal:</strong> R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</p>
                <p><strong>Valor Final:</strong> <span class="text-green-600 font-semibold">R$
                        {{ number_format($orcamento->valor_final, 2, ',', '.') }}</span></p>
            </div>
        </div>

        <!-- Aprovação de desconto -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h4 class="font-semibold mb-2">Aprovação de Desconto</h4>
            <form action="{{ route('orcamentos.aprovar-desconto', $orcamento->id) }}" method="POST"
                class="flex gap-4 items-center">
                @csrf
                @method('PUT')
                <label for="desconto_aprovado" class="text-sm">Novo desconto aprovado (%)</label>
                <input type="number" name="desconto_aprovado" step="0.01"
                    value="{{ $orcamento->desconto_aprovado ?? 0 }}"
                    class="border border-gray-300 rounded px-3 py-2 w-24 text-center">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Aprovar
                </button>
            </form>
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
            <a href="{{ route('orcamentos.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Voltar
            </a>
            <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Excluir Orçamento
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
