<!-- Wrapper Principal -->
<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div
            class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

            @if (!$orcamento)
                <!-- Mensagem quando orçamento não está carregado -->
                <div class="text-center py-12">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-4">
                        <x-heroicon-o-exclamation-circle class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Orçamento não encontrado</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        O orçamento ID: <span class="font-semibold">{{ $orcamentoId ?? 'N/A' }}</span> não foi
                        localizado.
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
                        <x-heroicon-o-receipt-percent class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        Aprovação de Descontos - <a href="{{ route('orcamentos.show', $orcamento->id) }}">Orçamento
                            #{{ $orcamento->id }}</a>
                    </h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Revise e aprove ou rejeite cada desconto aplicado ao orçamento
                    </p>
                </div>

                <!-- Alertas de Erro -->
                @if ($errors->any())
                    <div
                        class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex gap-3">
                            <x-heroicon-o-exclamation-circle
                                class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Atenção aos seguintes
                                    erros:</h4>
                                <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4 mb-6">
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        Informações do Orçamento
                    </h3>

                    @php
                        $valorAtualOrcamento = $orcamento->valor_total_itens ?? 0;

                        $descontosAprovados = $orcamento->descontos()->whereNotNull('aprovado_em')->get();

                        $descontosProdutoAprovados = $descontosAprovados->where('tipo', 'produto');
                        $descontosOutrosAprovados = $descontosAprovados->whereIn('tipo', ['percentual', 'fixo']);

                        $totalDescontosProduto = $descontosProdutoAprovados->sum('valor');
                        $totalDescontosOutros = $descontosOutrosAprovados->sum('valor');

                        $valorOriginalEstimado = $valorAtualOrcamento + $totalDescontosProduto;
                        $valorFinalComDescontos = $valorAtualOrcamento - $totalDescontosOutros;
                    @endphp

                    <div
                        class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden">
                        <div
                            class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dados Gerais</h4>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <!-- Cliente -->
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-user class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $orcamento->cliente->nome ?? ($orcamento->cliente->nome_fantasia ?? 'N/A') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Vendedor -->
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-user-circle class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $orcamento->vendedor->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($totalDescontosProduto > 0)
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-o-currency-dollar
                                            class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Valor Original (antes dos descontos)
                                            </p>
                                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                                R$ {{ number_format($valorOriginalEstimado, 2, ',', '.') }}
                                            </p>
                                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                <x-heroicon-o-information-circle class="w-3 h-3 inline" />
                                                Inclui {{ $descontosProdutoAprovados->count() }} desconto(s) por produto
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @if ($totalDescontosProduto > 0)
                                                Valor Base (com desc. produto)
                                            @else
                                                Valor Total Original
                                            @endif
                                        </p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            R$ {{ number_format($valorAtualOrcamento, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                @if ($totalDescontosOutros > 0 || ($descontosAprovados->count() > 0 && $totalDescontosProduto > 0))
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-o-currency-dollar
                                            class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Valor Final com Descontos
                                            </p>
                                            <p class="text-lg font-bold text-green-700 dark:text-green-300">
                                                R$ {{ number_format($valorFinalComDescontos, 2, ',', '.') }}
                                            </p>
                                            @if ($totalDescontosOutros > 0)
                                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                    -R$ {{ number_format($totalDescontosOutros, 2, ',', '.') }}
                                                    em desc. percentual/fixo
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($descontosAprovados->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">
                                        💰 Composição dos Descontos Aprovados
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                                        @if ($totalDescontosProduto > 0)
                                            <div
                                                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <x-heroicon-o-cube
                                                        class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                                    <span class="text-xs font-medium text-blue-900 dark:text-blue-200">
                                                        Desconto por Produto
                                                    </span>
                                                </div>
                                                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                                    -R$ {{ number_format($totalDescontosProduto, 2, ',', '.') }}
                                                </p>
                                                <p class="text-xs text-blue-600 dark:text-blue-400">
                                                    {{ $descontosProdutoAprovados->count() }} item(ns)
                                                </p>
                                            </div>
                                        @endif

                                        @if ($totalDescontosOutros > 0)
                                            <div
                                                class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <x-heroicon-o-tag
                                                        class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                                                    <span
                                                        class="text-xs font-medium text-orange-900 dark:text-orange-200">
                                                        Outros Descontos
                                                    </span>
                                                </div>
                                                <p class="text-lg font-bold text-orange-700 dark:text-orange-300">
                                                    -R$ {{ number_format($totalDescontosOutros, 2, ',', '.') }}
                                                </p>
                                                <p class="text-xs text-orange-600 dark:text-orange-400">
                                                    {{ $descontosOutrosAprovados->count() }} desconto(s)
                                                </p>
                                            </div>
                                        @endif

                                        @if ($descontosAprovados->count() > 0)
                                            <div
                                                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <x-heroicon-o-arrow-trending-down
                                                        class="w-4 h-4 text-green-600 dark:text-green-400" />
                                                    <span
                                                        class="text-xs font-medium text-green-900 dark:text-green-200">
                                                        Economia Total
                                                    </span>
                                                </div>
                                                <p class="text-lg font-bold text-green-700 dark:text-green-300">
                                                    -R$
                                                    {{ number_format($totalDescontosProduto + $totalDescontosOutros, 2, ',', '.') }}
                                                </p>
                                                @if ($valorOriginalEstimado > 0)
                                                    <p class="text-xs text-green-600 dark:text-green-400">
                                                        {{ number_format((($totalDescontosProduto + $totalDescontosOutros) / $valorOriginalEstimado) * 100, 1) }}%
                                                        de desconto
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200 dark:border-gray-700" />

                <!-- Lista de Descontos para Aprovação -->
                <div class="space-y-4 mb-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-tag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            Descontos Pendentes de Aprovação
                        </h3>
                        <div class="flex items-center gap-2">
                            <span
                                class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-sm font-medium rounded-full">
                                {{ is_countable($descontos) ? count($descontos) : 0 }} desconto(s)
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($descontos as $index => $desconto)
                            <div class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden"
                                wire:key="desconto-{{ $desconto->id }}">

                                <!-- Header do Desconto -->
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 px-4 py-3 border-b border-blue-200 dark:border-blue-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="flex items-center justify-center w-8 h-8 bg-blue-600 dark:bg-blue-500 text-white text-sm font-bold rounded-full">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $desconto->motivo }}
                                                </h4>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    Solicitado por: {{ $desconto->user->name ?? 'Sistema' }} •
                                                    {{ $desconto->created_at->format('d/m/Y H:i') }}
                                                </p>
                                                @if ($desconto->tipo === 'produto' && $desconto->produto_id)
                                                    @php
                                                        $produtoHeader = $desconto->produto ?? \App\Models\Produto::find($desconto->produto_id);
                                                    @endphp
                                                    @if ($produtoHeader)
                                                        <p class="text-xs mt-1 inline-flex items-center gap-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full font-medium">
                                                            <x-heroicon-o-cube class="w-3 h-3" />
                                                            {{ $produtoHeader->nome ?? $produtoHeader->descricao ?? "Produto #{$desconto->produto_id}" }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full
                                            @if ($desconto->tipo === 'fixo') bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300
                                            @elseif($desconto->tipo === 'produto') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                                            @else bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                            @endif">
                                            @if ($desconto->tipo === 'fixo')
                                                Valor Fixo
                                            @elseif($desconto->tipo === 'produto')
                                                Desconto por Produto
                                            @else
                                                Percentual
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Corpo do Desconto -->
                                <div class="bg-white dark:bg-zinc-900 p-4">
                                    @php
                                        // -------------------------------------------------------
                                        // IDs de produtos que já têm desconto do tipo 'produto'
                                        // (tanto pendentes quanto aprovados) — esses itens NÃO
                                        // entram na base de cálculo do desconto percentual.
                                        // Usamos a relação já carregada para evitar queries extras.
                                        // -------------------------------------------------------

                                        // Carrega todos os descontos do orçamento uma vez (evita N+1)
                                        $todosDescontosOrcamento = $orcamento->descontos()->get();

                                        // IDs dos produtos com desconto tipo 'produto' ATIVO (não rejeitado)
                                        // Quando produto_id está NULL (bug de fillable em duplicações),
                                        // extrai o ID do campo motivo para garantir filtragem correta.
                                        $produtosComDescontoProduto = \App\Models\Desconto::where('orcamento_id', $orcamento->id)
                                            ->where('tipo', 'produto')
                                            ->whereNull('rejeitado_em')
                                            ->get(['produto_id', 'motivo'])
                                            ->map(function ($d) {
                                                if ($d->produto_id) {
                                                    return (int) $d->produto_id;
                                                }
                                                // Fallback: extrai do motivo "...produto ID 9969..."
                                                preg_match('/produto ID\s+(\d+)/i', $d->motivo ?? '', $m);
                                                return isset($m[1]) ? (int) $m[1] : null;
                                            })
                                            ->filter(fn($id) => !is_null($id) && $id > 0)
                                            ->unique()
                                            ->values()
                                            ->toArray();

                                        // Carrega todos os itens do orçamento com o produto relacionado
                                        $todosItensOrcamento = $orcamento->itens()->with('produto')->get();

                                        if ($desconto->tipo === 'produto') {
                                            // --------------------------------------------------
                                            // DESCONTO POR PRODUTO
                                            // produto_id pode estar NULL em descontos duplicados
                                            // com bug de fillable. Nesse caso, extrai do motivo.
                                            // --------------------------------------------------
                                            $produtoIdReal = $desconto->produto_id;

                                            if (!$produtoIdReal) {
                                                // Extrai ID do motivo: "Desconto individual X em unidades do produto ID 9969"
                                                preg_match('/produto ID\s+(\d+)/i', $desconto->motivo, $matches);
                                                $produtoIdReal = isset($matches[1]) ? (int) $matches[1] : null;
                                            }

                                            $itemOrcamento = $produtoIdReal
                                                ? $todosItensOrcamento->firstWhere('produto_id', $produtoIdReal)
                                                : null;

                                            $valorDesconto = (float) $desconto->valor;

                                            if ($itemOrcamento) {
                                                $quantidade           = (float) ($itemOrcamento->quantidade ?? 1);
                                                $valorOriginalProduto = (float) $itemOrcamento->valor_unitario * $quantidade;

                                                // valor_com_desconto confiável somente se > 0 e < original
                                                $valorComDescontoItem = (float) $itemOrcamento->valor_com_desconto;
                                                if ($valorComDescontoItem > 0 && $valorComDescontoItem < $valorOriginalProduto) {
                                                    $valorComDesconto = $valorComDescontoItem;
                                                } else {
                                                    // Reconstrói: original - desconto registrado
                                                    $valorComDesconto = $valorOriginalProduto - $valorDesconto;
                                                }
                                            } else {
                                                // Fallback total
                                                $valorOriginalProduto = $valorDesconto; // mínimo coerente
                                                $valorComDesconto     = 0;
                                            }

                                            // Enriquece o produto_id no desconto para uso no template
                                            // (ex: exibição do nome do produto no header)
                                            if (!$desconto->produto_id && $produtoIdReal) {
                                                $desconto->produto_id = $produtoIdReal;
                                            }

                                            // Garante que variáveis do branch percentual não fiquem indefinidas
                                            $valorOriginalBasePercentual = 0;
                                            $descontosJaAprovados        = 0;
                                            $valorAposDesconto           = 0;

                                        } else {
                                            // --------------------------------------------------
                                            // DESCONTO PERCENTUAL ou FIXO
                                            // Base = apenas itens SEM desconto individual de produto
                                            // --------------------------------------------------
                                            $itensSemDescontoProduto = $todosItensOrcamento
                                                ->reject(fn($item) => in_array((int) $item->produto_id, $produtosComDescontoProduto, true));

                                            $valorOriginalBasePercentual = $itensSemDescontoProduto
                                                ->sum(fn($item) => (float) $item->valor_unitario * (float) $item->quantidade);

                                            $descontosJaAprovados = $todosDescontosOrcamento
                                                ->whereNotNull('aprovado_em')
                                                ->whereIn('tipo', ['percentual', 'fixo'])
                                                ->sum('valor');

                                            $valorAposDesconto = $valorOriginalBasePercentual
                                                - (float) $descontosJaAprovados
                                                - (float) $desconto->valor;

                                            // Aliases para manter consistência no template
                                            $valorOriginalProduto = $valorOriginalBasePercentual;
                                            $valorComDesconto     = 0;
                                            $valorDesconto        = (float) $desconto->valor;
                                        }
                                    @endphp

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                                        <!-- Preço Original (somente para desconto por produto) -->
                                        @if ($desconto->tipo === 'produto')
                                            <div
                                                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-900/40 rounded-full">
                                                    <x-heroicon-o-tag
                                                        class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                        Preço Original (total item)</p>
                                                    <p class="text-lg font-bold text-gray-700 dark:text-gray-300">
                                                        R$ {{ number_format($valorOriginalProduto, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Preço unitário original (somente para percentual/fixo) -->
                                        @if ($desconto->tipo !== 'produto')
                                            @php
                                                // Tenta encontrar o item relacionado para exibir preço unitário
                                                // Para desconto percentual pode haver múltiplos itens elegíveis,
                                                // então exibimos o total original dos itens elegíveis
                                                $qtdItensElegiveis = $itensSemDescontoProduto->count();
                                            @endphp
                                            <div
                                                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-900/40 rounded-full">
                                                    <x-heroicon-o-tag
                                                        class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                        Total dos itens elegíveis</p>
                                                    <p class="text-lg font-bold text-gray-700 dark:text-gray-300">
                                                        R$ {{ number_format($valorOriginalBasePercentual, 2, ',', '.') }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $qtdItensElegiveis }} item(ns) sem desc. individual
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Valor do Desconto -->
                                        <div
                                            class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full">
                                                <x-heroicon-o-currency-dollar
                                                    class="w-5 h-5 text-red-600 dark:text-red-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                                                    @if ($desconto->tipo === 'produto')
                                                        Desconto Aplicado
                                                    @else
                                                        Valor do Desconto
                                                    @endif
                                                </p>
                                                <p class="text-lg font-bold text-red-700 dark:text-red-300">
                                                    - R$ {{ number_format($desconto->valor, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Percentual (se aplicável) -->
                                        @if ($desconto->tipo === 'percentual' && $desconto->porcentagem)
                                            <div
                                                class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full">
                                                    <x-heroicon-o-percent-badge
                                                        class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                                                        Percentual</p>
                                                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                                        {{ number_format($desconto->porcentagem, 2, ',', '.') }}%
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Produto (se aplicável) -->
                                        @if ($desconto->tipo === 'produto' && $desconto->produto_id)
                                            <div
                                                class="flex items-center gap-3 p-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-full">
                                                    <x-heroicon-o-cube
                                                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p
                                                        class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                                        Produto</p>
                                                    <p class="text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                                        {{ $desconto->produto->nome ?? "ID: {$desconto->produto_id}" }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Valor COM Desconto / Valor Após Desconto -->
                                        <div
                                            class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full">
                                                <x-heroicon-o-calculator
                                                    class="w-5 h-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-green-600 dark:text-green-400 font-medium">
                                                    @if ($desconto->tipo === 'produto')
                                                        Preço com Desconto (total item)
                                                    @else
                                                        Base Após Desconto
                                                    @endif
                                                </p>
                                                <p class="text-lg font-bold text-green-700 dark:text-green-300">
                                                    @if ($desconto->tipo === 'produto')
                                                        R$ {{ number_format($valorComDesconto, 2, ',', '.') }}
                                                    @else
                                                        R$ {{ number_format($valorAposDesconto, 2, ',', '.') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detalhamento visual para desconto por produto -->
                                    @if ($desconto->tipo === 'produto' && $desconto->produto)
                                        <div
                                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-full flex-shrink-0">
                                                    <x-heroicon-o-information-circle
                                                        class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p
                                                        class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">
                                                        💰 Cálculo do Desconto por Produto
                                                    </p>
                                                    <div class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
                                                        <div class="flex justify-between items-center py-1">
                                                            <span>Preço Original (total item):</span>
                                                            <span class="font-semibold">R$
                                                                {{ number_format($valorOriginalProduto, 2, ',', '.') }}</span>
                                                        </div>
                                                        <div
                                                            class="flex justify-between items-center py-1 border-t border-blue-200 dark:border-blue-700">
                                                            <span>Desconto (total item):</span>
                                                            <span
                                                                class="font-semibold text-red-600 dark:text-red-400">-
                                                                R$
                                                                {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                                                        </div>
                                                        <div
                                                            class="flex justify-between items-center py-2 border-t-2 border-blue-300 dark:border-blue-600">
                                                            <span class="font-bold">Preço Final (total item):</span>
                                                            <span
                                                                class="font-bold text-green-600 dark:text-green-400">R$
                                                                {{ number_format($valorComDesconto, 2, ',', '.') }}</span>
                                                        </div>
                                                        <div class="bg-blue-100 dark:bg-blue-900/40 rounded p-2 mt-2">
                                                            <p class="text-xs">
                                                                <strong>Percentual:</strong>
                                                                {{ $valorOriginalProduto > 0 ? number_format(($valorDesconto / $valorOriginalProduto) * 100, 2, ',', '.') : '0,00' }}%
                                                                de desconto aplicado
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Detalhamento visual para desconto percentual/fixo -->
                                    @if ($desconto->tipo !== 'produto')
                                        <div
                                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full flex-shrink-0">
                                                    <x-heroicon-o-information-circle
                                                        class="w-5 h-5 text-green-600 dark:text-green-400" />
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-green-900 dark:text-green-200 mb-2">
                                                        💰 Cálculo do Desconto {{ $desconto->tipo === 'percentual' ? 'Percentual' : 'Fixo' }}
                                                    </p>
                                                    <div class="space-y-2 text-sm text-green-800 dark:text-green-300">

                                                        @if (count($produtosComDescontoProduto) > 0)
                                                            <div class="flex justify-between items-center py-1">
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                    Itens excluídos (possuem desc. individual):
                                                                </span>
                                                                <span class="font-semibold text-gray-500 text-xs">
                                                                    {{ count($produtosComDescontoProduto) }} item(ns)
                                                                </span>
                                                            </div>
                                                        @endif

                                                        {{-- Linha por item elegível --}}
                                                        @foreach ($itensSemDescontoProduto as $itemEl)
                                                            @php
                                                                $subtotalItemEl = (float) $itemEl->valor_unitario * (float) $itemEl->quantidade;
                                                                $descontoItemEl = $desconto->tipo === 'percentual' && $desconto->porcentagem
                                                                    ? $subtotalItemEl * ($desconto->porcentagem / 100)
                                                                    : 0;
                                                                $subtotalComDescEl = $subtotalItemEl - $descontoItemEl;
                                                            @endphp
                                                            <div class="flex justify-between items-center py-1 border-t border-green-200 dark:border-green-700 text-xs">
                                                                <span class="text-gray-700 dark:text-gray-300">
                                                                    {{ $itemEl->produto->nome ?? "Produto #{$itemEl->produto_id}" }}
                                                                    <span class="text-gray-400">({{ (int) $itemEl->quantidade }}x R$ {{ number_format((float) $itemEl->valor_unitario, 2, ',', '.') }})</span>
                                                                </span>
                                                                <span class="font-semibold">
                                                                    R$ {{ number_format($subtotalItemEl, 2, ',', '.') }}
                                                                </span>
                                                            </div>
                                                        @endforeach

                                                        <div class="flex justify-between items-center py-1 border-t border-green-300 dark:border-green-600">
                                                            <span class="font-semibold">Total da base de cálculo:</span>
                                                            <span class="font-semibold">
                                                                R$ {{ number_format($valorOriginalBasePercentual, 2, ',', '.') }}
                                                            </span>
                                                        </div>

                                                        @if ($desconto->tipo === 'percentual' && $desconto->porcentagem)
                                                            <div class="flex justify-between items-center py-1 border-t border-green-200 dark:border-green-700">
                                                                <span>Percentual aplicado:</span>
                                                                <span class="font-semibold text-orange-600 dark:text-orange-400">
                                                                    {{ number_format($desconto->porcentagem, 2, ',', '.') }}%
                                                                </span>
                                                            </div>
                                                        @endif

                                                        <div class="flex justify-between items-center py-1 border-t border-green-200 dark:border-green-700">
                                                            <span>Desconto total ({{ $desconto->tipo === 'percentual' ? number_format($desconto->porcentagem, 2, ',', '.').'%' : 'fixo' }}):</span>
                                                            <span class="font-semibold text-red-600 dark:text-red-400">
                                                                - R$ {{ number_format($desconto->valor, 2, ',', '.') }}
                                                            </span>
                                                        </div>

                                                        @if ($descontosJaAprovados > 0)
                                                            <div class="flex justify-between items-center py-1 border-t border-green-200 dark:border-green-700">
                                                                <span>Outros descontos já aprovados:</span>
                                                                <span class="font-semibold text-red-600 dark:text-red-400">
                                                                    - R$ {{ number_format($descontosJaAprovados, 2, ',', '.') }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        <div class="flex justify-between items-center py-2 border-t-2 border-green-300 dark:border-green-600">
                                                            <span class="font-bold">Base após este desconto:</span>
                                                            <span class="font-bold text-green-600 dark:text-green-400">
                                                                R$ {{ number_format($valorAposDesconto, 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Formulário de aprovação/rejeição -->
                                    <form method="POST" action="{{ route('descontos.avaliar', $desconto->id) }}"
                                        class="flex items-center gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        @csrf

                                        <div class="flex-1">
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Justificativa (opcional)
                                            </label>
                                            <input type="text" name="justificativa"
                                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Digite uma justificativa (se necessário)">
                                        </div>

                                        <div class="flex gap-2 pt-5">
                                            <button type="submit" name="acao" value="aprovar"
                                                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                                <span>Aprovar</span>
                                            </button>

                                            <button type="submit" name="acao" value="rejeitar"
                                                onclick="return confirm('Tem certeza que deseja rejeitar este desconto? Esta ação não pode ser desfeita.')"
                                                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                                <span>Rejeitar</span>
                                            </button>
                                        </div>
                                    </form>

                                    @if ($desconto->observacao)
                                        <div
                                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mt-3">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                Observação:</p>
                                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $desconto->observacao }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        @empty
                            <div
                                class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-3 opacity-50 text-green-500" />
                                <p class="text-lg font-medium mb-1">Nenhum desconto pendente</p>
                                <p class="text-sm">Todos os descontos foram avaliados ou não há descontos aplicados.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if (is_countable($descontos) && count($descontos) > 1)
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-information-circle
                                        class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                                        Ações em lote para todos os descontos:
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="aprovarTodos"
                                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                        Aprovar Todos
                                    </button>
                                    <button type="button" wire:click="rejeitarTodos"
                                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                                        <x-heroicon-o-x-circle class="w-4 h-4" />
                                        Rejeitar Todos
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($descontos && is_countable($descontos) && count($descontos) > 0)
                    <hr class="my-6 border-gray-200 dark:border-gray-700" />

                    <!-- Resumo Financeiro -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-calculator class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            Resumo Financeiro
                        </h3>

                        <div
                            class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-4 py-3 border-b border-blue-700 dark:border-blue-800">
                                <h4 class="text-sm font-semibold text-white">Impacto dos Descontos</h4>
                            </div>
                            <div class="bg-white dark:bg-zinc-900 p-4 space-y-2">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor
                                        Original:</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($orcamento->valor_total_itens ?? 0, 2, ',', '.') }}
                                    </span>
                                </div>

                                @if ($orcamento->totalDescontosAprovados() > 0)
                                    <div
                                        class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total de
                                            Descontos já aprovados:</span>
                                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                            - R$
                                            {{ number_format($orcamento->totalDescontosAprovados(), 2, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                                <div
                                    class="flex justify-between items-center py-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total de
                                        Descontos Solicitados:</span>
                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                        - R$ {{ number_format(collect($descontos)->sum('valor'), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600">
                                    <span class="text-base font-bold text-gray-900 dark:text-gray-100">Valor Final (se
                                        todos aprovados):</span>
                                    <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                        R$
                                        {{ number_format(($orcamento->valor_total_itens - $orcamento->totalDescontosAprovados() ?? 0) - collect($descontos)->sum('valor'), 2, ',', '.') }}
                                    </span>
                                </div>

                                @if (($orcamento->valor_total_itens ?? 0) > 0)
                                    <div
                                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mt-3">
                                        <div class="flex items-start gap-2">
                                            <x-heroicon-o-exclamation-triangle
                                                class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                                            <div class="flex-1">
                                                <p
                                                    class="text-sm font-medium text-yellow-900 dark:text-yellow-200 mb-1">
                                                    Percentual Total de Desconto:</p>
                                                <p class="text-lg font-bold text-yellow-700 dark:text-yellow-300">
                                                    {{ number_format(($orcamento->totalDescontosAprovados() / $orcamento->valor_total_itens) * 100, 2, ',', '.') }}%
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>