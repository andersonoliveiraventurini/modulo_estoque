<x-layouts.app :title="__('Criar Produto')">

@php
    $pre = session('prefill_produto', []);
    $p = fn(string $key, $default = '') => old($key, $pre[$key] ?? $default);
@endphp

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600" />
                    Cadastro de produto
                </h2>

                {{-- Aviso de origem quando vier de uma cotação --}}
                @if (!empty($pre['_origem_consulta_preco_id']))
                    <div class="mb-6 p-3 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-lg text-sm text-violet-700 dark:text-violet-300 flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                        </svg>
                        Dados pré-preenchidos a partir do item da
                        <a href="{{ route('consulta_preco.show_grupo', $pre['_origem_grupo_id']) }}"
                           class="font-semibold underline hover:text-violet-900">
                            Cotação #{{ $pre['_origem_grupo_id'] }}
                        </a>
                        — revise e complete antes de salvar.
                    </div>
                @endif

                <form id="produtoForm" action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- ── Dados Básicos ───────────────────────────── --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nome do Produto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nome" required
                                       value="{{ $p('nome') }}"
                                       placeholder="Digite o nome"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                @error('nome') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Código de Barras</label>
                                <input type="text" name="codigo_barras"
                                       value="{{ $p('codigo_barras') }}"
                                       placeholder="7891234567890"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">SKU / Código</label>
                                <input type="text" name="sku" required
                                       value="{{ $p('sku') }}"
                                       placeholder="PRD123456"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fornecedores ativos</label>
                                <select name="fornecedor_id" required
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}"
                                            {{ $p('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                                            {{ $fornecedor->nome_fantasia }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Part Number</label>
                                <input type="text" name="part_number"
                                       value="{{ $p('part_number') }}"
                                       placeholder="Digite o código do fornecedor"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cor</label>
                                <select name="cor_id"
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    @foreach ($cores as $cor)
                                        <option value="{{ $cor->id }}"
                                            {{ $p('cor_id') == $cor->id ? 'selected' : '' }}>
                                            {{ $cor->nome }}
                                        </option>
                                    @endforeach
                                </select>  {{-- ← faltava </select> --}}
                            </div>  {{-- ← estava <div> ao invés de </div> --}}

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Unidade de Medida <span class="text-red-500">*</span>
                                </label>
                                @php
                                    $unidades = [
                                        'UN'  => 'UN - Unidade',
                                        'PC'  => 'PC - Peça',
                                        'CX'  => 'CX - Caixa',
                                        'KG'  => 'KG - Quilograma',
                                        'G'   => 'G - Grama',
                                        'M'   => 'M - Metro',
                                        'M2'  => 'M2 - Metro quadrado',
                                        'M3'  => 'M3 - Metro cúbico',
                                        'L'   => 'L - Litro',
                                        'ML'  => 'ML - Mililitro',
                                        'PAR' => 'PAR - Par',
                                        'RL'  => 'RL - Rolo',
                                        'PT'  => 'PT - Pacote',
                                    ];
                                @endphp
                                <select name="unidade_medida" required
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    @foreach ($unidades as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ $p('unidade_medida') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidade_medida') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Peso</label>
                                <input type="number" step="1" name="peso"
                                       value="{{ $p('peso') }}"
                                       placeholder="0.000"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Estoque Mínimo</label>
                                <input type="number" step="1" name="estoque_minimo"
                                       value="{{ $p('estoque_minimo') }}"
                                       placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Categoria</label>
                                <select id="categoria_id" name="categoria_id"
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}"
                                            {{ $p('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Subcategoria</label>
                                <select id="subcategoria_id" name="subcategoria_id"
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                        style="{{ $p('categoria_id') ? '' : 'display:none;' }}">
                                    <option value="">Selecione...</option>
                                    @foreach ($subcategorias as $sub)
                                        <option value="{{ $sub->id }}"
                                                data-categoria="{{ $sub->categoria_id }}"
                                            {{ $p('subcategoria_id') == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Produto sob encomenda</label>
                                <select name="flag_encomenda"
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    <option value="1" {{ $p('flag_encomenda') == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ $p('flag_encomenda') === '0' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── Informações Fiscais - Entrada ────────────── --}}
                    <div class="space-y-4">
                        <hr class="border-zinc-200 dark:border-zinc-700" />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Informações Fiscais — Entrada
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Preço de Base (sem imposto)</label>
                                <input type="number" step="1" name="preco_base"
                                       value="{{ $p('preco_base') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">% ICMS</label>
                                <input type="number" step="1" name="icms"
                                       value="{{ $p('icms') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">PIS</label>
                                <input type="number" step="1" name="pis"
                                       value="{{ $p('pis') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cofins</label>
                                <input type="number" step="1" name="cofins"
                                       value="{{ $p('cofins') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">MVA</label>
                                <input type="number" step="1" name="mva"
                                       value="{{ $p('mva') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- ── Financeiro ───────────────────────────────── --}}
                    <div class="space-y-4">
                        <hr class="border-zinc-200 dark:border-zinc-700" />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-banknotes class="w-5 h-5 text-primary-600" />
                            Financeiro
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Preço de Custo</label>
                                <input type="number" step="1" name="preco_custo"
                                       value="{{ $p('preco_custo') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Custo Frete Fornecedor</label>
                                <input type="number" step="1" name="custo_frete_fornecedor"
                                       value="{{ $p('custo_frete_fornecedor') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Custo Operacional</label>
                                <input type="number" step="1" name="custo_operacional"
                                       value="{{ $p('custo_operacional') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Margem de Lucro</label>
                                <input type="number" step="1" name="margem_lucro"
                                       value="{{ $p('margem_lucro') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Preço de Venda</label>
                                <input type="number" step="1" name="preco_venda"
                                       value="{{ $p('preco_venda') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Liberar Desconto <span class="text-red-500">*</span></label>
                                <select name="liberar_desconto" required
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    <option value="1" {{ $p('liberar_desconto') == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ $p('liberar_desconto') === '0' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Porcentagem de Desconto</label>
                                <input type="number" step="1" name="porcentagem_desconto"
                                       value="{{ $p('porcentagem_desconto') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Valor do Desconto</label>
                                <input type="number" step="1" name="valor_desconto"
                                       value="{{ $p('valor_desconto') }}" placeholder="0.00"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- ── Observações ──────────────────────────────── --}}
                    <div class="space-y-4">
                        <hr class="border-zinc-200 dark:border-zinc-700" />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5 text-primary-600" />
                            Observações
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Descrição</label>
                                <textarea name="descricao" rows="3"
                                          placeholder="Escreva as características e detalhes do produto..."
                                          class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ $p('descricao') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observações</label>
                                <textarea name="observacoes" rows="3"
                                          placeholder="Observações gerais, condições especiais, informações adicionais..."
                                          class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ $p('observacoes') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Imagem do Produto</label>
                                <input type="file" name="images[]" id="imagesInput" multiple accept="image/png,image/jpeg,image/gif"
                                       class="mt-1 block w-full text-sm text-gray-600 border border-zinc-300 dark:border-zinc-600 rounded-lg p-2 dark:bg-zinc-800">
                                <p class="text-xs text-gray-500 mt-1">Selecione uma ou mais imagens (PNG, JPG ou GIF até 5MB cada)</p>
                                
                                <div id="imagePreviews" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Informações Fiscais - Saída ──────────────── --}}
                    <div class="space-y-4">
                        <hr class="border-zinc-200 dark:border-zinc-700" />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-5 h-5 text-primary-600" />
                            Informações Fiscais — Saída
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipo Produto SPED</label>
                                @php
                                    $tiposSped = [
                                        '00' => '00 - Mercadoria para Revenda',
                                        '01' => '01 - Matéria-Prima',
                                        '02' => '02 - Embalagem',
                                        '03' => '03 - Produto em Processo',
                                        '04' => '04 - Produto Acabado',
                                        '05' => '05 - Subproduto',
                                        '06' => '06 - Produto Intermediário',
                                        '07' => '07 - Material de Uso e Consumo',
                                        '08' => '08 - Ativo Imobilizado',
                                        '09' => '09 - Serviços',
                                        '10' => '10 - Outros Insumos',
                                        '99' => '99 - Outras',
                                    ];
                                @endphp
                                <select name="tipo_sped"
                                        class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Selecione...</option>
                                    @foreach ($tiposSped as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ $p('tipo_sped') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    NCM — Nomenclatura Comum do Mercosul (8 dígitos) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="ncm" required
                                       value="{{ $p('ncm') }}"
                                       placeholder="12345678"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                @error('ncm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">% Substituição Tributária</label>
                                <input type="number" step="1" name="substituicao_tributaria"
                                       value="{{ $p('substituicao_tributaria') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">% ICMS</label>
                                <input type="number" step="1" name="icms_saida"
                                       value="{{ $p('icms_saida') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">PIS</label>
                                <input type="number" step="1" name="pis_saida"
                                       value="{{ $p('pis_saida') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cofins</label>
                                <input type="number" step="1" name="cofins_saida"
                                       value="{{ $p('cofins_saida') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">CL Fiscal</label>
                                <input type="text" name="classificacao_fiscal"
                                       value="{{ $p('classificacao_fiscal') }}"
                                       class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- ── Ações ─────────────────────────────────────── --}}
                    <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <x-button type="submit" variant="primary" id="btnSubmit">Cadastrar Produto</x-button>
                        <x-button type="reset" variant="secondary">Limpar Formulário</x-button>
                        @if (!empty($pre['_origem_grupo_id']))
                            <a href="{{ route('consulta_preco.show_grupo', $pre['_origem_grupo_id']) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm text-zinc-600 hover:text-zinc-900 border border-zinc-300 rounded-lg transition">
                                ← Voltar para Cotação #{{ $pre['_origem_grupo_id'] }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const subcategorias = @json($subcategorias->map(fn($s) => ['id' => $s->id, 'nome' => $s->nome, 'categoria_id' => $s->categoria_id]));
        const prefillSubcategoriaId = {{ $p('subcategoria_id') ?: 'null' }};
        const prefillCategoriaId    = {{ $p('categoria_id') ?: 'null' }};

        const categoriaSelect    = document.getElementById('categoria_id');
        const subcategoriaSelect = document.getElementById('subcategoria_id');

        function renderSubcategorias(categoriaId, selectedId = null) {
            subcategoriaSelect.innerHTML = '<option value="">Selecione...</option>';
            const filtradas = subcategorias.filter(s => s.categoria_id == categoriaId);

            if (filtradas.length > 0) {
                filtradas.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.nome;
                    if (selectedId && sub.id == selectedId) opt.selected = true;
                    subcategoriaSelect.appendChild(opt);
                });
                subcategoriaSelect.style.display = 'block';
            } else {
                subcategoriaSelect.style.display = 'none';
            }
        }

        // Inicializa se vier com categoria pré-preenchida
        if (prefillCategoriaId) {
            renderSubcategorias(prefillCategoriaId, prefillSubcategoriaId);
        }

        categoriaSelect.addEventListener('change', function () {
            if (this.value) {
                renderSubcategorias(this.value);
            } else {
                subcategoriaSelect.innerHTML = '<option value="">Selecione...</option>';
                subcategoriaSelect.style.display = 'none';
            }
        });

        // Image Previews State
        let selectedFiles = [];
        const imagesInput = document.getElementById('imagesInput');
        const imagePreviews = document.getElementById('imagePreviews');

        imagesInput.addEventListener('change', function() {
            for (const file of this.files) {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                }
            }
            imagesInput.value = ''; // Reset input so user can add the same file again if desired
            renderPreviews();
        });

        function renderPreviews() {
            imagePreviews.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative border rounded-lg p-2 flex flex-col items-center shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-32 object-cover rounded shadow-sm">
                                     <span class="text-xs text-center mt-2 truncate w-full" title="${file.name}">${file.name}</span>
                                     <button type="button" class="absolute top-2 right-2 text-red-600 text-sm font-bold bg-white rounded-full w-6 h-6 flex items-center justify-center shadow hover:bg-red-50" onclick="removeFile(${index})">✕</button>`;
                    imagePreviews.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }

        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
        };

        // AJAX Form Submission
        document.getElementById('produtoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = document.getElementById('btnSubmit');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Salvando...';

            // Clear previous frontend JS errors
            document.querySelectorAll('.js-val-error').forEach(el => el.remove());

            const formData = new FormData(form);
            formData.delete('images[]'); // Clean any stray inputs
            
            selectedFiles.forEach(file => {
                formData.append('images[]', file);
            });

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok || response.redirected) {
                    if (response.url && response.url !== window.location.href) {
                        window.location.href = response.url; // Following Laravel redirect transparently
                    } else {
                        // In case it didn't redirect per URL change hook
                        const resData = await response.json().catch(() => null);
                        if (resData && resData.redirect) {
                            window.location.href = resData.redirect;
                        } else {
                            window.location.href = "{{ route('produtos.index') }}"; // Fallback
                        }
                    }
                } else if (response.status === 422) {
                    const data = await response.json();
                    
                    let firstErrorInput = null;
                    
                    for (const [field, messages] of Object.entries(data.errors)) {
                        let inputName = field;
                        // Handle array fields
                        if (field.includes('.')) {
                            const [base, idx] = field.split('.');
                            inputName = `${base}[${idx}]`;
                        }
                        
                        let input = form.querySelector(`[name="${inputName}"]`) || form.querySelector(`[name="${inputName}[]"]`);
                        
                        if (input) {
                            const err = document.createElement('p');
                            err.className = 'text-red-500 text-xs mt-1 js-val-error mb-2';
                            err.innerText = messages[0];
                            input.parentNode.appendChild(err);
                            if (!firstErrorInput) firstErrorInput = input;
                        }
                    }
                    
                    if (firstErrorInput) {
                        firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    alert('Ocorreu um erro ao processar sua requisição.');
                }
            } catch (error) {
                console.error('Submit error', error);
                alert('Erro na conexão com o servidor.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Cadastrar Produto';
            }
        });
    </script>
</x-layouts.app>
