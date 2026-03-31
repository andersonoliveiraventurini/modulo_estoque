<x-layouts.app :title="__('Editar Orçamento')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-6 h-6 text-accent" />
                    <flux:heading size="xl">
                        @if (!$orcamento->encomenda)
                            Editar Orçamento para Cliente {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
                        @else
                            Editar Encomenda para Cliente {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
                        @endif
                    </flux:heading>
                </div>
                {{-- Erros de validação --}}
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 mb-4">
                        <strong>Erros de validação:</strong>
                        <ul class="list-disc ml-5 mt-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Erro geral (ex: exceção capturada) --}}
                @if (session('error'))
                    <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 mb-4">
                        <strong>Erro:</strong> {{ session('error') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg p-4 mb-4">
                        <strong>Atenção:</strong> {{ session('warning') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50 border border-green-300 text-green-800 rounded-lg p-4 mb-4">
                        <strong>Sucesso:</strong> {{ session('success') }}
                    </div>
                @endif
                @if (!$ativo)
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-600 rounded-xl p-4 animate-pulse">
                        <div class="flex gap-3 items-center">
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-600 flex-shrink-0" />
                            <div>
                                <h4 class="font-bold text-red-900 dark:text-red-200 text-lg">PENDÊNCIA FISCAL DETECTADA</h4>
                                <p class="text-red-800 dark:text-red-300 font-medium text-sm">Favor verificar - Cliente sem inscrição estadual ativa ou CNPJ irregular na Receita Federal.</p>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            alert('⚠️ Favor verificar - Cliente sem inscrição estadual ativa ou CNPJ irregular na Receita Federal.');
                        });
                    </script>
                @endif
                @if ($cliente->vendedor_interno != null)
                    <p> Vendedor interno: {{ $cliente->vendedor_interno ?? 'Não atribuído' }} </p>
                @endif
                @if ($cliente->vendedor_externo != null)
                    <p> Vendedor externo: {{ $cliente->vendedor_externo ?? 'Não atribuído' }} </p>
                @endif
                @if ($cliente->desconto_aprovado != null)
                    <p> Desconto aprovado: {{ $cliente->desconto_aprovado ?? 'Não atribuído' }} </p>
                @endif
                <input type="hidden" name="desconto_aprovado" id="desconto_aprovado"
                    value="{{ $cliente->desconto_aprovado ?? 0 }}" />

                @if (!$orcamento->encomenda)
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Buscar Produtos
                        </h3>
                        <livewire:lista-produto-orcamento />
                    </div>
                @endif

                {{-- ================================================================
                     FORM — tudo que precisa ser enviado ao controller está aqui dentro
                     ================================================================ --}}
                <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-8"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- ✅ FIX 1: desconto_aprovado e cliente_id DENTRO do form --}}
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <input type="hidden" name="desconto_aprovado" id="desconto_aprovado"
                        value="{{ $cliente->desconto_aprovado ?? 0 }}" />

                    {{-- ================================================================
                         ITENS DE ENCOMENDA — ✅ FIX 2: agora DENTRO do form
                         ================================================================ --}}
                    @if ($orcamento->encomenda && $orcamento->itens->whereNotNull('produto_id')->isEmpty())
                        @php
                            $grupo = \App\Models\ConsultaPrecoGrupo::with([
                                'itens.cor',
                                'itens.fornecedorSelecionado.fornecedor',
                            ])
                                ->where('orcamento_id', $orcamento->id)
                                ->first();
                        @endphp

                        @if ($grupo && $grupo->itens->count() > 0)
                            <div class="space-y-4">
                                <hr />
                                <h3 class="text-lg font-medium flex items-center gap-2">
                                    <x-heroicon-o-shopping-cart class="w-5 h-5 text-purple-600" />
                                    Itens da Encomenda
                                    <span class="text-xs font-normal text-zinc-400">(cotação
                                        #{{ $grupo->id }})</span>
                                </h3>

                                <div
                                    class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                    Descrição</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                    Cor</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                    Fornecedor</th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                                                    Qtd</th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                    Preço Venda</th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                    Valor novo unit. (R$)</th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                    Subtotal</th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                    c/ Desconto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itens-encomenda" class="divide-y">
                                            @php
                                                $orcamentoEncomendaItens = $orcamento->itens->whereNull('produto_id')->values();
                                            @endphp
                                            @foreach ($grupo->itens as $item)
                                                @php
                                                    $forn = $item->fornecedorSelecionado;
                                                    $precoVenda = $forn ? (float) $forn->preco_venda : 0;
                                                    $orcamentoItem = $orcamentoEncomendaItens->get($loop->index);
                                                    $valorNovoUnit = $orcamentoItem ? (float) $orcamentoItem->valor_unitario_com_desconto : $precoVenda;
                                                    if ($valorNovoUnit <= 0) { $valorNovoUnit = $precoVenda; }
                                                @endphp
                                                <tr data-item-id="{{ $item->id }}"
                                                    data-preco-original="{{ $precoVenda }}"
                                                    data-quantidade="{{ $item->quantidade }}">

                                                    {{-- Hiddens enviados ao controller --}}
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][consulta_preco_id]"
                                                        value="{{ $item->id }}">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][fornecedor_id]"
                                                        value="{{ $forn->fornecedor_id ?? '' }}">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][quantidade]"
                                                        value="{{ $item->quantidade }}">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][preco_original]"
                                                        value="{{ $precoVenda }}" class="enc-preco-original">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][desconto_item]"
                                                        value="{{ number_format($precoVenda - $valorNovoUnit, 2, '.', '') }}" class="enc-desconto-item">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][tipo_desconto]"
                                                        value="{{ $valorNovoUnit < $precoVenda ? 'produto' : 'percentual' }}" class="enc-tipo-desconto">
                                                    <input type="hidden"
                                                        name="encomenda_itens[{{ $loop->index }}][preco_final]"
                                                        value="{{ number_format($valorNovoUnit, 2, '.', '') }}" class="enc-preco-final">

                                                    <td class="px-4 py-2 text-sm font-medium">{{ $item->descricao }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">{{ $item->cor->nome ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if ($forn)
                                                            <span
                                                                class="text-emerald-600 font-medium">{{ $forn->fornecedor->nome_fantasia }}</span>
                                                        @else
                                                            <span class="text-red-500 text-xs">Sem fornecedor</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-center font-semibold">
                                                        {{ $item->quantidade }}</td>
                                                    <td class="px-4 py-2 text-right text-sm">
                                                        R$ <span
                                                            class="enc-preco-display">{{ number_format($precoVenda, 2, ',', '.') }}</span>
                                                    </td>
                                                    <td class="px-4 py-2 text-right text-sm">
                                                        <input type="number" step="1" min="0"
                                                            value="{{ number_format($valorNovoUnit, 2, '.', '') }}"
                                                            placeholder="{{ number_format($precoVenda, 2, ',', '.') }}"
                                                            class="enc-valor-novo-input w-24 border rounded px-2 py-1 text-sm text-right"
                                                            data-index="{{ $loop->index }}"
                                                            onchange="recalcularItemEncomenda(this)" />
                                                    </td>
                                                    <td class="px-4 py-2 text-right text-sm font-medium">
                                                        R$ <span
                                                            class="enc-subtotal">{{ number_format($precoVenda * $item->quantidade, 2, ',', '.') }}</span>
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right text-sm font-semibold text-emerald-600">
                                                        R$ <span
                                                            class="enc-subtotal-desc">{{ number_format($valorNovoUnit * $item->quantidade, 2, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ================================================================
                         PRODUTOS NORMAIS (somente não-encomenda)
                         ================================================================ --}}
                    @if (!$orcamento->encomenda)
                        <div class="space-y-4"><br />
                            <hr />
                            <h3 class="text-lg font-medium flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 7h9m0 0V10"></path>
                                </svg>
                                Produtos no Orçamento
                            </h3>
                            <div
                                class="ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Código</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Produto</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Part Number</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Fornecedor</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Cor</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Preço Unit.</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">
                                                Qtd.</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Subtotal</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                c/ Desconto</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="produtos-originais" class="divide-y">
                                        @foreach ($orcamento->itens->whereNotNull('produto_id') as $item)
                                            @php
                                                // Tenta encontrar um desconto específico para este produto
                                                $descontoItem = $orcamento->descontos
                                                    ->where('produto_id', $item->produto_id)
                                                    ->where('tipo', 'produto')
                                                    ->first();
                                                
                                                $descontoProduto = $descontoItem ? ($descontoItem->valor / $item->quantidade) : 0;
                                                $tipoDesconto = $descontoItem ? 'produto' : 'percentual';
                                                
                                                $valorUnitario = (float) $item->valor_unitario;
                                                $quantidade = (float) $item->quantidade;
                                                $subtotal = $valorUnitario * $quantidade;
                                                $valorComDesconto = (float) $item->valor_com_desconto;
                                            @endphp
                                            <tr data-estoque="{{ $item->produto->estoque_atual ?? 'null' }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][produto_id]" value="{{ $item->produto->id }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][preco_original]" value="{{ $valorUnitario }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][liberar_desconto]" value="{{ $item->produto->liberar_desconto ? 1 : 0 }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][desconto_produto]" class="input-desconto-produto" value="{{ $descontoProduto }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][tipo_desconto]" class="input-tipo-desconto" value="{{ $tipoDesconto }}">
                                                
                                                <input type="hidden" name="produtos[{{ $loop->index }}][subtotal]" class="input-subtotal" value="{{ number_format($subtotal, 2, '.', '') }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][subtotal_com_desconto]" class="input-subtotal-com-desconto" value="{{ number_format($valorComDesconto, 2, '.', '') }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][preco_unitario_com_desconto]" class="input-preco-unitario-com-desconto" value="{{ number_format($valorComDesconto / $quantidade, 2, '.', '') }}">

                                                <td class="px-3 py-2 border">{{ $item->produto->id }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->nome }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '' }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome_fantasia ?? '' }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->cor->nome ?? '' }}</td>
                                                <td class="px-3 py-2 border">
                                                    @if($item->produto->liberar_desconto)
                                                        <div class="flex flex-col gap-1">
                                                            <input type="number" step="0.01" 
                                                                name="produtos[{{ $loop->index }}][valor_unitario]"
                                                                value="{{ number_format($valorUnitario, 2, '.', '') }}"
                                                                onchange="alterarPrecoProdutoOriginal({{ $loop->index }}, this.value)"
                                                                class="input-valor-unitario w-24 border rounded px-2 py-1 text-sm" />
                                                            @if($descontoProduto > 0)
                                                                <small class="text-xs text-gray-500">Original: R$ {{ number_format($valorUnitario, 2, ',', '.') }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        R$ {{ number_format($valorUnitario, 2, ',', '.') }}
                                                        <input type="hidden" name="produtos[{{ $loop->index }}][valor_unitario]" value="{{ $valorUnitario }}" class="input-valor-unitario">
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border">
                                                    <input type="number"
                                                        name="produtos[{{ $loop->index }}][quantidade]"
                                                        value="{{ $item->quantidade }}" min="1" step="1"
                                                        oninput="alterarQuantidadeOriginal({{ $loop->index }}, this.value)"
                                                        onchange="alterarQuantidadeOriginal({{ $loop->index }}, this.value)"
                                                        class="input-quantidade w-16 border rounded px-2 py-1 text-center font-bold" />
                                                </td>
                                                <td class="px-3 py-2 border label-subtotal font-medium">
                                                    R$ {{ number_format($subtotal, 2, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 border text-green-600 label-subtotal-desconto font-bold">
                                                    R$ {{ number_format($valorComDesconto, 2, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 border text-center">
                                                    <button type="button"
                                                        onclick="removerProdutoOriginal({{ $loop->index }})"
                                                        class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition-colors">
                                                        <x-heroicon-o-trash class="w-5 h-5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tbody id="produtos-selecionados" class="divide-y"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Seção de Vidros -->
                        <div class="space-y-4">
                            <br />
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-beaker class="w-5 h-5 text-accent" />
                                <flux:heading size="lg">Vidros ou Esteiras</flux:heading>

                                <flux:button type="button" size="sm" icon="plus" onclick="addVidro()" variant="filled">
                                    Adicionar Vidro/Esteira
                                </flux:button>
                            </div>

                            <!-- Wrapper dos vidros -->
                            <div id="vidros-wrapper" class="space-y-4">
                                @foreach ($orcamento->vidros ?? [] as $vidro)
                                    <div class="space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4"
                                        data-vidro-id="{{ $vidro->id }}">
                                        <input type="hidden" name="vidros_existentes[{{ $loop->index }}][id]"
                                            value="{{ old("vidros_existentes.{$loop->index}.id", $vidro->id) }}" />
                                        <div class="overflow-x-auto">
                                            <div class="flex gap-4 min-w-max">
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Descrição</label>
                                                    <input type="text"
                                                        name="vidros_existentes[{{ $loop->index }}][descricao]"
                                                        value="{{ old("vidros_existentes.{$loop->index}.descricao", $vidro->descricao) }}"
                                                        placeholder="Ex: Vidro incolor 8mm"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Quantidade</label>
                                                    <input type="number"
                                                        name="vidros_existentes[{{ $loop->index }}][quantidade]"
                                                        value="{{ old("vidros_existentes.{$loop->index}.quantidade", $vidro->quantidade) }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700">Preço
                                                        m²</label>
                                                    <input type="number" step="1"
                                                        name="vidros_existentes[{{ $loop->index }}][preco_m2]"
                                                        value="{{ old("vidros_existentes.{$loop->index}.preco_m2", $vidro->preco_metro_quadrado) }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700">Altura
                                                        (mm)
                                                    </label>
                                                    <input type="number"
                                                        name="vidros_existentes[{{ $loop->index }}][altura]"
                                                        value="{{ old("vidros_existentes.{$loop->index}.altura", $vidro->altura) }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700">Largura
                                                        (mm)</label>
                                                    <input type="number"
                                                        name="vidros_existentes[{{ $loop->index }}][largura]"
                                                        value="{{ $vidro->largura }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="vidros_existentes[{{ $loop->index }}][area]"
                                            class="area-hidden"
                                            value="{{ number_format(($vidro->altura / 1000) * ($vidro->largura / 1000), 2, '.', '') }}" />
                                        <input type="hidden"
                                            name="vidros_existentes[{{ $loop->index }}][valor_total]"
                                            class="valor-hidden" value="{{ $vidro->valor_total }}" />
                                        <input type="hidden"
                                            name="vidros_existentes[{{ $loop->index }}][valor_com_desconto]"
                                            class="valor-desconto-hidden" value="{{ $vidro->valor_com_desconto }}" />
                                        <div class="mt-2 text-sm">
                                            <strong>Área:</strong> <span
                                                class="area">{{ number_format(($vidro->altura / 1000) * ($vidro->largura / 1000), 2, ',', '.') }}</span>
                                            m² |
                                            <strong>Valor:</strong> R$ <span
                                                class="valor">{{ number_format($vidro->valor_total, 2, ',', '.') }}</span>
                                            |
                                            <strong>c/ desc:</strong> R$ <span
                                                class="valor-desconto">{{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</span>
                                            <button type="button" onclick="removeVidroExistente(this)"
                                                class="absolute right-2 text-red-600 hover:text-red-800"
                                                style="padding-top: -1rem;">Remover</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        @if (!$orcamento->encomenda)
                            <hr />
                        @else
                            <br />
                        @endif
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Nome da Obra e Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input type="text" name="obra" placeholder="Digite o nome da obra"
                                label="Nome da Obra" required :value="old('obra', $orcamento->obra)" />
                            <x-select name="complemento" label="Complemento de outro orçamento?" required>
                                <option value="Não" {{ old('complemento', $orcamento->complemento) == 'Não' ? 'selected' : '' }}>Não</option>
                                <option value="Sim" {{ old('complemento', $orcamento->complemento) == 'Sim' ? 'selected' : '' }}>Sim</option>
                            </x-select>
                            <x-input type="text" name="prazo_entrega" placeholder="Ex: 15 dias úteis"
                                label="Prazo de Entrega" :value="old('prazo_entrega', $orcamento->prazo_entrega)" />
                            <x-select name="frete" label="Tipo de Frete">
                                <option value="">Selecione...</option>
                                <option value="cif" {{ old('frete', $orcamento->frete) == 'cif' ? 'selected' : '' }}>CIF - entrega por conta do
                                    fornecedor</option>
                                <option value="fob" {{ old('frete', $orcamento->frete) == 'fob' ? 'selected' : '' }}>FOB - entrega por conta do cliente
                                </option>
                            </x-select>
                            <x-select name="enderecos_cadastrados" label="Endereços de cadastrados do cliente">
                                <option value="">Selecione...</option>
                                @foreach ($cliente->enderecos as $endereco)
                                    <option value="{{ $endereco->id }}">
                                        @if ($endereco->logradouro != null)
                                            {{ $endereco->logradouro . ' - ' }}
                                        @endif
                                        @if ($endereco->numero != null)
                                            {{ $endereco->numero . ' - ' }}
                                        @endif
                                        @if ($endereco->complemento != null)
                                            {{ $endereco->complemento . ' - ' }}
                                        @endif
                                        @if ($endereco->bairro != null)
                                            {{ $endereco->bairro . ' - ' }}
                                        @endif
                                        @if ($endereco->cidade != null)
                                            {{ $endereco->cidade . ' - ' }}
                                        @endif
                                        @if ($endereco->estado != null)
                                            {{ $endereco->estado . ' - ' }}
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" :value="$orcamento->endereco->cep ?? ''" />
                        </div>
                        <div id="endereco-entrega-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" :value="old('entrega_cidade', $orcamento->endereco->cidade ?? '')" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" :value="old('entrega_estado', $orcamento->endereco->estado ?? '')" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" :value="old('entrega_bairro', $orcamento->endereco->bairro ?? '')" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly" :value="old('entrega_logradouro', $orcamento->endereco->logradouro ?? '')" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    :value="old('entrega_numero', $orcamento->endereco->numero ?? '')" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc." :value="old('entrega_compl', $orcamento->endereco->complemento ?? '')" />
                            </div>
                        </div>
                    </div>
                    <!-- Opções de Transporte -->
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2a4 4 0 014-4h4l3 3-3 3h-8zM3 7h13a2 2 0 012 2v2"></path>
                            </svg>
                            Tipo de venda <span class="text-red-500">*</span>
                        </h3>
                        @error('tipos_transporte')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach ($opcoesTransporte as $opcao)
                                <label
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 hover:bg-blue-50 cursor-pointer transition">

                                    <input type="radio" name="tipos_transporte" value="{{ $opcao->id }}" x-model="tipoTransporteId"
                                        required class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @if ($orcamento->transportes->contains($opcao->id)) checked @endif /><span
                                        class="text-sm text-gray-700">{{ $opcao->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <hr />
                    <!-- Seção de Pagamento e Impostos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <flux:select name="condicao_id" id="condicao_id" label="Condição de pagamento" required>
                            <option value="">Selecione...</option>
                            @foreach ($condicao as $c)
                                <option value="{{ $c->id }}" {{ old('condicao_id', $orcamento->condicao_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->nome }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input name="outros_meios_pagamento" id="outros_meios_pagamento" label="Outros meios pagamento" 
                            disabled placeholder="Ex: Boleto 28/56/84..." :value="old('outros_meios_pagamento', $orcamento->outros_meios_pagamento)" />

                        <flux:select name="tipo_documento" label="Nota fiscal">
                            <option value="">Selecione...</option>
                            <option value="Nota fiscal" {{ old('tipo_documento', $orcamento->tipo_documento) == 'Nota fiscal' ? 'selected' : '' }}>Nota fiscal</option>
                            <option value="Cupom Fiscal" {{ old('tipo_documento', $orcamento->tipo_documento) == 'Cupom Fiscal' ? 'selected' : '' }}>Cupom Fiscal</option>
                        </flux:select>

                        <flux:select name="homologacao" label="Homologação" required>
                            <option value="0" {{ old('homologacao', $orcamento->homologacao) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('homologacao', $orcamento->homologacao) == 1 ? 'selected' : '' }}>Sim</option>
                        </flux:select>

                        <flux:select name="venda_triangular" id="venda_triangular" label="Venda triangular?" required>
                            <option value="0" {{ old('venda_triangular', $orcamento->venda_triangular) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('venda_triangular', $orcamento->venda_triangular) == 1 ? 'selected' : '' }}>Sim</option>
                        </flux:select>

                        <flux:input name="cnpj_triangular" id="cnpj_triangular" label="CNPJ venda triangular" 
                            disabled size="18" maxlength="18" onkeypress="mascara(this, '##.###.###/####-##')" 
                            placeholder="00.000.000/0000-00" :value="old('cnpj_triangular', $orcamento->cnpj_triangular)" />
                    </div>

                    <!-- Seção de Valores e Descontos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <input type="hidden" id="desconto_aprovado" value="{{ $cliente->desconto ?? 0 }}">
                        
                        <flux:input name="desconto" label="Desconto na vendedor %" 
                            :value="old('desconto', $desconto_percentual ?? 0)" 
                            placeholder="0" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="desconto_especifico" label="Desconto específico R$" 
                            :value="old('desconto_especifico', $desconto_especifico ?? '0.00')" 
                            placeholder="0.00" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="guia_recolhimento" label="Guia Recolhimento" 
                            :value="old('guia_recolhimento', $orcamento->guia_recolhimento ?? 0)" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input id="valor_total" name="valor_total" label="Total s/ desconto (R$)" 
                            readonly value="0,00" />

                        <flux:input id="valor_final" label="Valor Final c/ desconto (R$)" 
                            readonly value="0.00" 
                            class="font-semibold text-green-700 dark:text-green-400" />
                    </div>


                    <div class="space-y-4">
                        <hr />
                        <div class="flex items-center gap-2">
                             <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5 text-accent" />
                             <flux:heading size="lg">Observações Gerais</flux:heading>
                        </div>
                        <flux:textarea name="observacoes" placeholder="Digite as observações" rows="4">{{ old('observacoes', $orcamento->observacoes) }}</flux:textarea>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <flux:button type="submit" variant="primary" class="px-8">
                            Salvar Orçamento
                        </flux:button>
                        <flux:button type="reset" variant="ghost">
                            Limpar
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quantidade Produto -->
    <!-- Modal Quantidade Produto -->
    <div id="modal-quantidade"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-80 shadow-lg relative">
            <button onclick="fecharModal()"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Quantidade do Produto</h3>
            <p id="produto-nome" class="mb-2 font-medium"></p>

            {{-- ✅ NOVO: aviso de estoque --}}
            <div id="aviso-estoque"
                class="hidden bg-amber-50 border border-amber-300 rounded-lg p-3 mb-3 text-sm text-amber-800 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                <div>
                    <strong>Estoque insuficiente!</strong>
                    <p id="aviso-estoque-texto" class="mt-0.5"></p>
                </div>
            </div>

            <input id="quantidade-produto" type="number" min="1" value="1"
                class="w-full border rounded px-3 py-2 mb-4" />
            {{-- ✅ id adicionado no botão --}}
            <button id="btn-confirmar-quantidade" onclick="confirmarQuantidade()"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full transition-colors">
                Adicionar
            </button>
        </div>
    </div>

</x-layouts.app>
<script src="{{ asset('js/valida.js') }}"></script>

<script>
    // ==================== VARIÁVEIS GLOBAIS ====================
    const cores = @json($cores);
    const fornecedores = @json($fornecedores);
    const oldItensRaw = @json($itensParaJs ?? []);
    let totalProdutosExistentes = {{ $orcamento->itens->whereNotNull('produto_id')->count() }};
    let itemIndex = oldItensRaw.length;

    window.vidroIndex = 0;
    window.produtos = [];
    window.produtoSelecionado = null;

    // ==================== INICIALIZAÇÃO DE DADOS ====================
    
    // Restauração de novos itens (produtos adicionados via busca)
    const oldItens = @json(old('itens', []));
    if (oldItens.length > 0) {
        oldItens.forEach(item => {
            window.produtos.push({
                id: item.id,
                nome: item.nome,
                preco: parseFloat(item.preco_unitario) || 0,
                precoOriginal: parseFloat(item.preco_original) || 0,
                quantidade: parseInt(item.quantidade) || 1,
                fornecedor: item.fornecedor || '',
                cor: item.cor || '',
                partNumber: item.partNumber || '',
                liberarDesconto: parseInt(item.liberar_desconto) || 1,
                descontoProduto: parseFloat(item.desconto_produto) || 0,
                estoqueDisponivel: null
            });
        });
    }

    // ==================== FUNÇÕES DE INTERFACE (ITENS CONSULTA) ====================
    function addItem() {
        const wrapper = document.getElementById('itens-wrapper');
        const itemDiv = document.createElement('div');
        itemDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";

        const oldData = oldItensRaw[itemIndex] || {};

        let coresOptions = `<option value="">Selecione...</option>`;
        cores.forEach(cor => {
            const selected = oldData.cor === cor.nome ? 'selected' : '';
            coresOptions += `<option value="${cor.nome}" ${selected}>${cor.nome}</option>`;
        });

        let fornecedoresOptions = `<option value="">Selecione...</option>`;
        fornecedores.forEach(f => {
            const selected = oldData.fornecedor_id == f.id ? 'selected' : '';
            fornecedoresOptions += `<option value="${f.id}" ${selected}>${f.nome_fantasia}</option>`;
        });

        const idField = oldData.id ? `<input type="hidden" name="itens[${itemIndex}][id]" value="${oldData.id}" />` : '';

        itemDiv.innerHTML = `
            <button type="button" onclick="removeItem(this)"
                class="absolute right-2 top-2 text-red-600 hover:text-red-800 text-lg px-2"
                title="Remover item">
                Remover
            </button>
            ${idField}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Descrição do item</label>
                    <input type="text" name="itens[${itemIndex}][nome]" placeholder="Digite a descrição"
                           value="${oldData.nome || ''}"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                    <input type="number" name="itens[${itemIndex}][quantidade]" placeholder="Digite a quantidade"
                           value="${oldData.quantidade || ''}"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cor</label>
                    <select name="itens[${itemIndex}][cor]" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        ${coresOptions}
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Fornecedor</label>
                    <select name="itens[${itemIndex}][fornecedor_id]" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        ${fornecedoresOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="itens[${itemIndex}][observacoes]" placeholder="Digite os detalhes adicionais..." rows="2"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">${oldData.observacoes || ''}</textarea>
                </div>
            </div>
        `;

        wrapper.appendChild(itemDiv);
        itemIndex++;
    }

    function removeItem(button) {
        const itemDiv = button.closest('.space-y-2');
        if (itemDiv) itemDiv.remove();
    }

    // ==================== MODAL DE PRODUTOS ====================
    window.selecionarProdutoComQuantidade = function(id, nome, preco, fornecedor, cor, partNumber, liberarDesconto, estoqueDisponivel = null) {
        window.produtoSelecionado = {
            id: id,
            nome: nome,
            preco: parseFloat(preco) || 0,
            precoOriginal: parseFloat(preco) || 0,
            fornecedor: fornecedor || '',
            cor: cor || '',
            partNumber: partNumber || '',
            liberarDesconto: parseInt(liberarDesconto || 1),
            quantidade: 1,
            descontoProduto: 0,
            estoqueDisponivel: estoqueDisponivel !== null ? parseInt(estoqueDisponivel) : null
        };

        document.getElementById('produto-nome').textContent = nome;
        document.getElementById('quantidade-produto').value = 1;
        document.getElementById('aviso-estoque').classList.add('hidden');
        
        const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
        btnConfirmar.textContent = 'Adicionar';
        btnConfirmar.className = "bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full transition-colors";

        const modalBody = document.getElementById('modal-quantidade').querySelector('.bg-white');
        const avisoExistente = modalBody.querySelector('.aviso-desconto-modal');
        if (avisoExistente) avisoExistente.remove();

        if (parseInt(liberarDesconto) === 0) {
            const aviso = document.createElement('div');
            aviso.className = 'aviso-desconto-modal bg-red-50 border border-red-200 rounded p-2 mb-3 text-sm text-red-700';
            aviso.innerHTML = '⚠️ Este produto não permite desconto';
            const inputQuantidade = document.getElementById('quantidade-produto');
            inputQuantidade.parentNode.insertBefore(aviso, inputQuantidade);
        }

        document.getElementById('modal-quantidade').classList.remove('hidden');
    };

    window.fecharModal = function() {
        document.getElementById('modal-quantidade').classList.add('hidden');
        window.produtoSelecionado = null;
    };

    window.confirmarQuantidade = function() {
        if (!window.produtoSelecionado) return;

        if (window.produtoSelecionado._pendente) {
            _adicionarProdutoConfirmado(window.produtoSelecionado._pendente);
            return;
        }

        const quantidade = parseInt(document.getElementById('quantidade-produto').value) || 1;
        const estoque = window.produtoSelecionado.estoqueDisponivel;

        if (estoque !== null && estoque !== undefined && quantidade > estoque) {
            document.getElementById('aviso-estoque-texto').textContent = 
                `Você solicitou ${quantidade} unidade(s), mas há apenas ${estoque} em estoque. O pedido será gerado, mas pode haver indisponibilidade na entrega.`;
            document.getElementById('aviso-estoque').classList.remove('hidden');

            const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
            btnConfirmar.textContent = '⚠️ Estou ciente, adicionar mesmo assim';
            btnConfirmar.className = "bg-amber-500 text-white px-4 py-2 rounded hover:bg-amber-600 w-full transition-colors";

            window.produtoSelecionado._pendente = quantidade;
            return;
        }

        _adicionarProdutoConfirmado(quantidade);
    };

    function _adicionarProdutoConfirmado(quantidade) {
        window.produtoSelecionado.quantidade = quantidade;
        window.adicionarProduto(
            window.produtoSelecionado.id,
            window.produtoSelecionado.nome,
            window.produtoSelecionado.preco,
            window.produtoSelecionado.fornecedor,
            window.produtoSelecionado.cor,
            window.produtoSelecionado.partNumber,
            window.produtoSelecionado.quantidade,
            window.produtoSelecionado.liberarDesconto,
            window.produtoSelecionado.estoqueDisponivel
        );
        window.fecharModal();
    }

    // ==================== GESTÃO DE PRODUTOS NOVOS ====================
    window.adicionarProduto = function(id, nome, preco, fornecedor, cor, partNumber, quantidade, liberarDesconto, estoqueDisponivel = null) {
        const produtosOriginais = document.getElementById('produtos-originais');
        if (produtosOriginais) {
            const rows = produtosOriginais.querySelectorAll('tr');
            for (let row of rows) {
                if (row.style.display === 'none') continue;
                const input = row.querySelector('input[name*="[produto_id]"]');
                if (input && input.value == id) {
                    alert("Este produto já está no orçamento!");
                    return;
                }
            }
        }

        if (window.produtos.find(p => p.id == id)) {
            alert("Produto já adicionado!");
            return;
        }

        window.produtos.push({
            id: id,
            nome: nome,
            preco: parseFloat(preco) || 0,
            precoOriginal: parseFloat(preco) || 0,
            quantidade: parseInt(quantidade) || 1,
            fornecedor: fornecedor || '',
            cor: cor || '',
            partNumber: partNumber || '',
            liberarDesconto: parseInt(liberarDesconto || 1),
            descontoProduto: 0,
            estoqueDisponivel: estoqueDisponivel !== null ? parseInt(estoqueDisponivel) : null
        });

        renderProdutosNovos();
    };

    window.alterarPrecoProdutoNovo = function(index, novoPreco) {
        const produto = window.produtos[index];
        if (produto.liberarDesconto === 0) {
            alert("Este produto não permite alteração de preço!");
            renderProdutosNovos();
            return;
        }

        novoPreco = parseFloat(novoPreco) || produto.precoOriginal;
        if (novoPreco < 0) novoPreco = 0;

        window.produtos[index].preco = novoPreco;
        window.produtos[index].descontoProduto = produto.precoOriginal - novoPreco;
        renderProdutosNovos();
    };

    window.alterarQuantidade = function(index, valor) {
        if (!window.produtos[index]) return;
        const novaQuantidade = parseInt(valor) || 1;
        const produto = window.produtos[index];

        if (produto.estoqueDisponivel !== null && novaQuantidade > produto.estoqueDisponivel) {
            if (!confirm(`⚠️ Estoque insuficiente! Há apenas ${produto.estoqueDisponivel} em estoque. Deseja adicionar mesmo assim?`)) {
                renderProdutosNovos();
                return;
            }
        }

        window.produtos[index].quantidade = novaQuantidade;
        renderProdutosNovos();
    };

    window.removerProduto = function(index) {
        window.produtos.splice(index, 1);
        renderProdutosNovos();
    };

    function renderProdutosNovos() {
        const wrapper = document.getElementById('produtos-selecionados');
        if (!wrapper) return;
        wrapper.innerHTML = '';

        const descontoPercentual = obterDescontoAplicado();

        window.produtos.forEach((p, i) => {
            const index = totalProdutosExistentes + i;
            const subtotal = p.preco * p.quantidade;
            let subtotalComDesconto;
            let tipoDesconto = 'nenhum';

            if (p.descontoProduto > 0) {
                subtotalComDesconto = subtotal;
                tipoDesconto = 'produto';
            } else if (p.liberarDesconto === 1 && descontoPercentual > 0) {
                subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
                tipoDesconto = 'percentual';
            } else {
                subtotalComDesconto = subtotal;
            }

            const row = document.createElement('tr');
            row.className = p.liberarDesconto === 0 ? 'bg-red-50 dark:bg-red-900/10' : '';
            row.innerHTML = `
                <td class="px-3 py-2 border">
                    <input type="hidden" name="produtos[${index}][produto_id]" value="${p.id}">
                    <input type="hidden" name="produtos[${index}][nome]" value="${p.nome}">
                    <input type="hidden" name="produtos[${index}][preco_original]" value="${p.precoOriginal}">
                    <input type="hidden" name="produtos[${index}][liberar_desconto]" value="${p.liberarDesconto}">
                    <input type="hidden" name="produtos[${index}][desconto_produto]" value="${p.descontoProduto}">
                    <input type="hidden" name="produtos[${index}][tipo_desconto]" value="${tipoDesconto}">
                    <input type="hidden" name="produtos[${index}][subtotal]" class="input-subtotal" value="${subtotal.toFixed(2)}">
                    <input type="hidden" name="produtos[${index}][subtotal_com_desconto]" class="input-subtotal-com-desconto" value="${subtotalComDesconto.toFixed(2)}">
                    <input type="hidden" name="produtos[${index}][preco_unitario_com_desconto]" class="input-preco-unitario-com-desconto" value="${(subtotalComDesconto / p.quantidade).toFixed(2)}">
                    ${p.id}
                </td>
                <td class="px-3 py-2 border">${escaparHTML(p.nome)}</td>
                <td class="px-3 py-2 border">${escaparHTML(p.partNumber)}</td>
                <td class="px-3 py-2 border">${escaparHTML(p.fornecedor)}</td>
                <td class="px-3 py-2 border">${escaparHTML(p.cor)}</td>
                <td class="px-3 py-2 border">
                    <input type="number" step="0.01" name="produtos[${index}][valor_unitario]" value="${p.preco.toFixed(2)}" 
                        ${p.liberarDesconto === 0 ? 'readonly' : ''}
                        onchange="alterarPrecoProdutoNovo(${i}, this.value)"
                        class="w-24 border rounded px-2 py-1 text-sm" />
                </td>
                <td class="px-3 py-2 border">
                    <input type="number" name="produtos[${index}][quantidade]" value="${p.quantidade}" min="1"
                        onchange="alterarQuantidade(${i}, this.value)"
                        class="w-16 border rounded px-2 py-1 text-center font-bold" />
                </td>
                <td class="px-3 py-2 border">R$ ${formatarMoeda(p.precoOriginal * p.quantidade)}</td>
                <td class="px-3 py-2 border text-green-600 font-bold">R$ ${formatarMoeda(subtotalComDesconto)}</td>
                <td class="px-3 py-2 border text-center">
                    <button type="button" onclick="removerProduto(${i})" class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition-colors">🗑</button>
                </td>
            `;
            wrapper.appendChild(row);
        });
        recalcularTotais();
    }

    // ==================== GESTÃO DE PRODUTOS ORIGINAIS ====================
    window.alterarPrecoProdutoOriginal = function(index, novoPreco) {
        const row = document.getElementById('produtos-originais').querySelectorAll('tr')[index];
        const liberarDesconto = parseInt(row.querySelector('input[name*="[liberar_desconto]"]').value);
        if (liberarDesconto === 0) {
            alert("Este produto não permite alteração de preço!");
            recalcularTotais();
            return;
        }

        const precoOriginal = parseFloat(row.querySelector('input[name*="[preco_original]"]').value);
        novoPreco = parseFloat(novoPreco) || precoOriginal;
        const descontoEmReais = precoOriginal - novoPreco;

        row.querySelector('.input-valor-unitario').value = novoPreco.toFixed(2);
        row.querySelector('.input-desconto-produto').value = descontoEmReais.toFixed(2);
        row.querySelector('.input-tipo-desconto').value = descontoEmReais > 0 ? 'produto' : 'percentual';

        recalcularTotais();
    };

    window.alterarQuantidadeOriginal = function(index, novaQuantidade) {
        const row = document.getElementById('produtos-originais').querySelectorAll('tr')[index];
        const quantidade = parseInt(novaQuantidade) || 1;
        const estoque = row.getAttribute('data-estoque') !== 'null' ? parseInt(row.getAttribute('data-estoque')) : null;

        if (estoque !== null && quantidade > estoque) {
            if (!confirm(`⚠️ Estoque insuficiente! Há apenas ${estoque} em estoque. Deseja salvar mesmo assim?`)) {
                row.querySelector('.input-quantidade').value = row.querySelector('.input-quantidade').getAttribute('data-valor-anterior') || 1;
                return;
            }
        }

        row.querySelector('.input-quantidade').setAttribute('data-valor-anterior', quantidade);
        recalcularTotais();
    };

    window.removerProdutoOriginal = function(index) {
        const row = document.getElementById('produtos-originais').querySelectorAll('tr')[index];
        row.style.display = 'none';
        let removeInput = row.querySelector('input[name*="[_remove]"]');
        if (!removeInput) {
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = `produtos[${index}][_remove]`;
            removeInput.value = '1';
            row.appendChild(removeInput);
        }
        recalcularTotais();
    };

    function recalcularProdutoOriginal(row, index) {
        const quantidade = parseInt(row.querySelector('.input-quantidade').value) || 1;
        const valorUnitario = parseFloat(row.querySelector('.input-valor-unitario').value) || 0;
        const descontoProduto = parseFloat(row.querySelector('.input-desconto-produto').value) || 0;
        const descontoPercentual = obterDescontoAplicado();

        const subtotal = valorUnitario * quantidade;
        let subtotalComDesconto;

        if (descontoProduto > 0) {
            subtotalComDesconto = subtotal;
        } else {
            subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
        }

        row.querySelector('.input-subtotal').value = subtotal.toFixed(2);
        row.querySelector('.input-subtotal-com-desconto').value = subtotalComDesconto.toFixed(2);
        row.querySelector('.input-preco-unitario-com-desconto').value = (subtotalComDesconto / quantidade).toFixed(2);

        row.querySelector('.label-subtotal').innerHTML = 'R$ ' + formatarMoeda(subtotal);
        row.querySelector('.label-subtotal-desconto').innerHTML = 'R$ ' + formatarMoeda(subtotalComDesconto);
    }

    // ==================== VIDROS ====================
    window.addVidro = function() {
        const wrapper = document.getElementById('vidros-wrapper');
        const vidroDiv = document.createElement('div');
        vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
        vidroDiv.innerHTML = `
            <div class="overflow-x-auto"><div class="flex gap-4 min-w-max">
                <div class="flex-1"><label class="block text-sm font-medium">Descrição</label>
                <input type="text" name="vidros[${window.vidroIndex}][descricao]" class="mt-1 block w-full border rounded px-3 py-2" /></div>
                <div class="flex-1"><label class="block text-sm font-medium">Quantidade</label>
                <input type="number" name="vidros[${window.vidroIndex}][quantidade]" value="1" oninput="calcularVidro(this)" class="mt-1 block w-full border rounded px-3 py-2" /></div>
                <div class="flex-1"><label class="block text-sm font-medium">Preço m²</label>
                <input type="number" step="0.01" name="vidros[${window.vidroIndex}][preco_m2]" oninput="calcularVidro(this)" class="mt-1 block w-full border rounded px-3 py-2" /></div>
                <div class="flex-1"><label class="block text-sm font-medium">Altura (mm)</label>
                <input type="number" name="vidros[${window.vidroIndex}][altura]" oninput="calcularVidro(this)" class="mt-1 block w-full border rounded px-3 py-2" /></div>
                <div class="flex-1"><label class="block text-sm font-medium">Largura (mm)</label>
                <input type="number" name="vidros[${window.vidroIndex}][largura]" oninput="calcularVidro(this)" class="mt-1 block w-full border rounded px-3 py-2" /></div>
            </div></div>
            <input type="hidden" name="vidros[${window.vidroIndex}][area]" class="area-hidden" value="0">
            <input type="hidden" name="vidros[${window.vidroIndex}][valor_total]" class="valor-hidden" value="0">
            <input type="hidden" name="vidros[${window.vidroIndex}][valor_com_desconto]" class="valor-desconto-hidden" value="0">
            <div class="mt-2 text-sm">
                <strong>Área:</strong> <span class="area">0.00</span> m² | 
                <strong>Valor:</strong> R$ <span class="valor">0.00</span> | 
                <strong>c/ desc:</strong> R$ <span class="valor-desconto">0.00</span>
                <button type="button" onclick="removeVidro(this)" class="absolute right-2 text-red-600">Remover</button>
            </div>
        `;
        wrapper.appendChild(vidroDiv);
        window.vidroIndex++;
    };

    window.removeVidro = function(button) {
        button.closest('div.space-y-2').remove();
        recalcularTotais();
    };

    window.calcularVidro = function(element) {
        const container = element.closest('div.space-y-2');
        const altura = parseFloat(container.querySelector('[name*="[altura]"]').value) || 0;
        const largura = parseFloat(container.querySelector('[name*="[largura]"]').value) || 0;
        const quantidade = parseFloat(container.querySelector('[name*="[quantidade]"]').value) || 1;
        const precoM2 = parseFloat(container.querySelector('[name*="[preco_m2]"]').value) || 0;

        const area = (altura / 1000) * (largura / 1000);
        const valor = area * precoM2 * quantidade;
        const desconto = obterDescontoAplicado();
        const valorComDesconto = valor - (valor * (desconto / 100));

        container.querySelector('.area').textContent = area.toFixed(2);
        container.querySelector('.valor').textContent = valor.toFixed(2);
        container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2);

        container.querySelector('.area-hidden').value = area.toFixed(2);
        container.querySelector('.valor-hidden').value = valor.toFixed(2);
        container.querySelector('.valor-desconto-hidden').value = valorComDesconto.toFixed(2);

        recalcularTotais();
    };

    // ==================== CÁLCULOS TOTAIS ====================
    function obterDescontoAplicado() {
        const descontoCliente = parseFloat(document.getElementById('desconto_aprovado')?.value) || 0;
        const descontoOrcamentoEl = document.querySelector('[name="desconto"]');
        let descontoOrcamento = parseFloat(descontoOrcamentoEl?.value?.replace(',', '.')) || 0;
        return Math.max(descontoOrcamento, descontoCliente);
    }

    function recalcularTotais() {
        console.log('Recalculando orçamento...');
        
        // 1. Produtos Originais
        let totalOriginais = 0, totalOriginaisDesc = 0;
        const rowsOriginais = document.getElementById('produtos-originais')?.querySelectorAll('tr') || [];
        rowsOriginais.forEach((row, i) => {
            if (row.style.display !== 'none') {
                recalcularProdutoOriginal(row, i);
                totalOriginais += parseFloat(row.querySelector('.input-subtotal').value) || 0;
                totalOriginaisDesc += parseFloat(row.querySelector('.input-subtotal-com-desconto').value) || 0;
            }
        });

        // 2. Produtos Novos
        let totalNovos = 0, totalNovosDesc = 0;
        const descPercent = obterDescontoAplicado();
        window.produtos.forEach(p => {
            const subtotal = p.preco * p.quantidade;
            let subtotalDesc = (p.descontoProduto > 0 || p.liberarDesconto === 0) ? subtotal : subtotal * (1 - descPercent/100);
            totalNovos += p.precoOriginal * p.quantidade;
            totalNovosDesc += subtotalDesc;
        });

        // 3. Vidros
        let totalVidros = 0, totalVidrosDesc = 0;
        document.querySelectorAll('#vidros-wrapper .space-y-2').forEach(v => {
            if (v.style.display !== 'none') {
                totalVidros += parseFloat(v.querySelector('.valor-hidden').value) || 0;
                totalVidrosDesc += parseFloat(v.querySelector('.valor-desconto-hidden').value) || 0;
            }
        });

        // 4. Encomendas (se houver)
        let totalEnc = 0, totalEncDesc = 0;
        document.querySelectorAll('#itens-encomenda tr').forEach(row => {
            const precoOri = parseFloat(row.querySelector('.enc-preco-original')?.value) || 0;
            const precoFinal = parseFloat(row.querySelector('.enc-preco-final')?.value) || precoOri;
            const qtd = parseFloat(row.dataset.quantidade) || 1;
            const subtotal = precoOri * qtd;
            let subtotalDesc = (row.querySelector('.enc-tipo-desconto')?.value === 'produto') ? precoFinal * qtd : subtotal * (1 - descPercent/100);
            totalEnc += subtotal;
            totalEncDesc += subtotalDesc;
        });

        const totalBruto = totalOriginais + totalNovos + totalVidros + totalEnc;
        const totalComDesc = totalOriginaisDesc + totalNovosDesc + totalVidrosDesc + totalEncDesc;

        const valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) valorTotalInput.value = totalBruto.toFixed(2);

        const guia = parseFloat(document.querySelector('[name="guia_recolhimento"]')?.value.replace(',', '.')) || 0;
        const descEsp = parseFloat(document.querySelector('[name="desconto_especifico"]')?.value.replace(',', '.')) || 0;

        let valorFinal = totalComDesc - descEsp + guia;
        const valorFinalInput = document.getElementById('valor_final');
        if (valorFinalInput) valorFinalInput.value = Math.max(0, valorFinal).toFixed(2);
    }

    // ==================== AUXILIARES ====================
    function formatarMoeda(v) { return parseFloat(v).toFixed(2).replace('.', ','); }
    function escaparHTML(t) { return String(t||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    // ==================== INICIALIZAÇÃO ====================
    function inicializar() {
        ['desconto', 'desconto_especifico', 'guia_recolhimento'].forEach(name => {
            document.querySelector(`[name="${name}"]`)?.addEventListener('input', () => {
                document.querySelectorAll('#vidros-wrapper .space-y-2').forEach(v => {
                    const inp = v.querySelector('input[type="number"]');
                    if (inp) calcularVidro(inp);
                });
                recalcularTotais();
            });
        });

        // Condição de pagamento
        const condSelect = document.getElementById('condicao_id');
        const outrosMeios = document.getElementById('outros_meios_pagamento');
        if (condSelect && outrosMeios) {
            const toggle = () => {
                const is20 = condSelect.value == '20';
                outrosMeios.disabled = !is20;
                outrosMeios.required = is20;
            };
            condSelect.addEventListener('change', toggle);
            toggle();
        }

        // Venda triangular
        const triSelect = document.getElementById('venda_triangular');
        const cnpjTri = document.getElementById('cnpj_triangular');
        if (triSelect && cnpjTri) {
            const toggle = () => {
                const isTri = triSelect.value === '1';
                cnpjTri.disabled = !isTri;
                cnpjTriangular.required = isTri;
            };
            triSelect.addEventListener('change', toggle);
            toggle();
        }

        // Itens iniciais
        for (let i = 1; i < oldItensRaw.length; i++) addItem();

        recalcularTotais();
        console.log('Sistema de orçamento pronto.');
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', inicializar);
    else inicializar();
</script>
