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
                    Criar Orçamento para Cliente {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
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
                                <tbody id="produtos-selecionados" class="divide-y">
                                    @foreach ($orcamento->itens as $item)
                                        <tr>
                                            <input type="hidden" name="produtos[{{ $loop->index }}][produto_id]"
                                                value="{{ $item->produto->id }}">
                                            <input type="hidden" name="produtos[{{ $loop->index }}][valor_unitario]"
                                                class="valor-unitario-hidden" value="{{ $item->valor_unitario }}">

                                            <td class="px-4 py-3 text-sm">{{ $item->produto->codigo }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $item->produto->nome }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $item->produto->part_number }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ $item->produto->fornecedor->nome ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $item->produto->cor }}</td>
                                            <td class="px-4 py-3 text-sm">R$
                                                {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                            <td class="w-20 px-4 py-3">
                                                <input type="number" name="produtos[{{ $loop->index }}][quantidade]"
                                                    value="{{ $item->quantidade }}" min="1"
                                                    class="w-full text-center border-gray-300 rounded-md"
                                                    onchange="calcularSubtotal(this)">
                                            </td>
                                            <td class="px-4 py-3 text-sm subtotal">R$
                                                {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <input type="text" name="produtos[{{ $loop->index }}][desconto]"
                                                    value="{{ number_format($item->desconto ?? 0, 2, ',', '.') }}"
                                                    class="w-full text-center border-gray-300 rounded-md"
                                                    onchange="calcularSubtotal(this)">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" onclick="removerProdutoExistente(this)"
                                                    class="text-red-500 hover:text-red-700">
                                                    Remover
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
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
                        <div x-transition id="vidros-wrapper" class="space-y-4">
                            @foreach ($orcamento->vidros as $vidro)
                                <div class="vidro-item grid grid-cols-1 md:grid-cols-6 gap-4 border p-4 rounded-lg">
                                    <input type="hidden" name="vidros[{{ $loop->index }}][id]"
                                        value="{{ $vidro->id }}">

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium">Descrição</label>
                                        <input type="text" name="vidros[{{ $loop->index }}][descricao]"
                                            value="{{ $vidro->descricao }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="Descrição do Vidro/Esteira">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Qtd.</label>
                                        <input type="number" name="vidros[{{ $loop->index }}][quantidade]"
                                            value="{{ $vidro->quantidade }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="Qtd" onchange="calcularVidro(this)">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Altura (mm)</label>
                                        <input type="number" name="vidros[{{ $loop->index }}][altura]"
                                            value="{{ $vidro->altura }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="Altura" oninput="calcularVidro(this)">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Largura (mm)</label>
                                        <input type="number" name="vidros[{{ $loop->index }}][largura]"
                                            value="{{ $vidro->largura }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="Largura" oninput="calcularVidro(this)">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="this.closest('.vidro-item').remove(); atualizarValorFinal();"
                                            class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">Remover</button>
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
                            <x-input type="text" name="prazo_entrega" placeholder="Ex: 15 dias úteis"
                                label="Prazo de Entrega" :value="$orcamento->prazo_entrega" />
                            <x-select name="vendedor_id" label="Atendido por" required>
                                <option value="">Selecione...</option>
                                @foreach ($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" @selected($orcamento->vendedor_id == $vendedor->id)>
                                        {{ $vendedor->name }}</option>
                                @endforeach
                            </x-select>
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
                            Opções de Transporte
                        </h3>

                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach ($opcoesTransporte as $opcao)
                                <label
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 hover:bg-blue-50 cursor-pointer transition">
                                    <input type="checkbox" name="tipos_transporte[]" value="{{ $opcao->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @if ($orcamento->transportes->contains($opcao->id)) checked @endif />
                                    <span class="text-sm text-gray-700">{{ $opcao->nome }}</span>
                                </label>
                            @endforeach
                        </div>

                    </div>

                    <hr />
                    <!-- Valores e descontos -->
                    <div class="overflow-x-auto">
                        <div class="flex gap-4 min-w-max">
                            @php
                                $descontoPercentual = $orcamento->descontos()->where('tipo', 'percentual')->first();
                            @endphp
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto na vendedor %</label>
                                <input type="text" name="desconto_percentual"
                                    value="{{ $descontoPercentual->porcentagem ?? 0 }}" min="0"
                                    max="100" placeholder="Digite a porcentagem de desconto (0 a 100)"
                                    oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                            </div>

                            @php
                                $descontoFixo = $orcamento->descontos()->where('tipo', 'fixo')->first();
                            @endphp
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Desconto específico R$</label>
                                <input type="text" name="desconto_valor"
                                    value="{{ number_format($descontoFixo->valor ?? 0, 2, ',', '.') }}"
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
                                <input type="text" id="valor_total" name="valor_total_itens" readonly
                                    value="{{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100" />
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Valor Final c/ desconto
                                    (R$)</label>
                                <input type="text" id="valor_final"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 font-semibold text-green-700"
                                    value="{{ number_format($orcamento->valor_final, 2, ',', '.') }}" readonly />
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
                        <x-textarea name="observacoes" placeholder="Digite as observações" label="Observações"
                            rows="4"> {{ old('observacoes', $orcamento->observacoes) }} </x-textarea>
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

    <script src="{{ asset('js/valida.js') }}"></script>
    <script>
        // =============== UTIL ===============
        function parseBRL(inputEl) {
            if (!inputEl) return 0;
            const v = parseFloat(raw);
            return Number.isFinite(v) ? v : 0;
        }

        // =============== VIDROS ===============
        let vidroIndex = 1;

        function addVidro() {
            const wrapper = document.getElementById('vidros-wrapper');
            const vidroDiv = document.createElement('div');
            vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
            vidroDiv.innerHTML = `
                <button type="button" onclick="removeVidro(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                    Remover
                </button><br/>
                <div class="overflow-x-auto">
                    <div class="flex gap-4 min-w-max">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Descrição do Item</label>
                            <input type="text" name="vidros[${vidroIndex}][descricao]" placeholder="Ex: Vidro incolor 8mm" 
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                            <input type="number" name="vidros[${vidroIndex}][quantidade]" value="1" placeholder="Digite a quantidade" 
                                oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Preço do m²</label>
                            <input type="number" step="0.01" name="vidros[${vidroIndex}][preco_m2]" placeholder="Digite o preço" 
                                oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Altura (mm)</label>
                            <input type="number" name="vidros[${vidroIndex}][altura]" placeholder="Digite a altura em mm" 
                                oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Largura (mm)</label>
                            <input type="number" name="vidros[${vidroIndex}][largura]" placeholder="Digite a largura em mm" 
                                oninput="calcularVidro(this)" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                        </div>     
                    </div>                    
                </div>                

                <input type="hidden" name="vidros[${vidroIndex}][area]" class="area-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_total]" class="valor-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_com_desconto]" class="valor-desconto-hidden" />

                <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                    <strong>Área (m²):</strong> <span class="area">0.00</span> |
                    <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                    <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
                </div>
            `;
            wrapper.appendChild(vidroDiv);
            vidroIndex++;
        }

        function removeVidro(button) {
            button.closest('div.space-y-2').remove();
            atualizarValorFinal();
        }

        function calcularVidro(element) {
            if (!container) return;


            let area = (altura / 1000) * (largura / 1000);
            let valor = area * precoM2 * quantidade;

            if (descontoOrcamento < 0) descontoOrcamento = 0;
            if (descontoOrcamento > 100) descontoOrcamento = 100;
            const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

            let valorComDesconto = valor - (valor * (descontoAplicado / 100));

            container.querySelector('.area') && (container.querySelector('.area').textContent = area.toFixed(2));
            container.querySelector('.valor') && (container.querySelector('.valor').textContent = valor.toFixed(2));
            container.querySelector('.valor-desconto') && (container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2));

            container.querySelector('.area-hidden') && (container.querySelector('.area-hidden').value = area.toFixed(2));
            container.querySelector('.valor-hidden') && (container.querySelector('.valor-hidden').value = valor.toFixed(2));
            container.querySelector('.valor-desconto-hidden') && (container.querySelector('.valor-desconto-hidden').value = valorComDesconto.toFixed(2));

            setTimeout(() => atualizarValorFinal(), 10);
        }

        function calcularTotalVidros() {
            let totalVidros = 0;
            let totalVidrosComDesconto = 0;
            document.querySelectorAll('#vidros-wrapper .space-y-2, #vidros-wrapper .vidro-item').forEach(container => {
                const valorElement = container.querySelector('.valor-hidden');
                const valorDescontoElement = container.querySelector('.valor-desconto-hidden');

                if (valorElement && valorDescontoElement) {
                }
            });

            return { totalVidros, totalVidrosComDesconto };
        }

        // =============== PRODUTOS NOVOS (JS) ===============
        let produtos = [];

        function adicionarProduto(id, nome, preco, fornecedor = '', cor = '', quantidade = 1, part_number = '') {
            if (produtos.find(p => p.id === id)) {
                alert("Produto já adicionado!");
                return;
            }

            const produto = {
                id,
                nome,
                preco: parseFloat(preco),
                fornecedor,
                cor,
            };
            produtos.push(produto);
            renderProdutos();
        }

        function alterarQuantidade(index, valor) {
            renderProdutos();
        }

        function removerProdutoNovo(index) {
            produtos.splice(index, 1);
            renderProdutos();
        }

        function renderProdutos() {
            const wrapper = document.getElementById('produtos-selecionados');

            // Remove apenas linhas adicionadas via JS
            wrapper.querySelectorAll('tr.js-novo').forEach(r => r.remove());

            descontoPercentual = Math.min(Math.max(descontoPercentual, 0), 100);

            const descontoAplicado = Math.max(descontoCliente, descontoPercentual);

            produtos.forEach((p, i) => {
                const subtotal = p.preco * p.quantidade;
                const subtotalComDesconto = subtotal - (subtotal * (descontoAplicado / 100));

                const row = document.createElement('tr');
                row.className = 'js-novo';

                // IMPORTANTE: use nome distinto para novos (ex.: produtos_novos)
                row.innerHTML = `
                    <td class="px-4 py-3 text-sm">
                        ${p.id}
                        <input type="hidden" name="produtos_novos[${i}][produto_id]" value="${p.id}">
                        <input type="hidden" name="produtos_novos[${i}][valor_unitario]" value="${p.preco}">
                    </td>
                    <td class="px-4 py-3 text-sm">${p.nome}</td>
                    <td class="px-4 py-3 text-sm">R$ ${p.preco.toFixed(2)}</td>
                    <td class="w-20 px-4 py-3">
                        <input type="number" name="produtos_novos[${i}][quantidade]" 
                            value="${p.quantidade}" min="1"
                            onchange="alterarQuantidade(${i}, this.value)"
                            class="w-full text-center border-gray-300 rounded-md"/>
                    </td>
                    <td class="px-4 py-3 text-sm">R$ ${subtotal.toFixed(2)}</td>
                    <td class="px-4 py-3 text-sm text-green-600">R$ ${subtotalComDesconto.toFixed(2)}</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="removerProdutoNovo(${i})"
                                class="text-red-600 hover:text-red-800">Remover</button>
                    </td>
                `;
                wrapper.appendChild(row);
            });

            atualizarValorFinal();
        }

        // =============== PRODUTOS EXISTENTES (Blade) ===============
        function removerProdutoExistente(button) {
            // Opcional: enviar para exclusão no backend
            // Exemplo: pegar produto_id da linha e adicionar input hidden "remover_existentes[]"
            const produtoId = button.closest('tr')?.querySelector('input[name*="[produto_id]"]')?.value;
            if (produtoId) {
                const form = button.closest('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remover_existentes[]';
                input.value = produtoId;
                form.appendChild(input);
            }
            button.closest('tr')?.remove();
            atualizarValorFinal();
        }

        function calcularSubtotal(input) {
            // Basta recalcular o total geral
            atualizarValorFinal();
        }

        // =============== MODAL DE QUANTIDADE ===============
        let produtoSelecionado = null;

        function selecionarProdutoComQuantidade(id, nome, preco, fornecedor = '', cor = '', part_number = '') {
            produtoSelecionado = {
                id,
                nome,
                preco: parseFloat(preco),
                fornecedor,
                cor,
                quantidade: 1
            };
            document.getElementById('produto-nome').textContent = nome;
            document.getElementById('quantidade-produto').value = 1;
            document.getElementById('modal-quantidade').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modal-quantidade').classList.add('hidden');
            produtoSelecionado = null;
        }

        function confirmarQuantidade() {
            if (!produtoSelecionado) return;
            produtoSelecionado.quantidade = quantidade;

            adicionarProduto(
                produtoSelecionado.id,
                produtoSelecionado.nome,
                produtoSelecionado.preco,
                produtoSelecionado.fornecedor,
                produtoSelecionado.cor,
                produtoSelecionado.quantidade,
                produtoSelecionado.part_number
            );

            fecharModal();
        }

        // =============== CÁLCULO FINAL ===============
        function atualizarValorFinal() {
            // Descontos
            descontoPercentual = Math.min(Math.max(descontoPercentual, 0), 100);
            const descontoAplicado = Math.max(descontoCliente, descontoPercentual);

            // Somar produtos existentes (linhas do Blade)
            let totalExistentes = 0;
            let totalExistentesDesc = 0;

            document.querySelectorAll('#produtos-selecionados tr:not(.js-novo)').forEach(tr => {
                const valorUnitHidden = tr.querySelector('.valor-unitario-hidden');
                const qtdInput = tr.querySelector('input[name*="[quantidade]"]');
                const sub = unit * qtd;
                totalExistentes += sub;
                totalExistentesDesc += sub * (1 - descontoAplicado / 100);
            });

            // Somar novos
            let totalNovos = 0;
            let totalNovosDesc = 0;
            produtos.forEach(p => {
                const sub = p.preco * p.quantidade;
                totalNovos += sub;
                totalNovosDesc += sub * (1 - descontoAplicado / 100);
            });

            // Somar vidros
            const { totalVidros, totalVidrosComDesconto } = calcularTotalVidros();

            const total = totalExistentes + totalNovos + totalVidros;
            const totalDesc = totalExistentesDesc + totalNovosDesc + totalVidrosComDesconto;

            // Guia e desconto fixo
            const descFixo = parseBRL(document.querySelector('[name="desconto_valor"]'));

            const valorSemDescontoFinal = total + guia;
            let valorFinalComDesconto = Math.max(0, totalDesc - descFixo + guia);

            // Atualiza campos
            const vt = document.getElementById('valor_total');
            const vf = document.getElementById('valor_final');
            if (vt) vt.value = valorSemDescontoFinal.toFixed(2);
            if (vf) vf.value = valorFinalComDesconto.toFixed(2);
        }

        // =============== EVENTOS INICIAIS ===============
        document.addEventListener("DOMContentLoaded", () => {
            // Listeners de desconto e guia
            const pct = document.querySelector('[name="desconto_percentual"]');
            pct?.addEventListener("input", () => {
                pct.value = Math.min(Math.max(val, 0), 100);
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });
                renderProdutos();
            });

            const valFixo = document.querySelector('[name="desconto_valor"]');
            valFixo?.addEventListener("input", () => atualizarValorFinal());

            const guia = document.querySelector('[name="guia_recolhimento"]');
            guia?.addEventListener("input", () => atualizarValorFinal());

            // Caso desconto_aprovado seja editável
            document.querySelector('[name="desconto_aprovado"]')?.addEventListener("input", function() {
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });
                renderProdutos();
            });

            // Recalcula totais na carga inicial
            atualizarValorFinal();
        });
    </script>

</x-layouts.app>