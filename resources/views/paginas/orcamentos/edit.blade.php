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
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            alert('⚠️ Atenção: a situação do CNPJ não está ATIVA na Receita Federal.');
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
                                                        <input type="number" step="0.01" min="0"
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
                                            <tr data-estoque="{{ $item->produto->estoque_atual ?? 'null' }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][produto_id]"
                                                    value="{{ $item->produto->id }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][valor_unitario]"
                                                    class="valor-unitario-hidden"
                                                    value="{{ $item->valor_unitario }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][part_number]"
                                                    value="{{ $item->produto->part_number ?? '' }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][quantidade]"
                                                    value="{{ $item->quantidade }}">
                                                <input type="hidden" name="produtos[{{ $loop->index }}][subtotal]"
                                                    value="{{ number_format($item->valor_unitario * $item->quantidade, 2, '.', '') }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][subtotal_com_desconto]"
                                                    value="{{ number_format($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0), 2, '.', '') }}">
                                                <input type="hidden"
                                                    name="produtos[{{ $loop->index }}][preco_unitario_com_desconto]"
                                                    value="{{ number_format(($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0)) / $item->quantidade, 2, '.', '') }}">

                                                <td class="px-3 py-2 border">{{ $item->produto->id }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->nome }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '' }}
                                                </td>
                                                <td class="px-3 py-2 border">
                                                    {{ $item->produto->fornecedor->nome ?? '' }}</td>
                                                <td class="px-3 py-2 border">{{ $item->produto->cor ?? '' }}</td>
                                                <td class="px-3 py-2 border">R$
                                                    {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                                <td class="px-3 py-2 border">
                                                    <input type="number"
                                                        name="produtos[{{ $loop->index }}][quantidade]"
                                                        value="{{ $item->quantidade }}" min="1"
                                                        onchange="alterarQuantidadeOriginal({{ $loop->index }}, this.value)"
                                                        class="w-12 border rounded px-2 py-1 text-center"
                                                        style="max-width: 4rem;" />
                                                </td>
                                                <td class="px-3 py-2 border">R$
                                                    {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 border text-green-600">R$
                                                    {{ number_format($item->valor_unitario * $item->quantidade - ($item->desconto ?? 0), 2, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 border text-center">
                                                    <button type="button"
                                                        onclick="removerProdutoOriginal({{ $loop->index }})"
                                                        class="text-red-600 hover:text-red-800">🗑</button>
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
                                            value="{{ $vidro->id }}" />
                                        <div class="overflow-x-auto">
                                            <div class="flex gap-4 min-w-max">
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Descrição</label>
                                                    <input type="text"
                                                        name="vidros_existentes[{{ $loop->index }}][descricao]"
                                                        value="{{ $vidro->descricao }}"
                                                        placeholder="Ex: Vidro incolor 8mm"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700">Quantidade</label>
                                                    <input type="number"
                                                        name="vidros_existentes[{{ $loop->index }}][quantidade]"
                                                        value="{{ $vidro->quantidade }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700">Preço
                                                        m²</label>
                                                    <input type="number" step="0.01"
                                                        name="vidros_existentes[{{ $loop->index }}][preco_m2]"
                                                        value="{{ $vidro->preco_metro_quadrado }}"
                                                        oninput="calcularVidroExistente(this)"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700">Altura
                                                        (mm)
                                                    </label>
                                                    <input type="number"
                                                        name="vidros_existentes[{{ $loop->index }}][altura]"
                                                        value="{{ $vidro->altura }}"
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
                                label="Nome da Obra" required :value="$orcamento->obra" />
                            <x-select name="complemento" label="Complemento de outro orçamento?" required>
                                <option value="Não">Não</option>
                                <option value="Sim">Sim</option>
                            </x-select>
                            <x-input type="text" name="prazo_entrega" placeholder="Ex: 15 dias úteis"
                                label="Prazo de Entrega" :value="$orcamento->prazo_entrega" />
                            <x-select name="frete" label="Tipo de Frete">
                                <option value="">Selecione...</option>
                                <option value="cif" @selected($orcamento->frete == 'cif')>CIF - entrega por conta do
                                    fornecedor</option>
                                <option value="fob" @selected($orcamento->frete == 'fob')>FOB - entrega por conta do cliente
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
                                    readonly="readonly" placeholder="Cidade" :value="$orcamento->endereco->cidade ?? ''" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" :value="$orcamento->endereco->estado ?? ''" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" :value="$orcamento->endereco->bairro ?? ''" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly" :value="$orcamento->endereco->logradouro ?? ''" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    :value="$orcamento->endereco->numero ?? ''" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc." :value="$orcamento->endereco->complemento ?? ''" />
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

                                    <input type="radio" name="tipos_transporte" value="{{ $opcao->id }}"
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
                                <option value="{{ $c->id }}" @selected($orcamento->condicao_id == $c->id)>
                                    {{ $c->nome }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input name="outros_meios_pagamento" id="outros_meios_pagamento" label="Outros meios pagamento" 
                            disabled placeholder="Ex: Boleto 28/56/84..." :value="$orcamento->outros_meios_pagamento" />

                        <flux:select name="tipo_documento" label="Nota fiscal">
                            <option value="">Selecione...</option>
                            <option value="Nota fiscal" @selected($orcamento->tipo_documento == 'Nota fiscal')>Nota fiscal</option>
                            <option value="Cupom Fiscal" @selected($orcamento->tipo_documento == 'Cupom Fiscal')>Cupom Fiscal</option>
                        </flux:select>

                        <flux:select name="homologacao" label="Homologação" required>
                            <option value="0" @selected($orcamento->homologacao == 0)>Não</option>
                            <option value="1" @selected($orcamento->homologacao == 1)>Sim</option>
                        </flux:select>

                        <flux:select name="venda_triangular" id="venda_triangular" label="Venda triangular?" required>
                            <option value="0" @selected($orcamento->venda_triangular == 0)>Não</option>
                            <option value="1" @selected($orcamento->venda_triangular == 1)>Sim</option>
                        </flux:select>

                        <flux:input name="cnpj_triangular" id="cnpj_triangular" label="CNPJ venda triangular" 
                            disabled size="18" maxlength="18" onkeypress="mascara(this, '##.###.###/####-##')" 
                            placeholder="00.000.000/0000-00" :value="$orcamento->cnpj_triangular" />
                    </div>

                    <!-- Seção de Valores e Descontos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <flux:input name="desconto" label="Desconto na vendedor %" 
                            :value="$desconto_percentual ?? old('desconto')" 
                            placeholder="0" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="desconto_especifico" label="Desconto específico R$" 
                            :value="$desconto_especifico ?? old('desconto_especifico')" 
                            placeholder="0.00" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="guia_recolhimento" label="Guia Recolhimento" 
                            :value="$orcamento->guia_recolhimento ?? 0" 
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
    // Dados passados do Laravel para JavaScript
    const cores = @json($cores);
    const fornecedores = @json($fornecedores);
    const oldItens = @json($itensParaJs ?? []);

    // Inicializa o índice baseado na quantidade de itens existentes
    let itemIndex = oldItens.length;

    /**
     * Adiciona um novo item ao formulário
     */
    function addItem() {
        const wrapper = document.getElementById('itens-wrapper');
        const itemDiv = document.createElement('div');
        itemDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";

        // Recupera valores old para este índice (se existirem)
        const oldData = oldItens[itemIndex] || {};

        // Monta opções de cores
        let coresOptions = `<option value="">Selecione...</option>`;
        cores.forEach(cor => {
            const selected = oldData.cor === cor.nome ? 'selected' : '';
            coresOptions += `<option value="${cor.nome}" ${selected}>${cor.nome}</option>`;
        });

        // Monta opções de fornecedores
        let fornecedoresOptions = `<option value="">Selecione...</option>`;
        fornecedores.forEach(f => {
            const selected = oldData.fornecedor_id == f.id ? 'selected' : '';
            fornecedoresOptions += `<option value="${f.id}" ${selected}>${f.nome_fantasia}</option>`;
        });

        // Campo hidden para ID (se existir no oldData - necessário para UPDATE)
        const idField = oldData.id ? `<input type="hidden" name="itens[${itemIndex}][id]" value="${oldData.id}" />` :
            '';

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

    /**
     * Remove um item do formulário
     */
    function removeItem(button) {
        const itemDiv = button.closest('.space-y-2');
        if (itemDiv) {
            itemDiv.remove();
        }
    }

    /**
     * Ao carregar a página, adiciona os itens que já existem (edição) ou old() (validação)
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Se houver itens existentes além do primeiro (índice 0 já existe no HTML)
        for (let i = 1; i < oldItens.length; i++) {
            addItem();
        }
    });
</script>

<script>
    // ==================== SUBSTITUIR TODO O JAVASCRIPT DO BLADE EDIT ====================

    console.log('Script carregando...');

    // ==================== VARIÁVEIS GLOBAIS ====================
    window.vidroIndex = 0;
    window.produtos = [];
    window.produtoSelecionado = null;

    // ==================== MODAL ====================
    window.selecionarProdutoComQuantidade = function(id, nome, preco, fornecedor, cor, partNumber, liberarDesconto,
        estoqueDisponivel = null) {
        window.produtoSelecionado = {
            id: id,
            nome: nome,
            preco: parseFloat(preco),
            precoOriginal: parseFloat(preco),
            fornecedor: fornecedor || '',
            cor: cor || '',
            partNumber: partNumber || '',
            liberarDesconto: parseInt(liberarDesconto || 1),
            quantidade: 1,
            descontoProduto: 0,
            estoqueDisponivel: estoqueDisponivel !== null ? parseInt(estoqueDisponivel) : null // ✅
        };

        document.getElementById('produto-nome').textContent = nome;
        document.getElementById('quantidade-produto').value = 1;

        // Reset visual do modal
        document.getElementById('aviso-estoque').classList.add('hidden');
        const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
        btnConfirmar.textContent = 'Adicionar';
        btnConfirmar.classList.add('bg-blue-500', 'hover:bg-blue-600');
        btnConfirmar.classList.remove('bg-amber-500', 'hover:bg-amber-600');

        const modalBody = document.getElementById('modal-quantidade').querySelector('.bg-white');
        const avisoExistente = modalBody.querySelector('.aviso-desconto-modal');
        if (avisoExistente) avisoExistente.remove();

        if (parseInt(liberarDesconto) === 0) {
            const aviso = document.createElement('div');
            aviso.className =
                'aviso-desconto-modal bg-red-50 border border-red-200 rounded p-2 mb-3 text-sm text-red-700';
            aviso.innerHTML = '⚠️ Este produto não permite desconto';
            const inputQuantidade = document.getElementById('quantidade-produto');
            inputQuantidade.parentNode.insertBefore(aviso, inputQuantidade);
        }

        document.getElementById('modal-quantidade').classList.remove('hidden');
    };

    window.fecharModal = function() {
        document.getElementById('modal-quantidade').classList.add('hidden');
        document.getElementById('aviso-estoque').classList.add('hidden');

        const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
        btnConfirmar.textContent = 'Adicionar';
        btnConfirmar.classList.add('bg-blue-500', 'hover:bg-blue-600');
        btnConfirmar.classList.remove('bg-amber-500', 'hover:bg-amber-600');

        window.produtoSelecionado = null;
    };

    window.confirmarQuantidade = function() {
        if (!window.produtoSelecionado) return;

        // ✅ Se já confirmou o aviso, adiciona direto
        if (window.produtoSelecionado._pendente) {
            _adicionarProdutoConfirmado(window.produtoSelecionado._pendente);
            return;
        }

        const quantidade = parseInt(document.getElementById('quantidade-produto').value) || 1;
        const estoque = window.produtoSelecionado.estoqueDisponivel;

        document.getElementById('aviso-estoque').classList.add('hidden');

        // ✅ Verifica estoque
        if (estoque !== null && estoque !== undefined && quantidade > estoque) {
            document.getElementById('aviso-estoque-texto').textContent =
                `Você solicitou ${quantidade} unidade(s), mas há apenas ${estoque} em estoque. ` +
                `O pedido será gerado, mas pode haver indisponibilidade na entrega.`;

            document.getElementById('aviso-estoque').classList.remove('hidden');

            const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
            btnConfirmar.textContent = '⚠️ Estou ciente, adicionar mesmo assim';
            btnConfirmar.classList.remove('bg-blue-500', 'hover:bg-blue-600');
            btnConfirmar.classList.add('bg-amber-500', 'hover:bg-amber-600');

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
            window.produtoSelecionado.estoqueDisponivel // ✅ passa estoque
        );

        window.fecharModal();
    }
    // ==================== PRODUTOS NOVOS ====================
    window.adicionarProduto = function(id, nome, preco, fornecedor, cor, partNumber, quantidade, liberarDesconto,
        estoqueDisponivel = null) {
        // Verificar duplicados
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
            estoqueDisponivel: estoqueDisponivel !== null ? parseInt(estoqueDisponivel) : null // ✅
        });

        renderProdutosNovos();
    };

    window.alterarPrecoProdutoNovo = function(index, novoPreco) {
        const produto = window.produtos[index];

        if (produto.liberarDesconto === 0) {
            alert("Este produto não permite alteração de preço!");
            return;
        }

        novoPreco = parseFloat(novoPreco) || produto.precoOriginal;
        if (novoPreco < 0) novoPreco = 0;

        const descontoEmReais = produto.precoOriginal - novoPreco;

        window.produtos[index].preco = novoPreco;
        window.produtos[index].descontoProduto = descontoEmReais;

        renderProdutosNovos();
    };

    window.alterarQuantidade = function(index, valor) {
        if (!window.produtos[index]) return;

        const novaQuantidade = parseInt(valor) || 1;
        const produto = window.produtos[index];

        // ✅ Valida estoque
        if (
            produto.estoqueDisponivel !== null &&
            produto.estoqueDisponivel !== undefined &&
            novaQuantidade > produto.estoqueDisponivel
        ) {
            const confirmado = confirm(
                `⚠️ Estoque insuficiente!\n\n` +
                `Você solicitou ${novaQuantidade} unidade(s) de "${produto.nome}", ` +
                `mas há apenas ${produto.estoqueDisponivel} em estoque.\n\n` +
                `Deseja adicionar mesmo assim?`
            );
            if (!confirmado) {
                renderProdutosNovos(); // Reverte o input
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

        const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]')?.value) || 0;
        let descontoOrcamento = parseFloat((document.querySelector('[name="desconto"]')?.value || '').replace(',', '.')) || 0;
        descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);
        const descontoPercentual = Math.max(descontoCliente, descontoOrcamento);

        window.produtos.forEach((p, i) => {
            const valorUnitarioAtual = p.preco;
            const subtotal = valorUnitarioAtual * p.quantidade;

            let subtotalComDesconto;
            let descontoEfetivo = 0;
            let tipoDesconto = 'nenhum';

            if (p.descontoProduto > 0) {
                subtotalComDesconto = subtotal;
                tipoDesconto = 'produto';
                descontoEfetivo = ((p.descontoProduto / p.precoOriginal) * 100).toFixed(2);
            } else if (p.liberarDesconto === 1 && descontoPercentual > 0) {
                subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
                descontoEfetivo = descontoPercentual;
                tipoDesconto = 'percentual';
            } else {
                subtotalComDesconto = subtotal;
            }

            let descontoStatus = '';
            if (p.liberarDesconto === 0) {
                descontoStatus =
                    '<span class="inline-block px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Desconto bloqueado</span>';
            } else if (tipoDesconto === 'produto') {
                descontoStatus =
                    `<span class="inline-block px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">Preço alterado (-R$ ${p.descontoProduto.toFixed(2)})</span>`;
            } else if (tipoDesconto === 'percentual') {
                descontoStatus =
                    `<span class="inline-block px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">${descontoEfetivo}% aplicado</span>`;
            }

            const row = document.createElement('tr');
            row.className = p.liberarDesconto === 0 ? 'bg-red-50 dark:bg-red-900/10' : '';

            row.innerHTML = `
            <td class="px-3 py-2 border">
                <input type="hidden" name="itens[${i}][id]" value="${p.id}">
                <input type="hidden" name="itens[${i}][liberar_desconto]" value="${p.liberarDesconto}">
                <input type="hidden" name="itens[${i}][preco_original]" value="${p.precoOriginal}">
                <input type="hidden" name="itens[${i}][desconto_produto]" value="${p.descontoProduto}">
                <input type="hidden" name="itens[${i}][tipo_desconto]" value="${tipoDesconto}">
                ${p.id}
            </td>
            <td class="px-3 py-2 border">
                ${escaparHTML(p.nome)}
                <div class="mt-1">${descontoStatus}</div>
            </td>
            <td class="px-3 py-2 border">${escaparHTML(p.partNumber)}</td>
            <td class="px-3 py-2 border">${escaparHTML(p.fornecedor)}</td>
            <td class="px-3 py-2 border">${escaparHTML(p.cor)}</td>
            <td class="px-3 py-2 border">
                ${p.liberarDesconto === 1 ? `
                    <div class="flex flex-col gap-1">
                        <input type="number" step="0.01" value="${valorUnitarioAtual.toFixed(2)}"
                            onchange="alterarPrecoProdutoNovo(${i}, this.value)"
                            class="w-24 border rounded px-2 py-1 text-sm" />
                        ${p.descontoProduto > 0 ? `<small class="text-xs text-gray-500">Original: R$ ${p.precoOriginal.toFixed(2)}</small>` : ''}
                    </div>
                ` : `R$ ${valorUnitarioAtual.toFixed(2)}`}
                <input type="hidden" name="itens[${i}][preco_unitario]" value="${valorUnitarioAtual}">
            </td>
            <td class="px-3 py-2 border">
                <input type="number" name="itens[${i}][quantidade]" value="${p.quantidade}" min="1"
                    onchange="alterarQuantidade(${i}, this.value)"
                    class="w-12 border rounded px-2 py-1 text-center" style="max-width:4rem"/>
            </td>
            <td class="px-3 py-2 border">
                R$ ${formatarMoeda(p.precoOriginal * p.quantidade)}
                <input type="hidden" name="itens[${i}][subtotal_original]" value="${(p.precoOriginal * p.quantidade).toFixed(2)}">
                <input type="hidden" name="itens[${i}][subtotal]" value="${subtotal.toFixed(2)}">
            </td>
            <td class="px-3 py-2 border ${tipoDesconto !== 'nenhum' ? 'text-green-600' : 'text-gray-600'}">
                R$ ${formatarMoeda(subtotalComDesconto)}
                <input type="hidden" name="itens[${i}][subtotal_com_desconto]" value="${subtotalComDesconto.toFixed(2)}">
                <input type="hidden" name="itens[${i}][preco_unitario_com_desconto]" value="${(subtotalComDesconto / p.quantidade).toFixed(2)}">
                <input type="hidden" name="itens[${i}][desconto_percentual_aplicado]" value="${tipoDesconto === 'percentual' ? descontoPercentual : 0}">
            </td>
            <td class="px-3 py-2 border text-center">
                <button type="button" onclick="removerProduto(${i})"
                        class="text-red-600 hover:text-red-800">🗑</button>
            </td>
        `;

            wrapper.appendChild(row);
        });

        recalcularTotais();
    }

    // ==================== PRODUTOS ORIGINAIS ====================
    window.alterarPrecoProdutoOriginal = function(index, novoPreco) {
        const tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        const row = tbody.querySelectorAll('tr')[index];
        if (!row || row.style.display === 'none') return;

        const liberarDescontoInput = row.querySelector('input[name="produtos[' + index + '][liberar_desconto]"]');
        const liberarDesconto = liberarDescontoInput ? parseInt(liberarDescontoInput.value) : 1;

        if (liberarDesconto === 0) {
            alert("Este produto não permite alteração de preço!");
            return;
        }

        const precoOriginalInput = row.querySelector('input[name="produtos[' + index + '][preco_original]"]');
        const precoOriginal = precoOriginalInput ? parseFloat(precoOriginalInput.value) : 0;

        novoPreco = parseFloat(novoPreco) || precoOriginal;
        if (novoPreco < 0) novoPreco = 0;

        const descontoEmReais = precoOriginal - novoPreco;

        // Atualizar valor unitário
        const valorUnitarioInput = row.querySelector('.valor-unitario-hidden');
        if (valorUnitarioInput) {
            valorUnitarioInput.value = novoPreco;
        }

        // Atualizar desconto produto
        let descontoProdutoInput = row.querySelector('input[name="produtos[' + index + '][desconto_produto]"]');
        if (!descontoProdutoInput) {
            descontoProdutoInput = document.createElement('input');
            descontoProdutoInput.type = 'hidden';
            descontoProdutoInput.name = 'produtos[' + index + '][desconto_produto]';
            row.appendChild(descontoProdutoInput);
        }
        descontoProdutoInput.value = descontoEmReais;

        // Atualizar tipo desconto
        let tipoDescontoInput = row.querySelector('input[name="produtos[' + index + '][tipo_desconto]"]');
        if (!tipoDescontoInput) {
            tipoDescontoInput = document.createElement('input');
            tipoDescontoInput.type = 'hidden';
            tipoDescontoInput.name = 'produtos[' + index + '][tipo_desconto]';
            row.appendChild(tipoDescontoInput);
        }
        tipoDescontoInput.value = descontoEmReais > 0 ? 'produto' : 'percentual';

        const quantidadeInput = row.querySelector('input[name="produtos[' + index + '][quantidade]"]');
        const quantidade = quantidadeInput ? parseInt(quantidadeInput.value) : 1;

        recalcularProdutoOriginal(row, index, quantidade, novoPreco, descontoEmReais);
        recalcularTotais();
    };

    window.alterarQuantidadeOriginal = function(index, novaQuantidade) {
        const tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        const row = tbody.querySelectorAll('tr')[index];
        if (!row || row.style.display === 'none') return;

        const quantidade = parseInt(novaQuantidade) || 1;

        // ✅ Verifica estoque via data-attribute da row
        const estoqueAttr = row.getAttribute('data-estoque');
        const estoque = estoqueAttr !== 'null' && estoqueAttr !== null ? parseInt(estoqueAttr) : null;

        if (estoque !== null && quantidade > estoque) {
            const nomeProduto = row.querySelectorAll('td')[1]?.textContent?.trim() || 'este produto';
            const confirmado = confirm(
                `⚠️ Estoque insuficiente!\n\n` +
                `Você solicitou ${quantidade} unidade(s) de "${nomeProduto}", ` +
                `mas há apenas ${estoque} em estoque.\n\n` +
                `Deseja salvar mesmo assim?`
            );
            if (!confirmado) {
                // Reverte o input para o valor anterior
                const quantidadeInput = row.querySelector(`input[name="produtos[${index}][quantidade]"]`);
                if (quantidadeInput) {
                    quantidadeInput.value = window.produtos[index]?.quantidade ||
                        quantidadeInput.getAttribute('data-valor-anterior') || 1;
                }
                return;
            }
        }

        // Guarda o valor atual antes de alterar (para reverter se necessário)
        const quantidadeInput = row.querySelector(`input[name="produtos[${index}][quantidade]"]`);
        if (quantidadeInput) {
            quantidadeInput.setAttribute('data-valor-anterior', quantidade);
            quantidadeInput.value = quantidade;
        }

        const valorUnitarioInput = row.querySelector('.valor-unitario-hidden');
        const valorUnitario = valorUnitarioInput ? parseFloat(valorUnitarioInput.value) : 0;

        const descontoProdutoInput = row.querySelector(`input[name="produtos[${index}][desconto_produto]"]`);
        const descontoProduto = descontoProdutoInput ? parseFloat(descontoProdutoInput.value) : 0;

        recalcularProdutoOriginal(row, index, quantidade, valorUnitario, descontoProduto);
        recalcularTotais();
    };

    window.removerProdutoOriginal = function(index) {
        const tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        const row = tbody.querySelectorAll('tr')[index];
        if (row) {
            row.style.display = 'none';

            let removeInput = row.querySelector('input[name="produtos[' + index + '][_remove]"]');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'produtos[' + index + '][_remove]';
                removeInput.value = '1';
                row.appendChild(removeInput);
            }
        }

        recalcularTotais();
    };

    function recalcularProdutoOriginal(row, index, quantidade, valorUnitario, descontoProduto) {
        const precoOriginalInput = row.querySelector('input[name="produtos[' + index + '][preco_original]"]');
        const precoOriginal = precoOriginalInput ? parseFloat(precoOriginalInput.value) : valorUnitario;

        const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado]"')?.value) || 0;
        let descontoOrcamento = parseFloat((document.querySelector('[name="desconto"]')?.value || '').replace(',', '.')) || 0;
        descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);
        const descontoPercentual = Math.max(descontoCliente, descontoOrcamento);

        const subtotalOriginal = precoOriginal * quantidade;
        const subtotal = valorUnitario * quantidade;

        let subtotalComDesconto;
        let tipoDesconto = 'nenhum';

        if (descontoProduto > 0) {
            subtotalComDesconto = subtotal;
            tipoDesconto = 'produto';
        } else {
            subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
            tipoDesconto = 'percentual';
        }

        const precoUnitarioComDesconto = subtotalComDesconto / quantidade;

        // Atualizar inputs hidden
        const subtotalInput = row.querySelector('input[name="produtos[' + index + '][subtotal]"]');
        const subtotalComDescontoInput = row.querySelector('input[name="produtos[' + index +
            '][subtotal_com_desconto]"]');
        const precoComDescontoInput = row.querySelector('input[name="produtos[' + index +
            '][preco_unitario_com_desconto]"]');

        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
        if (subtotalComDescontoInput) subtotalComDescontoInput.value = subtotalComDesconto.toFixed(2);
        if (precoComDescontoInput) precoComDescontoInput.value = precoUnitarioComDesconto.toFixed(2);

        // Atualizar células visíveis
        const cells = row.querySelectorAll('td');
        if (cells[7]) cells[7].innerHTML = 'R$ ' + formatarMoeda(subtotal);
        if (cells[8]) cells[8].innerHTML = 'R$ ' + formatarMoeda(subtotalComDesconto);
    }

    function recalcularTodosProdutosOriginais() {
        const tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.style.display === 'none') continue;

            const quantidadeInput = row.querySelector('input[name="produtos[' + i + '][quantidade]"]');
            if (!quantidadeInput) continue;

            const quantidade = parseInt(quantidadeInput.value) || 1;
            const valorUnitarioInput = row.querySelector('.valor-unitario-hidden');
            const valorUnitario = valorUnitarioInput ? parseFloat(valorUnitarioInput.value) : 0;

            const descontoProdutoInput = row.querySelector('input[name="produtos[' + i + '][desconto_produto]"]');
            const descontoProduto = descontoProdutoInput ? parseFloat(descontoProdutoInput.value) : 0;

            recalcularProdutoOriginal(row, i, quantidade, valorUnitario, descontoProduto);
        }
    }

    // ==================== VIDROS (mantém mesma lógica) ====================
    window.addVidro = function() {
        const wrapper = document.getElementById('vidros-wrapper');
        if (!wrapper) return;

        const vidroDiv = document.createElement('div');
        vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
        vidroDiv.innerHTML =
            '<div class="overflow-x-auto"><div class="flex gap-4 min-w-max">' +
            '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Descrição</label>' +
            '<input type="text" name="vidros[' + window.vidroIndex +
            '][descricao]" placeholder="Ex: Vidro incolor 8mm" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
            '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Quantidade</label>' +
            '<input type="number" name="vidros[' + window.vidroIndex +
            '][quantidade]" value="1" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
            '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Preço m²</label>' +
            '<input type="number" step="0.01" name="vidros[' + window.vidroIndex +
            '][preco_m2]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
            '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Altura (mm)</label>' +
            '<input type="number" name="vidros[' + window.vidroIndex +
            '][altura]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
            '<div class="flex-1"><label class="block text-sm font-medium text-gray-700">Largura (mm)</label>' +
            '<input type="number" name="vidros[' + window.vidroIndex +
            '][largura]" oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" /></div>' +
            '</div></div>' +
            '<input type="hidden" name="vidros[' + window.vidroIndex + '][area]" class="area-hidden" />' +
            '<input type="hidden" name="vidros[' + window.vidroIndex + '][valor_total]" class="valor-hidden" />' +
            '<input type="hidden" name="vidros[' + window.vidroIndex +
            '][valor_com_desconto]" class="valor-desconto-hidden" />' +
            '<div class="mt-2 text-sm"><strong>Área:</strong> <span class="area">0.00</span> m² | ' +
            '<strong>Valor:</strong> R$ <span class="valor">0.00</span> | ' +
            '<strong>c/ desc:</strong> R$ <span class="valor-desconto">0.00</span>' +
            '<button type="button" onclick="removeVidro(this)" class="absolute right-2 text-red-600 hover:text-red-800" style="padding-top: -1rem;">Remover</button></div>';

        wrapper.appendChild(vidroDiv);
        window.vidroIndex++;
    };

    window.removeVidro = function(button) {
        button.closest('div.space-y-2').remove();
        recalcularTotais();
    };

    window.removeVidroExistente = function(button) {
        const container = button.closest('div.space-y-2');
        const vidroId = container.getAttribute('data-vidro-id');

        if (vidroId) {
            let removeInput = container.querySelector('input[name="vidros_removidos[]"]');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'vidros_removidos[]';
                removeInput.value = vidroId;
                container.appendChild(removeInput);
            }
        }

        container.style.display = 'none';
        recalcularTotais();
    };

    window.calcularVidro = function(element) {
        const container = element.closest('div.space-y-2');
        if (!container) return;

        const altura = parseFloat(container.querySelector('[name*="[altura]"]').value) || 0;
        const largura = parseFloat(container.querySelector('[name*="[largura]"]').value) || 0;
        const quantidade = parseFloat(container.querySelector('[name*="[quantidade]"]').value) || 1;
        const precoM2 = parseFloat(container.querySelector('[name*="[preco_m2]"]').value) || 0;

        const area = (altura / 1000) * (largura / 1000);
        const valor = area * precoM2 * quantidade;

        const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]')?.value) || 0;
        let descontoOrcamento = parseFloat((document.querySelector('[name="desconto"]')?.value || '').replace(',', '.')) || 0;
        descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);
        const desconto = Math.max(descontoCliente, descontoOrcamento);

        const valorComDesconto = valor - (valor * (desconto / 100));

        container.querySelector('.area').textContent = area.toFixed(2);
        container.querySelector('.valor').textContent = valor.toFixed(2);
        container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2);

        container.querySelector('.area-hidden').value = area.toFixed(2);
        container.querySelector('.valor-hidden').value = valor.toFixed(2);
        container.querySelector('.valor-desconto-hidden').value = valorComDesconto.toFixed(2);

        setTimeout(recalcularTotais, 10);
    };

    window.calcularVidroExistente = function(element) {
        calcularVidro(element);
    };

    function calcularTotalVidros() {
        let totalVidros = 0;
        let totalVidrosComDesconto = 0;
        const wrapper = document.getElementById('vidros-wrapper');

        if (wrapper) {
            const containers = wrapper.querySelectorAll('.space-y-2');
            for (let container of containers) {
                if (container.style.display === 'none') continue;

                const valorEl = container.querySelector('.valor-hidden');
                const valorDescEl = container.querySelector('.valor-desconto-hidden');
                if (valorEl && valorDescEl) {
                    totalVidros += parseFloat(valorEl.value) || 0;
                    totalVidrosComDesconto += parseFloat(valorDescEl.value) || 0;
                }
            }
        }

        return {
            totalVidros,
            totalVidrosComDesconto
        };
    }

    function recalcularTodosVidros() {
        const wrapper = document.getElementById('vidros-wrapper');
        if (!wrapper) return;

        const containers = wrapper.querySelectorAll('.space-y-2');
        for (let container of containers) {
            if (container.style.display === 'none') continue;

            const firstInput = container.querySelector('input[type="number"]');
            if (firstInput) {
                calcularVidro(firstInput);
            }
        }
    }

    // ==================== TOTAIS ====================
    function obterDescontoAplicado() {
        const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]')?.value) || 0;
        // Normaliza o campo de desconto do orçamento:
        // - remove tudo que não for dígito
        // - interpreta como inteiro de 0 a 100 (ex.: "45" => 45)
        let raw = (document.querySelector('[name="desconto"]')?.value || '').toString();
        raw = raw.replace(/[^\d]/g, '');
        let descontoOrcamento = raw ? parseInt(raw, 10) : 0;
        descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);
        // Se o usuário informar um desconto no campo do orçamento, usa esse valor;
        // se deixar em branco/zero, cai no desconto aprovado do cliente (se existir).
        return descontoOrcamento > 0 ? descontoOrcamento : descontoCliente;
    }

    function calcularTotalProdutosOriginais() {
        let total = 0;
        let totalComDesconto = 0;
        const tbody = document.getElementById('produtos-originais');

        if (tbody) {
            const rows = tbody.querySelectorAll('tr');
            for (let row of rows) {
                if (row.style.display === 'none') continue;

                const subtotalInput = row.querySelector('input[name*="[subtotal]"]:not([name*="com_desconto"])');
                const subtotalComDescontoInput = row.querySelector('input[name*="[subtotal_com_desconto]"]');

                if (subtotalInput && subtotalComDescontoInput) {
                    total += parseFloat(subtotalInput.value) || 0;
                    totalComDesconto += parseFloat(subtotalComDescontoInput.value) || 0;
                }
            }
        }

        return {
            total,
            totalComDesconto
        };
    }
    // ==================== CONTINUAÇÃO DO JAVASCRIPT ====================

    function calcularTotalProdutosNovos() {
        let total = 0;
        let totalComDesconto = 0;
        const descontoPercentual = obterDescontoAplicado();

        window.produtos.forEach(p => {
            const subtotalOriginal = p.precoOriginal * p.quantidade;
            const subtotal = p.preco * p.quantidade;

            let subtotalComDesc;
            if (p.descontoProduto > 0) {
                subtotalComDesc = subtotal;
            } else if (p.liberarDesconto === 1 && descontoPercentual > 0) {
                subtotalComDesc = subtotal - (subtotal * (descontoPercentual / 100));
            } else {
                subtotalComDesc = subtotal;
            }

            total += subtotalOriginal;
            totalComDesconto += subtotalComDesc;
        });

        return {
            total,
            totalComDesconto
        };
    }

    function recalcularTotais() {
        recalcularTodosProdutosOriginais();

        const totaisOriginais = calcularTotalProdutosOriginais();
        const totaisNovos = calcularTotalProdutosNovos();
        const totaisVidros = calcularTotalVidros();

        const totalGeral = totaisOriginais.total + totaisNovos.total + totaisVidros.totalVidros;
        const totalGeralComDesconto = totaisOriginais.totalComDesconto + totaisNovos.totalComDesconto + totaisVidros
            .totalVidrosComDesconto;

        const valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) {
            valorTotalInput.value = totalGeral.toFixed(2);
        }

        const guia = parseFloat(document.querySelector('[name="guia_recolhimento"]')?.value) || 0;
        const descontoEspecifico = parseFloat(document.querySelector('[name="desconto_especifico"]')?.value) || 0;

        let valorFinal = totalGeralComDesconto - descontoEspecifico + guia;
        if (valorFinal < 0) valorFinal = 0;

        const maxDesconto = totalGeralComDesconto + guia;
        if (descontoEspecifico > maxDesconto) {
            const descontoEspecificoInput = document.querySelector('[name="desconto_especifico"]');
            if (descontoEspecificoInput) {
                descontoEspecificoInput.value = maxDesconto.toFixed(2);
            }
        }

        const valorFinalInput = document.getElementById('valor_final');
        if (valorFinalInput) {
            valorFinalInput.value = valorFinal.toFixed(2);
        }
    }

    // ==================== FUNÇÕES AUXILIARES ====================
    function escaparHTML(texto) {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatarMoeda(valor) {
        return parseFloat(valor).toFixed(2).replace('.', ',');
    }
    window.recalcularItemEncomenda = function(input) {
        const index = input.getAttribute('data-index');
        const row = input.closest('tr');
        const precoOri = parseFloat(row.querySelector('.enc-preco-original').value) || 0;
        const qtd = parseFloat(row.querySelector('input[name="encomenda_itens[' + index + '][quantidade]"]')
            .value) || 1;
        // Input é o valor novo unitário (preço com desconto) — vendedor informa o novo preço, não o valor do desconto
        let valorNovo = parseFloat(input.value) || precoOri;
        valorNovo = Math.max(0, Math.min(valorNovo, precoOri));

        const precoFinal = valorNovo;
        const descItem = precoOri - precoFinal;
        const subtotal = precoOri * qtd;
        const subtotalComDesconto = precoFinal * qtd;

        // Atualiza hiddens
        row.querySelector('.enc-desconto-item').value = descItem.toFixed(2);
        row.querySelector('.enc-preco-final').value = precoFinal.toFixed(2);
        row.querySelector('.enc-tipo-desconto').value = descItem > 0 ? 'produto' : 'percentual';

        // Atualiza display
        row.querySelector('.enc-subtotal').textContent = formatarMoeda(subtotal);
        row.querySelector('.enc-subtotal-desc').textContent = formatarMoeda(subtotalComDesconto);

        recalcularTotaisEncomenda();
    };

    function recalcularTotaisEncomenda() {
        // Aplica desconto percentual global nos itens de encomenda sem desconto individual
        const descontoPercentual = obterDescontoAplicado();
        let totalEncomenda = 0;
        let totalEncomendaComDesc = 0;

        document.querySelectorAll('#itens-encomenda tr').forEach(function(row) {
            const precoOri = parseFloat(row.querySelector('.enc-preco-original')?.value) || 0;
            const precoFinal = parseFloat(row.querySelector('.enc-preco-final')?.value) || precoOri;
            const qtd = parseFloat(row.dataset.quantidade) || 1;
            const tipo = row.querySelector('.enc-tipo-desconto')?.value;

            const subtotal = precoOri * qtd;
            let subtotalDesc;

            if (tipo === 'produto') {
                // Valor novo unitário definido manualmente pelo vendedor (ignora percentual global)
                subtotalDesc = precoFinal * qtd;
            } else if (descontoPercentual > 0) {
                // Aplica percentual global
                subtotalDesc = subtotal - (subtotal * (descontoPercentual / 100));
                // Atualiza o hidden preco_final com valor pós-percentual
                const precoFinalInput = row.querySelector('.enc-preco-final');
                if (precoFinalInput) {
                    precoFinalInput.value = ((precoOri) - (precoOri * descontoPercentual / 100)).toFixed(4);
                }
            } else {
                subtotalDesc = subtotal;
            }

            // Atualiza display da coluna c/ desconto
            const spanDesc = row.querySelector('.enc-subtotal-desc');
            if (spanDesc) spanDesc.textContent = formatarMoeda(subtotalDesc);

            totalEncomenda += subtotal;
            totalEncomendaComDesc += subtotalDesc;
        });

        return {
            totalEncomenda,
            totalEncomendaComDesc
        };
    }

    // Sobrescreve recalcularTotais para incluir encomenda
    const _recalcularTotaisOriginal = recalcularTotais;
    recalcularTotais = function() {
        recalcularTodosProdutosOriginais();

        const totaisOriginais = calcularTotalProdutosOriginais();
        const totaisNovos = calcularTotalProdutosNovos();
        const totaisVidros = calcularTotalVidros();
        const totaisEncomenda = recalcularTotaisEncomenda();

        const totalGeral = totaisOriginais.total + totaisNovos.total +
            totaisVidros.totalVidros + totaisEncomenda.totalEncomenda;

        const totalGeralComDesconto = totaisOriginais.totalComDesconto + totaisNovos.totalComDesconto +
            totaisVidros.totalVidrosComDesconto + totaisEncomenda.totalEncomendaComDesc;

        const valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) valorTotalInput.value = totalGeral.toFixed(2);

        const guia = parseFloat(document.querySelector('[name="guia_recolhimento"]')?.value) || 0;
        const descontoEspecifico = parseFloat(document.querySelector('[name="desconto_especifico"]')?.value) || 0;

        let valorFinal = totalGeralComDesconto - descontoEspecifico + guia;
        if (valorFinal < 0) valorFinal = 0;

        const valorFinalInput = document.getElementById('valor_final');
        if (valorFinalInput) valorFinalInput.value = valorFinal.toFixed(2);
    };
    // ==================== INICIALIZAÇÃO ====================
    function inicializar() {
        console.log('Inicializando sistema de orçamento - EDIT...');

        // Inicializar vidroIndex
        const vidrosWrapper = document.getElementById('vidros-wrapper');
        if (vidrosWrapper) {
            const vidrosExistentes = vidrosWrapper.querySelectorAll('.space-y-2');
            window.vidroIndex = vidrosExistentes.length;
        }

        // Adicionar campos necessários aos produtos originais
        const tbody = document.getElementById('produtos-originais');
        if (tbody) {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const produtoId = row.querySelector('input[name="produtos[' + index + '][produto_id]"]')?.value;
                if (!produtoId) return;

                // Buscar dados do produto para verificar liberar_desconto
                // Por enquanto, assume que todos permitem desconto por padrão
                const valorUnitarioInput = row.querySelector('.valor-unitario-hidden');
                const valorUnitario = valorUnitarioInput ? parseFloat(valorUnitarioInput.value) : 0;

                // Adicionar campo preco_original se não existir
                if (!row.querySelector('input[name="produtos[' + index + '][preco_original]"]')) {
                    const precoOriginalInput = document.createElement('input');
                    precoOriginalInput.type = 'hidden';
                    precoOriginalInput.name = 'produtos[' + index + '][preco_original]';
                    precoOriginalInput.value = valorUnitario;
                    row.appendChild(precoOriginalInput);
                }

                // Adicionar campo liberar_desconto se não existir
                if (!row.querySelector('input[name="produtos[' + index + '][liberar_desconto]"]')) {
                    const liberarDescontoInput = document.createElement('input');
                    liberarDescontoInput.type = 'hidden';
                    liberarDescontoInput.name = 'produtos[' + index + '][liberar_desconto]';
                    liberarDescontoInput.value = '1'; // Assume que permite desconto
                    row.appendChild(liberarDescontoInput);
                }

                // Adicionar campo desconto_produto se não existir
                if (!row.querySelector('input[name="produtos[' + index + '][desconto_produto]"]')) {
                    const descontoProdutoInput = document.createElement('input');
                    descontoProdutoInput.type = 'hidden';
                    descontoProdutoInput.name = 'produtos[' + index + '][desconto_produto]';
                    descontoProdutoInput.value = '0';
                    row.appendChild(descontoProdutoInput);
                }

                // Adicionar campo tipo_desconto se não existir
                if (!row.querySelector('input[name="produtos[' + index + '][tipo_desconto]"]')) {
                    const tipoDescontoInput = document.createElement('input');
                    tipoDescontoInput.type = 'hidden';
                    tipoDescontoInput.name = 'produtos[' + index + '][tipo_desconto]';
                    tipoDescontoInput.value = 'percentual';
                    row.appendChild(tipoDescontoInput);
                }

                // Tornar o preço editável
                const cells = row.querySelectorAll('td');
                if (cells[5]) { // Coluna do preço
                    const liberarDesconto = parseInt(row.querySelector('input[name="produtos[' + index +
                        '][liberar_desconto]"]')?.value) || 1;

                    if (liberarDesconto === 1) {
                        const precoAtual = valorUnitario.toFixed(2);
                        cells[5].innerHTML = `
                        <div class="flex flex-col gap-1">
                            <input type="number" step="0.01" value="${precoAtual}"
                                onchange="alterarPrecoProdutoOriginal(${index}, this.value)"
                                class="w-24 border rounded px-2 py-1 text-sm" />
                        </div>
                        <input type="hidden" class="valor-unitario-hidden" name="produtos[${index}][valor_unitario]" value="${valorUnitario}">
                    `;
                    }
                }
            });
        }

        // Recalcular vidros existentes
        recalcularTodosVidros();

        // Recalcular tudo
        recalcularTotais();

        // Event listeners
        const camposDesconto = [
            '[name="desconto"]',
            '[name="desconto_especifico"]',
            '[name="guia_recolhimento"]'
        ];

        camposDesconto.forEach(selector => {
            const campo = document.querySelector(selector);
            if (campo) {
                campo.addEventListener('input', function() {
                    recalcularTodosVidros();
                    recalcularTotais();
                });
            }
        });

        // Listener para condição de pagamento
        const condicaoPagamentoSelect = document.getElementById('condicao_id');
        const outrosMeiosInput = document.getElementById('outros_meios_pagamento');

        if (condicaoPagamentoSelect && outrosMeiosInput) {
            function toggleOutrosMeios() {
                if (condicaoPagamentoSelect.value == 20) {
                    outrosMeiosInput.disabled = false;
                    outrosMeiosInput.required = true;
                } else {
                    outrosMeiosInput.disabled = true;
                    outrosMeiosInput.required = false;
                    outrosMeiosInput.value = '';
                }
            }

            condicaoPagamentoSelect.addEventListener('change', toggleOutrosMeios);
            toggleOutrosMeios();
        }

        // Listener para venda triangular
        const vendaTriangularSelect = document.getElementById('venda_triangular');
        const cnpjTriangularInput = document.getElementById('cnpj_triangular');

        if (vendaTriangularSelect && cnpjTriangularInput) {
            function toggleVendaTriangular() {
                if (vendaTriangularSelect.value === '1') {
                    cnpjTriangularInput.disabled = false;
                    cnpjTriangularInput.required = true;
                } else {
                    cnpjTriangularInput.disabled = true;
                    cnpjTriangularInput.required = false;
                    cnpjTriangularInput.value = '';
                }
            }

            vendaTriangularSelect.addEventListener('change', toggleVendaTriangular);
            toggleVendaTriangular();
        }

        console.log('Sistema inicializado com sucesso!');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const vendaTriangular = document.getElementById('venda_triangular');
        const cnpj_triangular = document.getElementById('cnpj_triangular');
        const condicaoPagamento = document.getElementById('condicao_id');
        const outrosMeios = document.getElementById('outros_meios_pagamento');

        function toggleVendaTriangular() {
            if (vendaTriangular.value === '1') {
                cnpj_triangular.disabled = false;
                cnpj_triangular.required = true;
            } else {
                cnpj_triangular.disabled = true;
                cnpj_triangular.required = false;
                cnpj_triangular.value = '';
            }
        }

        function toggleOutrosMeios() {
            if (condicaoPagamento.value == 20) {
                outrosMeios.disabled = false;
                outrosMeios.required = true;
            } else {
                outrosMeios.disabled = true;
                outrosMeios.required = false;
                outrosMeios.value = '';
            }
        }

        if (vendaTriangular) {
            vendaTriangular.addEventListener('change', toggleVendaTriangular);
            toggleVendaTriangular();
        }

        if (condicaoPagamento) {
            condicaoPagamento.addEventListener('change', toggleOutrosMeios);
            toggleOutrosMeios();
        }
    });
</script>
