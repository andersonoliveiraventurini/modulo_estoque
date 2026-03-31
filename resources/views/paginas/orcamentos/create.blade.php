<x-layouts.app :title="__('Criar Orçamento')">

    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-o-document-text class="w-6 h-6 text-accent" />
                    <flux:heading size="xl">
                        {{ __('Novo Orçamento para Cliente') }} {{ $cliente->id }} - {{ $cliente->nome ?? $cliente->nome_fantasia }}
                    </flux:heading>
                </div>
                @if ($cliente->desconto != null)
                    Autorizado desconto de até {{ $cliente->desconto }}%
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

                @if($cliente->bloqueado)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 rounded-md">
                        <div class="flex items-center">
                            <x-heroicon-s-lock-closed class="h-5 w-5 mr-2" />
                            <p class="font-bold text-lg">Atenção: Cliente Bloqueado</p>
                        </div>
                        <p class="mt-2 text-sm">Este cliente está bloqueado. Descontos exigirão aprovação (independente do limite do vendedor) e opções de pagamento faturadas não devem estar disponíveis.</p>
                        @if($cliente->ultimoBloqueio)
                            <p class="mt-1 text-sm font-medium">Motivo: {{ $cliente->ultimoBloqueio->motivo }}</p>
                        @endif
                    </div>
                @endif

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
                <form action="{{ route('orcamentos.store') }}" method="POST" class="space-y-8"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}" />
                    <!-- Token CSRF seria aqui -->

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
                                <tbody id="produtos-selecionados" class="divide-y"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção de Vidros Corrigida -->
                    <div class="space-y-4">
                        <br />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <!-- Wrapper dos vidros (só aparece quando aberto) -->
                        <div x-transition id="vidros-wrapper" class="space-y-4">
                        </div>
                    </div>

                    <!-- Endereço de entrega -->
                    <div class="space-y-4">

                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Nome da Obra e Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input type="text" name="nome_obra" placeholder="Digite o nome da obra"
                                label="Nome da Obra" required value="{{ old('nome_obra') }}" />
                            <x-select name="complemento" label="Complemento de outro orçamento?" required>
                                <option value="Não" {{ old('complemento') == 'Não' ? 'selected' : '' }}>Não</option>
                                <option value="Sim" {{ old('complemento') == 'Sim' ? 'selected' : '' }}>Sim</option>
                            </x-select>
                            <x-input type="text" name="prazo_entrega" placeholder="Ex: 15 dias úteis"
                                label="Prazo de Entrega" value="{{ old('prazo_entrega') }}" />
                            <x-select name="frete" label="Tipo de Frete" required>
                                <option value="">Selecione...</option>
                                <option value="cif" {{ old('frete') == 'cif' ? 'selected' : '' }}>CIF - entrega por conta do fornecedor
                                </option>
                                <option value="fob" {{ old('frete') == 'fob' ? 'selected' : '' }}>FOB - entrega por conta do cliente</option>
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
                            <x-input id="entrega_cep" name="entrega_cep"
                                label="CEP - Para adicionar um novo endereço" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" value="{{ old('entrega_cep') }}" />


                        </div>

                        <!-- Wrapper que será ocultado até o CEP ser válido -->
                        <div id="endereco-entrega-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" value="{{ old('entrega_cidade') }}" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" value="{{ old('entrega_estado') }}" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" value="{{ old('entrega_bairro') }}" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly"
                                    value="{{ old('entrega_logradouro') }}" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    value="{{ old('entrega_numero') }}" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc."
                                    value="{{ old('entrega_compl') }}" />
                            </div>
                        </div>
                    </div>
                    <!-- Opções de Transporte -->
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
                                        required {{ old('tipos_transporte') == $opcao->id ? 'checked' : '' }}
                                        class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500" /> <span
                                        class="text-sm text-gray-700">{{ $opcao->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- Valores e descontos -->
                    <hr />
                    <!-- Seção de Pagamento e Impostos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <flux:select name="condicao_id" id="condicao_id" label="Condição de pagamento" required>
                            <option value="">Selecione...</option>
                            @foreach ($condicao as $c)
                                <option value="{{ $c->id }}" {{ old('condicao_id') == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input name="outros_meios_pagamento" id="outros_meios_pagamento" label="Outros meios pagamento" 
                            disabled placeholder="Ex: Boleto 28/56/84..." value="{{ old('outros_meios_pagamento') }}" />

                        <flux:select name="tipo_documento" label="Nota fiscal" required>
                            <option value="">Selecione...</option>
                            <option value="Nota fiscal" {{ old('tipo_documento') == 'Nota fiscal' ? 'selected' : '' }}>Nota fiscal</option>
                            <option value="Cupom Fiscal" {{ old('tipo_documento') == 'Cupom Fiscal' ? 'selected' : '' }}>Cupom Fiscal</option>
                        </flux:select>

                        <flux:select name="homologacao" label="Homologação" required>
                            <option value="0" {{ old('homologacao', '0') == '0' ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('homologacao') == '1' ? 'selected' : '' }}>Sim</option>
                        </flux:select>

                        <flux:select name="venda_triangular" id="venda_triangular" label="Venda triangular?" required>
                            <option value="0" {{ old('venda_triangular', '0') == '0' ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('venda_triangular') == '1' ? 'selected' : '' }}>Sim</option>
                        </flux:select>

                        <flux:input name="cnpj_triangular" id="cnpj_triangular" label="CNPJ venda triangular" 
                            disabled placeholder="00.000.000/0000-00" 
                            onkeypress="mascara(this, '##.###.###/####-##')" maxlength="18" value="{{ old('cnpj_triangular') }}" />
                    </div>

                    <!-- Seção de Valores e Descontos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <flux:input name="desconto" label="Desconto na venda %" value="{{ old('desconto', 0) }}" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="desconto_especifico" label="Desconto específico R$" value="{{ old('desconto_especifico', '0.00') }}" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input name="guia_recolhimento" label="Guia Recolhimento R$" value="{{ old('guia_recolhimento', 0) }}" 
                            oninput="this.value = this.value.replace(/[^0-9,\.]/g,'');" />

                        <flux:input id="valor_total" name="valor_total" label="Total s/ desconto (R$)" readonly value="{{ old('valor_total', '0,00') }}" />

                        <flux:input id="valor_final" label="Valor Final c/ desconto (R$)" readonly value="0.00" 
                            class="font-semibold text-green-700 dark:text-green-400" />
                    </div>

                    <div class="space-y-4">
                        <hr />
                        <div class="flex items-center gap-2">
                             <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5 text-accent" />
                             <flux:heading size="lg">Observações Gerais</flux:heading>
                        </div>
                        <flux:textarea name="observacoes" placeholder="Digite as observações" rows="4">{{ old('observacoes') }}</flux:textarea>
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
    </div>

    <!-- Modal Quantidade Produto -->
    <div id="modal-quantidade"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-80 shadow-lg relative">
            <button onclick="fecharModal()"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Quantidade do Produto</h3>
            <p id="produto-nome" class="mb-2 font-medium"></p>
            <!-- Aviso de estoque -->
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
            <button onclick="confirmarQuantidade()" id="btn-confirmar-quantidade"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                Adicionar
            </button>
        </div>
    </div>

    @if ($ativo !== true)
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

            vendaTriangular.addEventListener('change', toggleVendaTriangular);
            condicaoPagamento.addEventListener('change', toggleOutrosMeios);

            // Executa ao carregar a página
            toggleVendaTriangular();
            toggleOutrosMeios();
        });
    </script>


    <script src="{{ asset('js/valida.js') }}"></script>
    <script>
        let vidroIndex = 1;

        function addVidro() {
            const wrapper = document.getElementById('vidros-wrapper');
            const vidroDiv = document.createElement('div');
            vidroDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";
            vidroDiv.innerHTML = `
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
                            <input type="number" step="1" name="vidros[${vidroIndex}][preco_m2]" placeholder="Digite o preço" 
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

                <!-- Campos hidden para valores calculados -->
                <input type="hidden" name="vidros[${vidroIndex}][area]" class="area-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_total]" class="valor-hidden" />
                <input type="hidden" name="vidros[${vidroIndex}][valor_com_desconto]" class="valor-desconto-hidden" />

                <div class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                    <strong>Área (m²):</strong> <span class="area">0.00</span> |
                    <strong>Valor Total:</strong> R$ <span class="valor">0.00</span> |
                    <strong>c/ desconto:</strong> R$ <span class="valor-desconto">0.00</span>
                
            <button type="button" onclick="removeVidro(this)" class="absolute right-2 text-red-600 hover:text-red-800"'+
            'style="padding-top: -1rem;">Remover</button> </div>
            `;
            wrapper.appendChild(vidroDiv);
            vidroIndex++;
        }

        function removeVidro(button) {
            button.closest('div.space-y-2').remove();
            atualizarValorFinal();
        }

        function calcularVidro(element) {
            const container = element.closest('div.space-y-2');
            let altura = parseFloat(container.querySelector('[name*="[altura]"]').value) || 0;
            let largura = parseFloat(container.querySelector('[name*="[largura]"]').value) || 0;
            let quantidade = parseInt(container.querySelector('[name*="[quantidade]"]').value) || 0;
            let precoM2 = parseFloat(container.querySelector('[name*="[preco_m2]"]').value) || 0;

            let area = (altura / 1000) * (largura / 1000);
            let valor = area * precoM2 * quantidade;

            // Aplicar desconto
            const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
            let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;
            if (descontoOrcamento < 0) descontoOrcamento = 0;
            if (descontoOrcamento > 100) descontoOrcamento = 100;
            const descontoAplicado = Math.max(descontoCliente, descontoOrcamento);

            let valorComDesconto = valor - (valor * (descontoAplicado / 100));

            // Atualizar displays
            container.querySelector('.area').textContent = area.toFixed(2);
            container.querySelector('.valor').textContent = valor.toFixed(2);
            container.querySelector('.valor-desconto').textContent = valorComDesconto.toFixed(2);

            // Atualizar campos hidden para envio
            container.querySelector('.area-hidden').value = area.toFixed(2);
            container.querySelector('.valor-hidden').value = valor.toFixed(2);
            container.querySelector('.valor-desconto-hidden').value = valorComDesconto.toFixed(2);

            // Forçar atualização do valor final
            setTimeout(() => {
                atualizarValorFinal();
            }, 10);
        }

        function calcularTotalVidros() {
            let totalVidros = 0;
            let totalVidrosComDesconto = 0;

            // Percorrer todos os vidros
            document.querySelectorAll('#vidros-wrapper .space-y-2').forEach(container => {
                const valorElement = container.querySelector('.valor-hidden');
                const valorDescontoElement = container.querySelector('.valor-desconto-hidden');

                if (valorElement && valorDescontoElement) {
                    totalVidros += parseFloat(valorElement.value) || 0;
                    totalVidrosComDesconto += parseFloat(valorDescontoElement.value) || 0;
                }
            });

            return {
                totalVidros,
                totalVidrosComDesconto
            };
        }
    </script>

    <script>
        let itemIndex = 1;

        const cores = @json($cores);
        const fornecedores = @json($fornecedores);

        function addItem() {
            const wrapper = document.getElementById('itens-wrapper');
            const itemDiv = document.createElement('div');
            itemDiv.className = "space-y-2 relative border border-neutral-200 dark:border-neutral-700 rounded-lg p-4";

            // Monta opções de cores
            let coresOptions = `<option value="">Selecione...</option>`;
            cores.forEach(cor => {
                coresOptions += `<option value="${cor.nome}">${cor.nome}</option>`;
            });

            // Monta opções de fornecedores
            let fornecedoresOptions = `<option value="">Selecione...</option>`;
            fornecedores.forEach(f => {
                fornecedoresOptions += `<option value="${f.id}">${f.nome_fantasia}</option>`;
            });

            itemDiv.innerHTML = ` <button type="button" onclick="removeItem(this)" class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                 Remover
            </button>
            <br/>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Descrição do item</label>
                    <input type="text" name="itens[${itemIndex}][nome]" placeholder="Digite a descrição" required
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                    <input type="number" name="itens[${itemIndex}][quantidade]" placeholder="Digite a quantidade"
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
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>
            </div>

           
        `;

            wrapper.appendChild(itemDiv);
            itemIndex++;
        }


        function removeItem(button) {
            button.closest('div.space-y-2').remove();
        }

        let produtos = [];

        // Altere a assinatura para receber estoque
        function adicionarProduto(id, nome, preco, fornecedor = '', cor = '', quantidade = 1, partNumber = '',
            liberarDesconto = 1, estoqueDisponivel = null) { // ✅ novo parâmetro

            if (produtos.find(p => p.id === id)) {
                alert("Produto já adicionado!");
                return;
            }

            const produto = {
                id,
                nome,
                preco: parseFloat(preco),
                precoOriginal: parseFloat(preco),
                quantidade: parseInt(quantidade) || 1,
                fornecedor,
                cor,
                partNumber,
                liberarDesconto: parseInt(liberarDesconto),
                descontoProduto: 0,
                estoqueDisponivel: estoqueDisponivel !== null ? parseInt(estoqueDisponivel) : null // ✅ novo
            };
            produtos.push(produto);
            renderProdutos();
        }

        function alterarPrecoProduto(index, novoPreco) {
            const produto = produtos[index];

            if (produto.liberarDesconto === 0) {
                alert("Este produto não permite alteração de preço!");
                return;
            }

            novoPreco = parseFloat(novoPreco) || produto.precoOriginal;

            // Não permite preço negativo
            if (novoPreco < 0) {
                novoPreco = 0;
            }

            // Calcula o desconto em R$ (diferença entre preço original e novo)
            const descontoEmReais = produto.precoOriginal - novoPreco;

            produtos[index].preco = novoPreco;
            produtos[index].descontoProduto = descontoEmReais;

            renderProdutos();
        }

        function alterarQuantidade(index, valor) {
            const novaQuantidade = parseInt(valor) || 1;
            const produto = produtos[index];

            // ✅ Verifica estoque ao alterar quantidade na tabela
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
                    // Reverte o input para o valor anterior
                    renderProdutos();
                    return;
                }
            }

            produtos[index].quantidade = novaQuantidade;
            renderProdutos();
        }

        function removerProduto(index) {
            produtos.splice(index, 1);
            renderProdutos();
        }

        function renderProdutos() {
            const wrapper = document.getElementById('produtos-selecionados');
            wrapper.innerHTML = '';

            const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
            let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;
            descontoOrcamento = Math.min(Math.max(descontoOrcamento, 0), 100);

            const descontoPercentual = Math.max(descontoCliente, descontoOrcamento);

            let totalProdutos = 0;
            let totalProdutosComDesconto = 0;

            produtos.forEach((p, i) => {
                // ✅ Usa o preço atual (que pode ter sido alterado manualmente)
                const valorUnitarioAtual = p.preco;
                const subtotal = valorUnitarioAtual * p.quantidade;

                let subtotalComDesconto;
                let descontoEfetivo = 0;
                let tipoDesconto = 'nenhum';

                // ✅ Verifica se houve desconto manual no produto
                if (p.descontoProduto > 0) {
                    // Desconto por produto (manual) - NÃO aplica desconto percentual
                    subtotalComDesconto = subtotal;
                    tipoDesconto = 'produto';
                    descontoEfetivo = ((p.descontoProduto / p.precoOriginal) * 100).toFixed(2);
                } else if (p.liberarDesconto === 1 && descontoPercentual > 0) {
                    // Desconto percentual
                    subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
                    descontoEfetivo = descontoPercentual;
                    tipoDesconto = 'percentual';
                } else {
                    // Sem desconto
                    subtotalComDesconto = subtotal;
                }

                totalProdutos += p.precoOriginal * p.quantidade; // Total sempre com preço original
                totalProdutosComDesconto += subtotalComDesconto;

                // ✅ Badge de status do desconto
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
                <input type="hidden" name="itens[${i}][nome]" value="${p.nome}">
                <input type="hidden" name="itens[${i}][partNumber]" value="${p.partNumber || ''}">
                <input type="hidden" name="itens[${i}][fornecedor]" value="${p.fornecedor || ''}">
                <input type="hidden" name="itens[${i}][cor]" value="${p.cor || ''}">
                <input type="hidden" name="itens[${i}][liberar_desconto]" value="${p.liberarDesconto}">
                <input type="hidden" name="itens[${i}][preco_original]" value="${p.precoOriginal}">
                <input type="hidden" name="itens[${i}][preco_unitario]" value="${valorUnitarioAtual}">
                <input type="hidden" name="itens[${i}][desconto_produto]" value="${p.descontoProduto}">
                <input type="hidden" name="itens[${i}][tipo_desconto]" value="${tipoDesconto}">
                ${p.id}
            </td>
            <td class="px-3 py-2 border">
                ${p.nome}
                <div class="mt-1">${descontoStatus}</div>
            </td>
            <td class="px-3 py-2 border">${p.partNumber || '-'}</td>
            <td class="px-3 py-2 border">${p.fornecedor || '-'}</td>
            <td class="px-3 py-2 border">${p.cor || '-'}</td>
            <td class="px-3 py-2 border">
                ${p.liberarDesconto === 1 ? `
                                                    <div class="flex flex-col gap-1">
                                                        <input type="number" step="1" value="${valorUnitarioAtual.toFixed(2)}" 
                                                            onchange="alterarPrecoProduto(${i}, this.value)"
                                                            class="w-24 border rounded px-2 py-1 text-sm" 
                                                            title="Clique para alterar o preço"/>
                                                        ${p.descontoProduto > 0 ? `<small class="text-xs text-gray-500">Original: R$ ${p.precoOriginal.toFixed(2)}</small>` : ''}
                                                    </div>
                                                ` : `
                                                    R$ ${valorUnitarioAtual.toFixed(2)}
                                                `}
                <input type="hidden" name="itens[${i}][preco_unitario]" value="${valorUnitarioAtual}">
            </td>
            <td class="px-3 py-2 border">
                <input type="number" name="itens[${i}][quantidade]" 
                    value="${p.quantidade}" min="1"
                    onchange="alterarQuantidade(${i}, this.value)"
                    class="w-12 border rounded px-2 py-1 text-center" style="max-width: 4rem;"/>
            </td>
            <td class="px-3 py-2 border">
                R$ ${(p.precoOriginal * p.quantidade).toFixed(2)}
                <input type="hidden" name="itens[${i}][subtotal_original]" value="${(p.precoOriginal * p.quantidade).toFixed(2)}">
                <input type="hidden" name="itens[${i}][subtotal]" value="${subtotal.toFixed(2)}">
            </td>
            <td class="px-3 py-2 border ${tipoDesconto !== 'nenhum' ? 'text-green-600' : 'text-gray-600'}">
                R$ ${subtotalComDesconto.toFixed(2)}
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

            const {
                totalVidros,
                totalVidrosComDesconto
            } = calcularTotalVidros();

            const totalGeral = totalProdutos + totalVidros;
            const totalGeralComDesconto = totalProdutosComDesconto + totalVidrosComDesconto;

            document.getElementById('valor_total').value = totalGeral.toFixed(2);
            atualizarValorFinal(totalGeral, totalGeralComDesconto);

            exibirAvisoDescontoBloqueado();
        }

        function exibirAvisoDescontoBloqueado() {
            const produtosSemDesconto = produtos.filter(p => p.liberarDesconto === 0);
            const avisoExistente = document.getElementById('aviso-desconto-bloqueado');

            if (avisoExistente) {
                avisoExistente.remove();
            }

            if (produtosSemDesconto.length > 0) {
                const aviso = document.createElement('div');
                aviso.id = 'aviso-desconto-bloqueado';
                aviso.className = 'bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 mb-4';
                aviso.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        <strong>Atenção:</strong> ${produtosSemDesconto.length} produto(s) neste orçamento não permite(m) desconto.
                        O desconto percentual não será aplicado sobre esses itens.
                    </p>
                </div>
            </div>
        `;

                const tabelaProdutos = document.getElementById('produtos-selecionados').closest('.space-y-4');
                tabelaProdutos.insertBefore(aviso, tabelaProdutos.firstChild.nextSibling);
            }
        }

        function atualizarValorFinal(total = null, totalComDesconto = null) {
            if (total === null) {
                let totalProdutos = 0;
                let totalProdutosComDesconto = 0;

                const descontoCliente = parseFloat(document.querySelector('[name="desconto_aprovado"]').value) || 0;
                let descontoOrcamento = parseFloat(document.querySelector('[name="desconto"]').value) || 0;

                if (descontoOrcamento < 0) descontoOrcamento = 0;
                if (descontoOrcamento > 100) descontoOrcamento = 100;

                const descontoPercentual = Math.max(descontoCliente, descontoOrcamento);

                produtos.forEach(p => {
                    const subtotal = p.preco * p.quantidade;
                    const subtotalOriginal = p.precoOriginal * p.quantidade;
                    let subtotalComDesconto;

                    // ✅ Se tem desconto no produto, não aplica percentual
                    if (p.descontoProduto > 0) {
                        subtotalComDesconto = subtotal;
                    } else if (p.liberarDesconto === 1 && descontoPercentual > 0) {
                        subtotalComDesconto = subtotal - (subtotal * (descontoPercentual / 100));
                    } else {
                        subtotalComDesconto = subtotal;
                    }

                    totalProdutos += subtotalOriginal;
                    totalProdutosComDesconto += subtotalComDesconto;
                });

                const {
                    totalVidros,
                    totalVidrosComDesconto
                } = calcularTotalVidros();

                total = totalProdutos + totalVidros;
                totalComDesconto = totalProdutosComDesconto + totalVidrosComDesconto;

                document.getElementById('valor_total').value = total.toFixed(2);
            }

            const guia_recolhimento = parseFloat(document.querySelector('[name="guia_recolhimento"]').value) || 0;
            const descontoEspecificoInput = document.querySelector('[name="desconto_especifico"]');

            let descontoEspecifico = parseFloat(
                descontoEspecificoInput.value.replace(/\./g, '').replace(',', '.')
            ) || 0;

            const valorSemDescontoFinal = total + guia_recolhimento;
            let valorFinalComDesconto = totalComDesconto - descontoEspecifico + guia_recolhimento;

            if (valorFinalComDesconto < 0) valorFinalComDesconto = 0;

            const maxDesconto = totalComDesconto + guia_recolhimento;
            if (descontoEspecifico > maxDesconto) {
                descontoEspecifico = maxDesconto;
                descontoEspecificoInput.value = maxDesconto.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            document.getElementById('valor_final').value = valorFinalComDesconto.toFixed(2);
        }



        // Event listeners
        document.addEventListener("DOMContentLoaded", () => {
            // Listener para desconto
            document.querySelector('[name="desconto"]').addEventListener("input", function() {
                let val = parseFloat(this.value) || 0;

                if (val < 0) val = 0;
                if (val > 100) val = 100;

                this.value = val;

                // Recalcular todos os vidros
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });

                // Recalcular produtos
                renderProdutos();
            });

            // Listener para guia_recolhimento
            document.querySelector('[name="guia_recolhimento"]').addEventListener("input", () => {
                atualizarValorFinal();
            });

            document.querySelector('[name="desconto_especifico"]').addEventListener("input", () => {
                atualizarValorFinal();
            });

            // Listener para desconto aprovado (caso seja editável)
            document.querySelector('[name="desconto_aprovado"]').addEventListener("input", function() {
                // Recalcular todos os vidros
                document.querySelectorAll('#vidros-wrapper [name*="[altura]"]').forEach(input => {
                    if (input.value) calcularVidro(input);
                });

                // Recalcular produtos
                renderProdutos();
            });

            // Restauração de dados old()
            const oldItens = @json(old('itens', []));
            if (oldItens.length > 0) {
                oldItens.forEach(item => {
                    produtos.push({
                        id: item.id,
                        nome: item.nome,
                        preco: parseFloat(item.preco_unitario),
                        precoOriginal: parseFloat(item.preco_original),
                        quantidade: parseInt(item.quantidade),
                        fornecedor: item.fornecedor,
                        cor: item.cor,
                        partNumber: item.partNumber,
                        liberarDesconto: parseInt(item.liberar_desconto),
                        descontoProduto: parseFloat(item.desconto_produto) || 0
                    });
                });
                renderProdutos();
            }

            const oldVidros = @json(old('vidros', []));
            if (oldVidros.length > 0) {
                oldVidros.forEach((vidro, idx) => {
                    addVidro();
                    const container = document.querySelector(`#vidros-wrapper > div:nth-child(${idx + 1})`);
                    if (container) {
                        container.querySelector('[name*="[descricao]"]').value = vidro.descricao || '';
                        container.querySelector('[name*="[quantidade]"]').value = vidro.quantidade || 1;
                        container.querySelector('[name*="[preco_m2]"]').value = vidro.preco_m2 || 0;
                        container.querySelector('[name*="[altura]"]').value = vidro.altura || 0;
                        container.querySelector('[name*="[largura]"]').value = vidro.largura || 0;
                        
                        // Disparar cálculo
                        calcularVidro(container.querySelector('[name*="[altura]"]'));
                    }
                });
            }
        });

        let produtoSelecionado = null; // variável global para armazenar o produto temporário

        function selecionarProdutoComQuantidade(id, nome, preco, fornecedor = '', cor = '', partNumber = '',
            liberarDesconto = '1', estoqueDisponivel = null) {
            produtoSelecionado = {
                id,
                nome,
                preco: parseFloat(preco),
                fornecedor,
                cor,
                partNumber,
                liberarDesconto: parseInt(liberarDesconto),
                quantidade: 1,
                estoqueDisponivel: estoqueDisponivel // ✅ NOVO
            };

            document.getElementById('produto-nome').textContent = nome;
            document.getElementById('quantidade-produto').value = 1;

            // ... resto do código existente

            const modalBody = document.getElementById('modal-quantidade').querySelector('.bg-white');
            const avisoExistente = modalBody.querySelector('.aviso-desconto-modal');

            if (avisoExistente) {
                avisoExistente.remove();
            }

            if (parseInt(liberarDesconto) === 0) {
                const aviso = document.createElement('div');
                aviso.className =
                    'aviso-desconto-modal bg-red-50 border border-red-200 rounded p-2 mb-3 text-sm text-red-700';
                aviso.innerHTML = '⚠️ Este produto não permite desconto';

                const inputQuantidade = document.getElementById('quantidade-produto');
                inputQuantidade.parentNode.insertBefore(aviso, inputQuantidade);
            }

            document.getElementById('modal-quantidade').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modal-quantidade').classList.add('hidden');
            document.getElementById('aviso-estoque').classList.add('hidden');

            const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
            btnConfirmar.textContent = 'Adicionar';
            btnConfirmar.classList.add('bg-blue-500', 'hover:bg-blue-600');
            btnConfirmar.classList.remove('bg-amber-500', 'hover:bg-amber-600');

            produtoSelecionado = null;
        }

        function confirmarQuantidade() {
            if (!produtoSelecionado) return;

            // ✅ Se já está pendente (usuário viu o aviso e clicou de novo), confirma
            if (produtoSelecionado._pendente) {
                _adicionarProdutoConfirmado(produtoSelecionado._pendente);
                return;
            }

            const quantidade = parseInt(document.getElementById('quantidade-produto').value) || 1;
            const estoque = produtoSelecionado.estoqueDisponivel;

            // Esconde aviso anterior
            document.getElementById('aviso-estoque').classList.add('hidden');

            // ✅ Verifica estoque — agora funciona pois estoque chega como número
            if (estoque !== null && estoque !== undefined && quantidade > estoque) {
                const avisoEstoque = document.getElementById('aviso-estoque');
                const avisoTexto = document.getElementById('aviso-estoque-texto');

                avisoTexto.textContent =
                    `Você solicitou ${quantidade} unidade(s), mas há apenas ${estoque} em estoque. O pedido será gerado, mas pode haver indisponibilidade na entrega.`;

                avisoEstoque.classList.remove('hidden');

                const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
                btnConfirmar.textContent = '⚠️ Estou ciente, adicionar mesmo assim';
                btnConfirmar.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                btnConfirmar.classList.add('bg-amber-500', 'hover:bg-amber-600');

                produtoSelecionado._pendente = quantidade; // Guarda para o próximo clique
                return;
            }

            _adicionarProdutoConfirmado(quantidade);
        }

        function _adicionarProdutoConfirmado(quantidade) {
            produtoSelecionado.quantidade = quantidade ?? produtoSelecionado._pendente;

            adicionarProduto(
                produtoSelecionado.id,
                produtoSelecionado.nome,
                produtoSelecionado.preco,
                produtoSelecionado.fornecedor,
                produtoSelecionado.cor,
                produtoSelecionado.quantidade,
                produtoSelecionado.partNumber,
                produtoSelecionado.liberarDesconto,
                produtoSelecionado.estoqueDisponivel // ✅ passa o estoque
            );

            const btnConfirmar = document.getElementById('btn-confirmar-quantidade');
            btnConfirmar.textContent = 'Adicionar';
            btnConfirmar.classList.add('bg-blue-500', 'hover:bg-blue-600');
            btnConfirmar.classList.remove('bg-amber-500', 'hover:bg-amber-600');

            fecharModal();
        }
    </script>

</x-layouts.app>
