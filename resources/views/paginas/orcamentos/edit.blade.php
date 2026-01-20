<x-layouts.app :title="__('Editar Orçamento')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Editar Orçamento para Cliente {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
                </h2>
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

                <!-- Pesquisa de Produtos -->
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
                <!-- Campos iniciais -->
                <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-8"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />

                    <!-- Produtos no Orçamento -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Código</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Produto</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Part Number</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Fornecedor</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Cor</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Preço Unit.</th>
                                        <th scope="col"
                                            class="w-20 px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Qtd.</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Subtotal</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            c/ Desconto</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">
                                            Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="produtos-originais" class="divide-y">
                                    @foreach ($orcamento->itens as $item)
                                        <tr>
                                            <input type="hidden" name="produtos[{{ $loop->index }}][produto_id]"
                                                value="{{ $item->produto->id }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][valor_unitario]"
                                                class="valor-unitario-hidden" value="{{ $item->valor_unitario }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][part_number]"
                                                value="{{ $item->produto->part_number ?? '' }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][quantidade]"
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
                                            <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '' }}</td>
                                            <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome ?? '' }}
                                            </td>
                                            <td class="px-3 py-2 border">{{ $item->produto->cor ?? '' }}</td>
                                            <td class="px-3 py-2 border">R$
                                                {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                <input type="number" name="produtos[{{ $loop->index }}][quantidade]"
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
                                <tbody id="produtos-selecionados" class="divide-y">

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção de Vidros -->
                    <div class="space-y-4">
                        <br />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                </path>
                            </svg>
                            Vidros ou Esteiras

                            <button type="button" onclick="addVidro()"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                + Adicionar Vidro/Esteira
                            </button>
                        </h3>

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
                                                <label class="block text-sm font-medium text-gray-700">Preço m²</label>
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
                                    <input type="hidden" name="vidros_existentes[{{ $loop->index }}][valor_total]"
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

                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        <hr />
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
                                <option value="cif" @selected($orcamento->frete == 'cif')>CIF</option>
                                <option value="fob" @selected($orcamento->frete == 'fob')>FOB</option>
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
                    <!-- Valores e descontos -->
                    <div class="overflow-x-auto">
                        <div class="flex gap-4 min-w-max">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Condição de pagamento</label>
                                <x-select name="condicao_pagamento" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="">Selecione...</option>
                                    @foreach ($condicao as $c)
                                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto na vendedor %</label>
                                <input type="text" name="desconto"
                                    value="{{ $desconto_percentual ?? old('desconto') }}" min="0"
                                    max="100" placeholder="Digite a porcentagem de desconto (0 a 100)"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto específico R$</label>
                                <input type="text" name="desconto_especifico"
                                    value="{{ $desconto_especifico ?? old('desconto_especifico') }}"
                                    placeholder="Digite o valor do desconto específico"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Guia Recolhimento</label>
                                <input type="text" step="0.01" name="guia_recolhimento"
                                    value="{{ $orcamento->guia_recolhimento ?? 0 }}"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Valor Total dos Itens s/
                                    desconto (R$)</label>
                                <input type="text" id="valor_total" name="valor_total" readonly value="0,00"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Valor Final c/ desconto
                                    (R$)</label>
                                <input type="text" id="valor_final"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 font-semibold text-green-700"
                                    value="0.00" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Observações Gerais
                        </h3>
                        <x-textarea name="observacoes" placeholder="Digite as observações"
                            rows="4">{{ old('observacoes', $orcamento->observacoes) }}</x-textarea>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            Salvar Orçamento
                        </button>
                        <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                            Limpar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quantidade Produto -->
    <div id="modal-quantidade"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-80 shadow-lg relative">
            <button onclick="fecharModal()"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Quantidade do Produto</h3>
            <p id="produto-nome" class="mb-2 font-medium"></p>
            <input id="quantidade-produto" type="number" min="1" value="1"
                class="w-full border rounded px-3 py-2 mb-4" />
            <button onclick="confirmarQuantidade()"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
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
    console.log('Script carregando...');

    // ==================== VARIÁVEIS GLOBAIS ====================
    // CORREÇÃO: Remover as chaves do Blade aqui
    window.vidroIndex = 0; // Será inicializado corretamente no DOMContentLoaded
    window.produtos = [];
    window.produtoSelecionado = null;

    // ==================== MODAL ====================
    window.selecionarProdutoComQuantidade = function(id, nome, preco, fornecedor, cor, part_number) {
        console.log('Selecionando produto:', {
            id,
            nome,
            preco
        });

        window.produtoSelecionado = {
            id: id,
            nome: nome,
            preco: parseFloat(preco),
            fornecedor: fornecedor || '',
            cor: cor || '',
            part_number: part_number || '',
            quantidade: 1
        };

        document.getElementById('produto-nome').textContent = nome;
        document.getElementById('quantidade-produto').value = 1;
        document.getElementById('modal-quantidade').classList.remove('hidden');
    };

    window.fecharModal = function() {
        document.getElementById('modal-quantidade').classList.add('hidden');
        window.produtoSelecionado = null;
    };

    window.confirmarQuantidade = function() {
        if (!window.produtoSelecionado) {
            alert('Erro: Nenhum produto selecionado');
            return;
        }

        var quantidade = parseInt(document.getElementById('quantidade-produto').value) || 1;

        window.adicionarProduto(
            window.produtoSelecionado.id,
            window.produtoSelecionado.nome,
            window.produtoSelecionado.preco,
            window.produtoSelecionado.fornecedor,
            window.produtoSelecionado.cor,
            window.produtoSelecionado.part_number,
            quantidade
        );

        window.fecharModal();
    };

    // ==================== GERENCIAMENTO DE PRODUTOS NOVOS ====================
    window.adicionarProduto = function(id, nome, preco, fornecedor, cor, part_number, quantidade) {
        // Verificar se produto já existe nos produtos originais
        var produtosOriginais = document.getElementById('produtos-originais');
        if (produtosOriginais) {
            var rowsOriginais = produtosOriginais.querySelectorAll('tr');
            for (var i = 0; i < rowsOriginais.length; i++) {
                var row = rowsOriginais[i];
                if (row.style.display === 'none') continue;

                var produtoIdInput = row.querySelector('input[name*="[produto_id]"]');
                if (produtoIdInput && produtoIdInput.value == id) {
                    alert("Este produto já está no orçamento!");
                    return;
                }
            }
        }

        // Verificar se produto já existe nos produtos novos
        for (var i = 0; i < window.produtos.length; i++) {
            if (window.produtos[i].id == id) {
                alert("Produto já adicionado!");
                return;
            }
        }

        window.produtos.push({
            id: id,
            nome: nome,
            preco: parseFloat(preco) || 0,
            quantidade: parseInt(quantidade) || 1,
            fornecedor: fornecedor || '',
            cor: cor || '',
            part_number: part_number || ''
        });

        console.log('Produto adicionado. Total:', window.produtos.length);
        renderProdutosNovos();
    };

    window.alterarQuantidade = function(index, valor) {
        if (window.produtos[index]) {
            window.produtos[index].quantidade = parseInt(valor) || 1;
            renderProdutosNovos();
        }
    };

    window.removerProduto = function(index) {
        window.produtos.splice(index, 1);
        renderProdutosNovos();
    };

    function renderProdutosNovos() {
        var wrapper = document.getElementById('produtos-selecionados');
        if (!wrapper) return;

        wrapper.innerHTML = '';

        var desconto = obterDescontoAplicado();

        for (var i = 0; i < window.produtos.length; i++) {
            var p = window.produtos[i];
            var subtotal = p.preco * p.quantidade;
            var subtotalComDesconto = subtotal - (subtotal * (desconto / 100));
            var precoUnitarioComDesconto = subtotalComDesconto / p.quantidade;

            var row = document.createElement('tr');
            row.innerHTML =
                '<td class="px-3 py-2 border">' +
                '<input type="hidden" name="itens[' + i + '][id]" value="' + p.id + '">' +
                p.id + '</td>' +
                '<td class="px-3 py-2 border">' + escaparHTML(p.nome) + '</td>' +
                '<td class="px-3 py-2 border">' + escaparHTML(p.part_number) + '</td>' +
                '<td class="px-3 py-2 border">' + escaparHTML(p.fornecedor) + '</td>' +
                '<td class="px-3 py-2 border">' + escaparHTML(p.cor) + '</td>' +
                '<td class="px-3 py-2 border">R$ ' + formatarMoeda(p.preco) +
                '<input type="hidden" name="itens[' + i + '][preco_unitario]" value="' + p.preco + '"></td>' +
                '<td class="px-3 py-2 border">' +
                '<input type="number" name="itens[' + i + '][quantidade]" value="' + p.quantidade + '" ' +
                'min="1" onchange="alterarQuantidade(' + i + ', this.value)" ' +
                'class="w-12 border rounded px-2 py-1 text-center" style="max-width:4rem"/></td>' +
                '<td class="px-3 py-2 border">R$ ' + formatarMoeda(subtotal) +
                '<input type="hidden" name="itens[' + i + '][subtotal]" value="' + subtotal.toFixed(2) + '"></td>' +
                '<td class="px-3 py-2 border text-green-600">R$ ' + formatarMoeda(subtotalComDesconto) +
                '<input type="hidden" name="itens[' + i + '][subtotal_com_desconto]" value="' + subtotalComDesconto
                .toFixed(2) + '">' +
                '<input type="hidden" name="itens[' + i + '][preco_unitario_com_desconto]" value="' +
                precoUnitarioComDesconto.toFixed(2) + '"></td>' +
                '<td class="px-3 py-2 border text-center">' +
                '<button type="button" onclick="removerProduto(' + i +
                ')" class="text-red-600 hover:text-red-800">🗑</button></td>';

            wrapper.appendChild(row);
        }

        recalcularTotais();
    }

    // ==================== GERENCIAMENTO DE PRODUTOS ORIGINAIS ====================
    window.alterarQuantidadeOriginal = function(index, novaQuantidade) {
        console.log('Alterando quantidade original:', index, novaQuantidade);

        var tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        var row = tbody.querySelectorAll('tr')[index];
        if (!row || row.style.display === 'none') return;

        var quantidade = parseInt(novaQuantidade) || 1;

        var quantidadeInput = row.querySelector('input[name="produtos[' + index + '][quantidade]"]');
        if (quantidadeInput) {
            quantidadeInput.value = quantidade;
        }

        recalcularProdutoOriginal(row, index, quantidade);
        recalcularTotais();
    };

    window.removerProdutoOriginal = function(index) {
        console.log('Removendo produto original:', index);
        var tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        var row = tbody.querySelectorAll('tr')[index];
        if (row) {
            row.style.display = 'none';

            var removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'produtos[' + index + '][_remove]';
            removeInput.value = '1';
            row.appendChild(removeInput);
        }

        recalcularTotais();
    };

    function recalcularProdutoOriginal(row, index, quantidade) {
        var valorUnitarioInput = row.querySelector('.valor-unitario-hidden');
        if (!valorUnitarioInput) return;

        var valorUnitario = parseFloat(valorUnitarioInput.value) || 0;
        var desconto = obterDescontoAplicado();

        var subtotal = valorUnitario * quantidade;
        var subtotalComDesconto = subtotal - (subtotal * (desconto / 100));
        var precoUnitarioComDesconto = subtotalComDesconto / quantidade;

        var subtotalInput = row.querySelector('input[name="produtos[' + index + '][subtotal]"]');
        var subtotalComDescontoInput = row.querySelector('input[name="produtos[' + index +
            '][subtotal_com_desconto]"]');
        var precoComDescontoInput = row.querySelector('input[name="produtos[' + index +
            '][preco_unitario_com_desconto]"]');

        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
        if (subtotalComDescontoInput) subtotalComDescontoInput.value = subtotalComDesconto.toFixed(2);
        if (precoComDescontoInput) precoComDescontoInput.value = precoUnitarioComDesconto.toFixed(2);

        var cells = row.querySelectorAll('td');
        if (cells[7]) cells[7].innerHTML = 'R$ ' + formatarMoeda(subtotal);
        if (cells[8]) cells[8].innerHTML = 'R$ ' + formatarMoeda(subtotalComDesconto);
    }

    function recalcularTodosProdutosOriginais() {
        var tbody = document.getElementById('produtos-originais');
        if (!tbody) return;

        var rows = tbody.querySelectorAll('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.style.display === 'none') continue;

            var quantidadeInput = row.querySelector('input[name="produtos[' + i + '][quantidade]"]');
            if (!quantidadeInput) continue;

            var quantidade = parseInt(quantidadeInput.value) || 1;
            recalcularProdutoOriginal(row, i, quantidade);
        }
    }

    // ==================== GERENCIAMENTO DE VIDROS ====================
    window.addVidro = function() {
        var wrapper = document.getElementById('vidros-wrapper');
        if (!wrapper) return;

        var vidroDiv = document.createElement('div');
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
        var container = button.closest('div.space-y-2');
        var vidroId = container.getAttribute('data-vidro-id');

        if (vidroId) {
            var removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'vidros_removidos[]';
            removeInput.value = vidroId;
            container.appendChild(removeInput);
        }

        container.style.display = 'none';
        recalcularTotais();
    };

    window.calcularVidro = function(element) {
        var container = element.closest('div.space-y-2');
        if (!container) return;

        var altura = parseFloat(container.querySelector('[name*="[altura]"]').value) || 0;
        var largura = parseFloat(container.querySelector('[name*="[largura]"]').value) || 0;
        var quantidade = parseFloat(container.querySelector('[name*="[quantidade]"]').value) || 1;
        var precoM2 = parseFloat(container.querySelector('[name*="[preco_m2]"]').value) || 0;

        var area = (altura / 1000) * (largura / 1000);
        var valor = area * precoM2 * quantidade;
        var desconto = obterDescontoAplicado();
        var valorComDesconto = valor - (valor * (desconto / 100));

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
        var totalVidros = 0;
        var totalVidrosComDesconto = 0;
        var wrapper = document.getElementById('vidros-wrapper');

        if (wrapper) {
            var containers = wrapper.querySelectorAll('.space-y-2');
            for (var i = 0; i < containers.length; i++) {
                if (containers[i].style.display === 'none') continue;

                var valorEl = containers[i].querySelector('.valor-hidden');
                var valorDescEl = containers[i].querySelector('.valor-desconto-hidden');
                if (valorEl && valorDescEl) {
                    totalVidros += parseFloat(valorEl.value) || 0;
                    totalVidrosComDesconto += parseFloat(valorDescEl.value) || 0;
                }
            }
        }

        return {
            totalVidros: totalVidros,
            totalVidrosComDesconto: totalVidrosComDesconto
        };
    }

    function recalcularTodosVidros() {
        var wrapper = document.getElementById('vidros-wrapper');
        if (!wrapper) return;

        var containers = wrapper.querySelectorAll('.space-y-2');
        for (var i = 0; i < containers.length; i++) {
            if (containers[i].style.display === 'none') continue;

            var firstInput = containers[i].querySelector('input[type="number"]');
            if (firstInput) {
                calcularVidro(firstInput);
            }
        }
    }

    // ==================== CÁLCULOS E TOTAIS ====================
    function obterDescontoAplicado() {
        var descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]')?.value) || 0;
        var descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]')?.value) || 0;
        descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);
        return Math.max(descontoCliente, descontoOrcamento);
    }

    function calcularTotalProdutosOriginais() {
        var total = 0;
        var totalComDesconto = 0;
        var tbody = document.getElementById('produtos-originais');

        if (tbody) {
            var rows = tbody.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].style.display === 'none') continue;

                var subtotalInput = rows[i].querySelector('input[name*="[subtotal]"]:not([name*="com_desconto"])');
                var subtotalComDescontoInput = rows[i].querySelector('input[name*="[subtotal_com_desconto]"]');

                if (subtotalInput && subtotalComDescontoInput) {
                    total += parseFloat(subtotalInput.value) || 0;
                    totalComDesconto += parseFloat(subtotalComDescontoInput.value) || 0;
                }
            }
        }

        return {
            total: total,
            totalComDesconto: totalComDesconto
        };
    }

    function calcularTotalProdutosNovos() {
        var total = 0;
        var totalComDesconto = 0;
        var desconto = obterDescontoAplicado();

        for (var i = 0; i < window.produtos.length; i++) {
            var subtotal = window.produtos[i].preco * window.produtos[i].quantidade;
            var subtotalComDesconto = subtotal - (subtotal * (desconto / 100));
            total += subtotal;
            totalComDesconto += subtotalComDesconto;
        }

        return {
            total: total,
            totalComDesconto: totalComDesconto
        };
    }

    function recalcularTotais() {
        recalcularTodosProdutosOriginais();

        var totaisOriginais = calcularTotalProdutosOriginais();
        var totaisNovos = calcularTotalProdutosNovos();
        var totaisVidros = calcularTotalVidros();

        var totalGeral = totaisOriginais.total + totaisNovos.total + totaisVidros.totalVidros;
        var totalGeralComDesconto = totaisOriginais.totalComDesconto + totaisNovos.totalComDesconto + totaisVidros
            .totalVidrosComDesconto;

        var valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) {
            valorTotalInput.value = totalGeral.toFixed(2);
        }

        var guia = parseFloat(document.querySelector('[name="guia_recolhimento"]')?.value) || 0;
        var descontoEspecifico = parseFloat(document.querySelector('[name="desconto_especifico"]')?.value) || 0;

        var valorFinal = totalGeralComDesconto - descontoEspecifico + guia;
        if (valorFinal < 0) valorFinal = 0;

        var maxDesconto = totalGeralComDesconto + guia;
        if (descontoEspecifico > maxDesconto) {
            descontoEspecifico = maxDesconto;
            var descontoEspecificoInput = document.querySelector('[name="desconto_especifico"]');
            if (descontoEspecificoInput) {
                descontoEspecificoInput.value = maxDesconto.toFixed(2);
            }
        }

        var valorFinalInput = document.getElementById('valor_final');
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

    // ==================== INICIALIZAÇÃO ====================
    function inicializar() {
        console.log('Inicializando sistema de orçamento...');

        // Inicializar vidroIndex com base na quantidade de vidros existentes
        var vidrosWrapper = document.getElementById('vidros-wrapper');
        if (vidrosWrapper) {
            var vidrosExistentes = vidrosWrapper.querySelectorAll('.space-y-2');
            window.vidroIndex = vidrosExistentes.length;
            console.log('Vidros existentes:', window.vidroIndex);
        }

        // Recalcular vidros existentes na inicialização
        recalcularTodosVidros();

        // Recalcular tudo na inicialização
        recalcularTotais();

        var camposDesconto = [
            '[name="desconto"]',
            '[name="desconto_especifico"]',
            '[name="guia_recolhimento"]'
        ];

        camposDesconto.forEach(function(selector) {
            var campo = document.querySelector(selector);
            if (campo) {
                campo.addEventListener('input', function() {
                    recalcularTodosVidros();
                    recalcularTotais();
                });
            }
        });

        console.log('Sistema inicializado com sucesso!');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }
</script>
