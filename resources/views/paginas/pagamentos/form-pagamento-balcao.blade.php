<x-layouts.app :title="__('Realizar pagamento')">
    @if (!$ativo)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                alert('⚠️ Atenção: a situação do CNPJ não está ATIVA na Receita Federal.');
            });
        </script>
    @endif

    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-6xl mx-auto">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-lg border border-zinc-200 dark:border-zinc-700 p-6">

                        <!-- Header -->
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Pagamento no Balcão — Orçamento #{{ $orcamento->id }}
                            </h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Finalize o pagamento do orçamento selecionado</p>
                        </div>

                        <!-- Erros -->
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Atenção aos seguintes erros:</h4>
                                        <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                                            @foreach ($errors->all() as $error)
                                                <li>• {{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Informações do Orçamento -->
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Informações do Orçamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->cliente->nome ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $orcamento->vendedor->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Condição de Pagamento</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100 flex items-center gap-1 flex-wrap">
                                        {{ $orcamento->condicaoPagamento->nome ?? 'N/A' }}
                                        @if($condicaoEspecial)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">Especial</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                                    </p>
                                </div>
                                @if ($orcamento->totalDescontosAprovados() > 0)
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor com desconto</p>
                                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                            R$ {{ number_format($orcamento->valor_total_itens - $orcamento->totalDescontosAprovados(), 2, ',', '.') }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($condicaoEspecial && $orcamento->outros_meios_pagamento)
                                <div class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg p-3">
                                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Meio definido pelo vendedor (condição especial)
                                    </p>
                                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400 pl-6">{{ $orcamento->outros_meios_pagamento }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Formulário -->
                        <form action="{{ route('orcamentos.pagamento.salvar', $orcamento->id) }}"
                              method="POST" id="formPagamento" enctype="multipart/form-data">
                            @csrf

                            <!-- ═══════════════════════════════════════════════ -->
                            <!-- FORMAS DE PAGAMENTO                            -->
                            <!-- ═══════════════════════════════════════════════ -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Formas de Pagamento</h3>
                                        @if($condicaoPadrao)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                Condição definida no orçamento:
                                                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $condicaoPadrao->nome }}</span>
                                                — pode ser alterado pelo atendente.
                                            </p>
                                        @endif
                                    </div>
                                    @if(!$condicaoEspecial)
                                        <button type="button" onclick="adicionarForma()"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                            + Adicionar Forma
                                        </button>
                                    @endif
                                </div>

                                {{-- Alerta global de alteração --}}
                                <div id="alertaMetodoAlterado"
                                    class="hidden mb-3 bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-700 rounded-lg p-3">
                                    <p class="text-sm font-semibold text-orange-800 dark:text-orange-300 flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        Atenção: condição de pagamento diferente da original do orçamento
                                    </p>
                                    <p class="mt-0.5 text-xs text-orange-700 dark:text-orange-400 pl-6">
                                        A condição original era <strong>{{ $condicaoPadrao->nome ?? 'N/A' }}</strong>. Certifique-se que a alteração foi autorizada.
                                    </p>
                                </div>

                                {{-- Container das formas --}}
                                <div id="formasContainer" class="space-y-4">
                                    @php
                                        $formasOld  = old('formas_pagamento');
                                        $valorBase  = number_format(
                                            $orcamento->valor_total_itens - $orcamento->totalDescontosAprovados(),
                                            2, '.', ''
                                        );
                                        $formasIniciais = $formasOld
                                            ? $formasOld
                                            : [['condicao_id' => $condicaoPadrao->id ?? '', 'valor' => $valorBase]];
                                    @endphp

                                    @foreach ($formasIniciais as $idx => $formaOld)
                                        @php
                                            $isPrimeira = $idx === 0;
                                            $isExtra    = $idx > 0;
                                            $isAlterada = $isPrimeira
                                                && !empty($formaOld['condicao_id'])
                                                && (int)$formaOld['condicao_id'] !== ($condicaoPadrao->id ?? 0);
                                        @endphp
                                        <div class="forma-card rounded-xl border-2 p-4 transition-all
                                            {{ $isExtra    ? 'bg-orange-50 dark:bg-orange-900/10 border-orange-300 dark:border-orange-700' : '' }}
                                            {{ $isAlterada ? 'bg-orange-50 dark:bg-orange-900/10 border-orange-300 dark:border-orange-700' : '' }}
                                            {{ (!$isExtra && !$isAlterada) ? 'bg-gray-50 dark:bg-gray-800 border-transparent' : '' }}"
                                            data-index="{{ $idx }}"
                                            data-extra="{{ $isExtra ? 'true' : 'false' }}">

                                            <!-- Cabeçalho do card -->
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma #{{ $idx + 1 }}</span>
                                                    @if($isExtra)
                                                        <span class="badge-extra inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">⚠ Forma adicional</span>
                                                    @endif
                                                    <span class="badge-alterada hidden inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">⚠ Diferente do orçamento</span>
                                                </div>
                                                @if($isExtra)
                                                    <button type="button" onclick="removerForma(this)" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                                                @endif
                                            </div>

                                            <!-- Condição + Valor -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Condição de Pagamento *</label>
                                                    <select name="formas_pagamento[{{ $idx }}][condicao_id]" required
                                                        onchange="onCondicaoChange(this)"
                                                        class="select-condicao w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                                                        <option value="">Selecione...</option>
                                                        @foreach ($condicoesBalcao as $condicao)
                                                            <option value="{{ $condicao->id }}"
                                                                data-tipo="{{ $condicao->tipo }}"
                                                                {{ ($formaOld['condicao_id'] ?? '') == $condicao->id ? 'selected' : '' }}>
                                                                {{ $condicao->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("formas_pagamento.{$idx}.condicao_id")
                                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor *</label>
                                                    <input type="number" name="formas_pagamento[{{ $idx }}][valor]"
                                                        step="0.01" min="0" required
                                                        value="{{ $formaOld['valor'] ?? '' }}"
                                                        oninput="atualizarDesconto(); calcularValores()"
                                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                                        placeholder="0,00">
                                                    @error("formas_pagamento.{$idx}.valor")
                                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Comprovante desta forma -->
                                            <div class="comprovante-forma-section">
                                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Comprovante desta forma
                                                    <span class="text-gray-400 font-normal">(PDF, JPG, PNG, WEBP — máx. 10 MB cada)</span>
                                                </p>
                                                <div class="comprovante-drop border border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors"
                                                    onclick="document.getElementById('comp-forma-{{ $idx }}').click()"
                                                    ondragover="event.preventDefault(); this.classList.add('border-blue-400','bg-blue-50')"
                                                    ondragleave="this.classList.remove('border-blue-400','bg-blue-50')"
                                                    ondrop="handleDropForma(event, {{ $idx }})">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">📎 Clique ou arraste o comprovante aqui</p>
                                                </div>
                                                <input type="file" id="comp-forma-{{ $idx }}"
                                                    name="comprovantes_forma_{{ $idx }}[]"
                                                    multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    class="hidden"
                                                    onchange="adicionarArquivosForma({{ $idx }}, this.files)">
                                                <ul id="lista-forma-{{ $idx }}" class="mt-2 space-y-1"></ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Resumo de Valores -->
                                <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Valor a Pagar</p>
                                            <p class="text-xl font-bold text-blue-700 dark:text-blue-300" id="valorTotal">
                                                R$ {{ number_format($orcamento->valor_total_itens - $orcamento->totalDescontosAprovados(), 2, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Valor Pago</p>
                                            <p class="text-xl font-bold text-green-700 dark:text-green-300" id="valorPago">R$ 0,00</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Troco</p>
                                            <p class="text-xl font-bold text-orange-700 dark:text-orange-300" id="valorTroco">R$ 0,00</p>
                                        </div>
                                    </div>
                                    <div id="alertaFaltando" class="hidden mt-3 bg-red-100 border-2 border-red-300 rounded-md p-3">
                                        <p class="text-sm text-red-800 font-medium">⚠️ Falta pagar: <span id="valorFaltando" class="font-bold">R$ 0,00</span></p>
                                    </div>
                                    <div id="alertaPronto" class="hidden mt-3 bg-green-100 border-2 border-green-300 rounded-md p-3">
                                        <p class="text-sm text-green-800 font-medium flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            ✓ Valor correto! Pode finalizar o pagamento.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700 hidden" id="hrDesconto">

                            <!-- ═══════════════════════════════════════════════ -->
                            <!-- DESCONTO NO BALCÃO (aparece só com pix/dinheiro) -->
                            <!-- ═══════════════════════════════════════════════ -->
                            <div class="mb-6 hidden" id="secaoDesconto">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Desconto no Balcão (até 3%)</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    Aplicável apenas sobre o valor pago em <strong>Dinheiro</strong> ou <strong>PIX</strong>.
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor do Desconto</label>
                                        <input type="number" name="desconto_balcao" id="inputDesconto"
                                            step="0.01" min="0" max="0"
                                            value="{{ old('desconto_balcao', 0) }}"
                                            oninput="enforceMaxDesconto(this)"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                            placeholder="0,00">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Máximo (3% do valor em PIX/Dinheiro): <strong id="textoMaxDesconto">R$ 0,00</strong>
                                        </p>
                                        @error('desconto_balcao')
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- ═══════════════════════════════════════════════ -->
                            <!-- DOCUMENTO FISCAL                               -->
                            <!-- ═══════════════════════════════════════════════ -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Documento Fiscal</h3>
                                <div class="mb-3 flex items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Definido no orçamento:</span>
                                    @if(strtolower($orcamento->tipo_documento ?? '') === 'nota fiscal')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">📄 Nota Fiscal</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">🧾 Cupom Fiscal</span>
                                    @endif
                                </div>
                                <div class="space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="precisa_nota_fiscal" value="1"
                                            {{ old('precisa_nota_fiscal', $precisaNotaFiscal ? '1' : '') ? 'checked' : '' }}
                                            onchange="toggleNotaFiscal(this)"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Emitir Nota Fiscal?</span>
                                    </label>
                                    <div id="camposNotaFiscal" class="{{ old('precisa_nota_fiscal', $precisaNotaFiscal) ? '' : 'hidden' }} pl-7">
                                        <div class="max-w-md">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CNPJ/CPF para a Nota</label>
                                            <input type="text" name="cnpj_cpf_nota"
                                                value="{{ old('cnpj_cpf_nota', $orcamento->cliente->cnpj ?? '') }}"
                                                readonly
                                                class="w-full border border-gray-200 dark:border-gray-700 rounded-md px-3 py-2 bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 cursor-not-allowed select-none">
                                            <p class="text-xs text-gray-400 mt-1">Preenchido automaticamente com o CNPJ/CPF do cliente.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- ═══════════════════════════════════════════════ -->
                            <!-- COMPROVANTES GERAIS                            -->
                            <!-- ═══════════════════════════════════════════════ -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Comprovantes Gerais</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                    Opcional. Anexe documentos adicionais não vinculados a uma forma específica
                                    (recibos avulsos, autorizações, etc.).
                                    Formatos: <strong>PDF, JPG, PNG, WEBP</strong> — máx. <strong>10 MB</strong> cada.
                                </p>
                                <div id="dropZoneGeral"
                                    onclick="document.getElementById('inputComprovantesGeral').click()"
                                    ondragover="event.preventDefault(); this.classList.add('border-blue-400','bg-blue-50')"
                                    ondragleave="this.classList.remove('border-blue-400','bg-blue-50')"
                                    ondrop="handleDropGeral(event)"
                                    class="cursor-pointer border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center transition-colors hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10">
                                    <svg class="mx-auto w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clique para selecionar ou arraste arquivos aqui</p>
                                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG, WEBP — máx. 10 MB cada</p>
                                </div>
                                <input type="file" id="inputComprovantesGeral" name="comprovantes[]"
                                    multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                                    class="hidden"
                                    onchange="adicionarArquivosGeral(this.files)">
                                @error('comprovantes')
                                    <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                                @error('comprovantes.*')
                                    <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                                <ul id="listaComprovantesGeral" class="mt-4 space-y-2"></ul>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- ═══════════════════════════════════════════════ -->
                            <!-- OBSERVAÇÕES                                    -->
                            <!-- ═══════════════════════════════════════════════ -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                                <textarea name="observacoes" rows="3"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                    placeholder="Informações adicionais sobre o pagamento...">{{ old('observacoes', $orcamento->observacoes ?? '') }}</textarea>
                            </div>

                            <!-- Ações -->
                            <div class="flex gap-4">
                                <a href="{{ route('orcamentos.index') }}"
                                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-3 rounded-lg transition-colors shadow-md">
                                    Cancelar
                                </a>
                                <button type="button" id="btnFinalizar" onclick="confirmarSubmit()"
                                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg border-2 border-green-700 disabled:border-gray-400">
                                    Finalizar Pagamento
                                </button>
                            </div>
                        </form>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- MODAL — troco em método não-dinheiro               -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div id="modalTrocoNaoDinheiro"
                            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
                            role="dialog" aria-modal="true" aria-labelledby="modalTrocoTitulo">

                            {{-- Overlay escuro --}}
                            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="fecharModal()"></div>

                            {{-- Card do modal --}}
                            <div class="relative z-10 w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-red-200 dark:border-red-800 overflow-hidden">

                                {{-- Cabeçalho vermelho --}}
                                <div class="bg-red-600 px-6 py-4 flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 id="modalTrocoTitulo" class="text-lg font-bold text-white">Atenção — Troco em método incomum</h3>
                                        <p class="text-red-200 text-xs mt-0.5">Confirmação obrigatória</p>
                                    </div>
                                </div>

                                {{-- Corpo --}}
                                <div class="px-6 py-5 space-y-4">
                                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                                        <p class="text-sm text-red-800 dark:text-red-200 font-medium">
                                            O valor recebido é maior que o necessário, mas <strong>parte ou todo o pagamento não é em dinheiro</strong>.
                                        </p>
                                        <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                                            Dar troco em formas como PIX, cartão ou boleto <strong>exige autorização</strong> e pode
                                            envolver processos manuais de estorno ou transferência.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-center">
                                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Valor Cobrado</p>
                                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100" id="modalValorFinal">—</p>
                                        </div>
                                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                                            <p class="text-xs text-orange-600 dark:text-orange-400">Troco a Devolver</p>
                                            <p class="text-lg font-bold text-orange-700 dark:text-orange-300" id="modalValorTroco">—</p>
                                        </div>
                                    </div>

                                    {{-- Formas não-dinheiro com excesso --}}
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Formas com valor acima do necessário:</p>
                                        <ul id="modalListaFormas" class="space-y-1 text-sm text-gray-700 dark:text-gray-300"></ul>
                                    </div>

                                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-xs text-amber-800 dark:text-amber-200">
                                            Confirme apenas se um <strong>supervisor ou gerente autorizou</strong> o recebimento com troco neste método.
                                        </p>
                                    </div>
                                </div>

                                {{-- Rodapé com ações --}}
                                <div class="px-6 pb-6 flex gap-3">
                                    <button type="button" onclick="fecharModal()"
                                        class="flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 font-medium px-4 py-3 rounded-lg transition-colors">
                                        ← Corrigir valores
                                    </button>
                                    <button type="button" onclick="confirmarComTroco()"
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-3 rounded-lg transition-colors shadow-md">
                                        Confirmar mesmo assim
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // ─── Constantes PHP → JS ─────────────────────────────────────────────────
                let contadorFormas        = {{ count($formasIniciais ?? []) }};
                const valorTotalOrcamento = {{ $orcamento->valor_total_itens - ($orcamento->totalDescontosAprovados() ?? 0) }};
                const condicaoPadraoId    = {{ $condicaoPadrao->id ?? 'null' }};
                const permiteDesconto     = {{ $permiteDescontoBalcao ? 'true' : 'false' }};

                // Mapa condicao_id → tipo (gerado pelo PHP)
                const condicaoTipo = {
                    @foreach($condicoesBalcao as $c)
                        {{ $c->id }}: '{{ $c->tipo }}',
                    @endforeach
                };

                const TIPOS_COM_DESCONTO = ['dinheiro', 'pix'];

                // DataTransfer por forma (índice) + um para os gerais
                const dtFormas = {};
                const dtGeral  = new DataTransfer();

                // ─── Calcular valor pix/dinheiro ─────────────────────────────────────────
                function calcularValorPixDinheiro() {
                    let total = 0;
                    document.querySelectorAll('.forma-card').forEach(card => {
                        const select = card.querySelector('.select-condicao');
                        const input  = card.querySelector('[name$="[valor]"]');
                        if (!select || !input) return;
                        const tipo = condicaoTipo[parseInt(select.value)];
                        if (TIPOS_COM_DESCONTO.includes(tipo)) {
                            total += parseFloat(input.value || 0);
                        }
                    });
                    return total;
                }

                // ─── Desconto de balcão ──────────────────────────────────────────────────
                function atualizarDesconto() {
                    if (!permiteDesconto) return;
                    const valorPD      = calcularValorPixDinheiro();
                    const maxDesconto  = valorPD * 0.03;
                    const inputD       = document.getElementById('inputDesconto');
                    const secao        = document.getElementById('secaoDesconto');
                    const hr           = document.getElementById('hrDesconto');
                    const textoMax     = document.getElementById('textoMaxDesconto');

                    const temPD = valorPD > 0;
                    secao?.classList.toggle('hidden', !temPD);
                    hr?.classList.toggle('hidden', !temPD);

                    if (temPD && inputD) {
                        inputD.max = maxDesconto.toFixed(2);
                        if (textoMax) textoMax.textContent = fmt(maxDesconto);
                        if (parseFloat(inputD.value || 0) > maxDesconto) {
                            inputD.value = maxDesconto.toFixed(2);
                        }
                    } else if (inputD) {
                        inputD.value = '0';
                    }
                }

                // ─── Calcular totais ─────────────────────────────────────────────────────
                function calcularValores() {
                    const desconto   = parseFloat(document.getElementById('inputDesconto')?.value || 0);
                    const valorFinal = Math.max(0, valorTotalOrcamento - desconto);

                    let valorPago = 0;
                    document.querySelectorAll('[name^="formas_pagamento"][name$="[valor]"]').forEach(i => {
                        valorPago += parseFloat(i.value || 0);
                    });

                    const troco    = Math.max(0, valorPago - valorFinal);
                    const faltando = Math.max(0, valorFinal - valorPago);

                    document.getElementById('valorTotal').textContent = fmt(valorFinal);
                    document.getElementById('valorPago').textContent  = fmt(valorPago);
                    document.getElementById('valorTroco').textContent = fmt(troco);

                    // Coloração do troco: laranja normal se dinheiro, vermelho se não-dinheiro
                    const trocoEl = document.getElementById('valorTroco');
                    if (troco > 0.01 && temTrocoNaoDinheiro(valorFinal)) {
                        trocoEl.classList.remove('text-orange-700', 'dark:text-orange-300');
                        trocoEl.classList.add('text-red-600', 'dark:text-red-400', 'font-extrabold');
                    } else {
                        trocoEl.classList.remove('text-red-600', 'dark:text-red-400', 'font-extrabold');
                        trocoEl.classList.add('text-orange-700', 'dark:text-orange-300');
                    }

                    const btn = document.getElementById('btnFinalizar');
                    if (faltando > 0.01) {
                        document.getElementById('alertaFaltando').classList.remove('hidden');
                        document.getElementById('alertaPronto').classList.add('hidden');
                        document.getElementById('valorFaltando').textContent = fmt(faltando);
                        btn.disabled = true;
                    } else {
                        document.getElementById('alertaFaltando').classList.add('hidden');
                        document.getElementById('alertaPronto').classList.remove('hidden');
                        btn.disabled = false;
                    }
                }

                /**
                 * Retorna true se houver troco e existir alguma forma que NÃO é dinheiro
                 * com valor que, sozinha ou em conjunto, gera o excedente.
                 */
                function temTrocoNaoDinheiro(valorFinal) {
                    let totalNaoDinheiro = 0;
                    document.querySelectorAll('.forma-card').forEach(card => {
                        const select = card.querySelector('.select-condicao');
                        const input  = card.querySelector('[name$="[valor]"]');
                        if (!select || !input) return;
                        const tipo = condicaoTipo[parseInt(select.value)];
                        if (tipo !== 'dinheiro') {
                            totalNaoDinheiro += parseFloat(input.value || 0);
                        }
                    });
                    // Há troco de não-dinheiro se o total não-dinheiro + dinheiro > valorFinal
                    // e existe pelo menos uma forma não-dinheiro com valor > 0
                    const valorPago = Array.from(
                        document.querySelectorAll('[name^="formas_pagamento"][name$="[valor]"]')
                    ).reduce((s, i) => s + parseFloat(i.value || 0), 0);
                    return totalNaoDinheiro > 0 && valorPago > valorFinal + 0.01;
                }

                /**
                 * Coleta as formas não-dinheiro que contribuem para o troco,
                 * retornando array de { nome, valor, tipo }.
                 */
                function formasNaoDinheiroComExcesso() {
                    const desconto   = parseFloat(document.getElementById('inputDesconto')?.value || 0);
                    const valorFinal = Math.max(0, valorTotalOrcamento - desconto);
                    const resultado  = [];

                    document.querySelectorAll('.forma-card').forEach(card => {
                        const select = card.querySelector('.select-condicao');
                        const input  = card.querySelector('[name$="[valor]"]');
                        if (!select || !input) return;
                        const tipo  = condicaoTipo[parseInt(select.value)];
                        const valor = parseFloat(input.value || 0);
                        if (tipo && tipo !== 'dinheiro' && valor > 0) {
                            const nomeEl = select.options[select.selectedIndex];
                            resultado.push({ nome: nomeEl?.text ?? tipo, valor, tipo });
                        }
                    });

                    return resultado;
                }

                // ─── Modal de troco em não-dinheiro ──────────────────────────────────────
                let _submitAutorizado = false;

                function confirmarSubmit() {
                    const desconto   = parseFloat(document.getElementById('inputDesconto')?.value || 0);
                    const valorFinal = Math.max(0, valorTotalOrcamento - desconto);
                    const valorPago  = Array.from(
                        document.querySelectorAll('[name^="formas_pagamento"][name$="[valor]"]')
                    ).reduce((s, i) => s + parseFloat(i.value || 0), 0);
                    const troco = valorPago - valorFinal;

                    // Se já foi autorizado pelo modal, submete direto
                    if (_submitAutorizado) {
                        _submitAutorizado = false;
                        document.getElementById('formPagamento').submit();
                        return;
                    }

                    // Há troco em forma não-dinheiro → mostra modal
                    if (troco > 0.01 && temTrocoNaoDinheiro(valorFinal)) {
                        abrirModal(valorFinal, troco);
                        return;
                    }

                    // Caso normal: submete
                    document.getElementById('formPagamento').submit();
                }

                function abrirModal(valorFinal, troco) {
                    document.getElementById('modalValorFinal').textContent = fmt(valorFinal);
                    document.getElementById('modalValorTroco').textContent = fmt(troco);

                    const lista = document.getElementById('modalListaFormas');
                    lista.innerHTML = '';
                    formasNaoDinheiroComExcesso().forEach(f => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-2';
                        li.innerHTML = `
                            <span class="inline-block w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span>
                            <span><strong>${f.nome}</strong> — ${fmt(f.valor)}</span>`;
                        lista.appendChild(li);
                    });

                    document.getElementById('modalTrocoNaoDinheiro').classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                function fecharModal() {
                    document.getElementById('modalTrocoNaoDinheiro').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                function confirmarComTroco() {
                    fecharModal();
                    _submitAutorizado = true;
                    document.getElementById('formPagamento').submit();
                }

                // Fecha modal com ESC
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') fecharModal();
                });

                const fmt = v => 'R$ ' + v.toFixed(2).replace('.', ',');

                // ─── Alteração de condição ───────────────────────────────────────────────
                function onCondicaoChange(selectEl) {
                    const card      = selectEl.closest('.forma-card');
                    const isExtra   = card.dataset.extra === 'true';
                    const badge     = card.querySelector('.badge-alterada');
                    const selecionado = parseInt(selectEl.value) || null;

                    if (!isExtra && condicaoPadraoId !== null) {
                        const alterado = selecionado !== condicaoPadraoId;
                        card.classList.toggle('bg-orange-50',          alterado);
                        card.classList.toggle('dark:bg-orange-900/10', alterado);
                        card.classList.toggle('border-orange-300',     alterado);
                        card.classList.toggle('dark:border-orange-700',alterado);
                        card.classList.toggle('bg-gray-50',            !alterado);
                        card.classList.toggle('dark:bg-gray-800',      !alterado);
                        card.classList.toggle('border-transparent',    !alterado);
                        badge?.classList.toggle('hidden', !alterado);
                    }

                    atualizarAlertaGlobal();
                    atualizarDesconto();
                    calcularValores();
                }

                // ─── Alerta global ───────────────────────────────────────────────────────
                function atualizarAlertaGlobal() {
                    let exibir = false;
                    document.querySelectorAll('.forma-card').forEach(card => {
                        if (card.dataset.extra === 'true') { exibir = true; return; }
                        const sel = card.querySelector('.select-condicao');
                        if (sel && condicaoPadraoId && (parseInt(sel.value) || null) !== condicaoPadraoId) exibir = true;
                    });
                    document.getElementById('alertaMetodoAlterado')?.classList.toggle('hidden', !exibir);
                }

                // ─── Adicionar / remover formas ──────────────────────────────────────────
                function adicionarForma() {
                    const container  = document.getElementById('formasContainer');
                    const idx        = contadorFormas;
                    const opcoesHtml = Array.from(document.querySelector('.select-condicao').options)
                        .map(o => `<option value="${o.value}" data-tipo="${o.dataset.tipo}">${o.text}</option>`)
                        .join('');

                    container.insertAdjacentHTML('beforeend', `
                        <div class="forma-card bg-orange-50 dark:bg-orange-900/10 border-2 border-orange-300 dark:border-orange-700 rounded-xl p-4"
                             data-index="${idx}" data-extra="true">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma #${idx + 1}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">⚠ Forma adicional</span>
                                </div>
                                <button type="button" onclick="removerForma(this)" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Condição de Pagamento *</label>
                                    <select name="formas_pagamento[${idx}][condicao_id]" required
                                        onchange="onCondicaoChange(this)"
                                        class="select-condicao w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                                        <option value="">Selecione...</option>
                                        ${opcoesHtml}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor *</label>
                                    <input type="number" name="formas_pagamento[${idx}][valor]"
                                        step="0.01" min="0" required
                                        oninput="atualizarDesconto(); calcularValores()"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                                        placeholder="0,00">
                                </div>
                            </div>
                            <div class="comprovante-forma-section">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Comprovante desta forma <span class="text-gray-400 font-normal">(PDF, JPG, PNG, WEBP — máx. 10 MB)</span></p>
                                <div class="comprovante-drop border border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 text-center cursor-pointer hover:border-blue-400 transition-colors"
                                    onclick="document.getElementById('comp-forma-${idx}').click()"
                                    ondragover="event.preventDefault()"
                                    ondrop="handleDropForma(event, ${idx})">
                                    <p class="text-xs text-gray-500">📎 Clique ou arraste o comprovante aqui</p>
                                </div>
                                <input type="file" id="comp-forma-${idx}" name="comprovantes_forma_${idx}[]"
                                    multiple accept=".pdf,.jpg,.jpeg,.png,.webp" class="hidden"
                                    onchange="adicionarArquivosForma(${idx}, this.files)">
                                <ul id="lista-forma-${idx}" class="mt-2 space-y-1"></ul>
                            </div>
                        </div>`);

                    dtFormas[idx] = new DataTransfer();
                    contadorFormas++;
                    atualizarAlertaGlobal();
                }

                function removerForma(btn) {
                    const container = document.getElementById('formasContainer');
                    if (container.querySelectorAll('.forma-card').length <= 1) return;
                    const card = btn.closest('.forma-card');
                    const idx  = parseInt(card.dataset.index);
                    delete dtFormas[idx];
                    card.remove();
                    atualizarAlertaGlobal();
                    atualizarDesconto();
                    calcularValores();
                }

                // ─── Upload comprovante por forma ────────────────────────────────────────
                function adicionarArquivosForma(idx, files) {
                    if (!dtFormas[idx]) dtFormas[idx] = new DataTransfer();
                    processarArquivos(files, dtFormas[idx], function() {
                        document.getElementById(`comp-forma-${idx}`).files = dtFormas[idx].files;
                        renderizarListaForma(idx);
                    });
                }

                function removerArquivoForma(idx, fileIdx) {
                    dtFormas[idx].items.remove(fileIdx);
                    document.getElementById(`comp-forma-${idx}`).files = dtFormas[idx].files;
                    renderizarListaForma(idx);
                }

                function handleDropForma(event, idx) {
                    event.preventDefault();
                    adicionarArquivosForma(idx, event.dataTransfer.files);
                }

                function renderizarListaForma(idx) {
                    renderizarLista(
                        document.getElementById(`lista-forma-${idx}`),
                        dtFormas[idx],
                        (fi) => removerArquivoForma(idx, fi)
                    );
                }

                // ─── Upload comprovante geral ────────────────────────────────────────────
                function adicionarArquivosGeral(files) {
                    processarArquivos(files, dtGeral, function() {
                        document.getElementById('inputComprovantesGeral').files = dtGeral.files;
                        renderizarListaGeral();
                    });
                }

                function removerArquivoGeral(fileIdx) {
                    dtGeral.items.remove(fileIdx);
                    document.getElementById('inputComprovantesGeral').files = dtGeral.files;
                    renderizarListaGeral();
                }

                function handleDropGeral(event) {
                    event.preventDefault();
                    document.getElementById('dropZoneGeral').classList.remove('border-blue-400', 'bg-blue-50');
                    adicionarArquivosGeral(event.dataTransfer.files);
                }

                function renderizarListaGeral() {
                    renderizarLista(
                        document.getElementById('listaComprovantesGeral'),
                        dtGeral,
                        (fi) => removerArquivoGeral(fi)
                    );
                }

                // ─── Processamento e renderização comum ──────────────────────────────────
                const MIMES_ACEITOS = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

                function processarArquivos(files, dt, callback) {
                    Array.from(files).forEach(file => {
                        const duplicado = Array.from(dt.files).some(f => f.name === file.name && f.size === file.size);
                        if (duplicado) return;
                        if (!MIMES_ACEITOS.includes(file.type)) {
                            alert(`"${file.name}" não é um formato aceito (PDF, JPG, PNG, WEBP).`); return;
                        }
                        if (file.size > 10 * 1024 * 1024) {
                            alert(`"${file.name}" excede 10 MB.`); return;
                        }
                        dt.items.add(file);
                    });
                    callback();
                }

                function renderizarLista(ulEl, dt, onRemover) {
                    ulEl.innerHTML = '';
                    Array.from(dt.files).forEach((file, i) => {
                        const isPdf   = file.type === 'application/pdf';
                        const tamanho = file.size >= 1024 * 1024
                            ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
                            : Math.round(file.size / 1024) + ' KB';
                        const pid = `prev-${ulEl.id}-${i}`;

                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2';
                        li.innerHTML = `
                            <div id="${pid}" class="w-9 h-9 flex-shrink-0 rounded overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-lg">
                                ${isPdf ? '📄' : ''}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">${file.name}</p>
                                <p class="text-xs text-gray-400">${tamanho}</p>
                            </div>
                            <button type="button" class="text-red-500 hover:text-red-700 text-xs font-medium flex-shrink-0">Remover</button>`;

                        li.querySelector('button').addEventListener('click', () => onRemover(i));
                        ulEl.appendChild(li);

                        if (!isPdf) {
                            const reader = new FileReader();
                            reader.onload = e => {
                                const el = document.getElementById(pid);
                                if (el) el.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // ─── Helpers ─────────────────────────────────────────────────────────────
                function enforceMaxDesconto(input) {
                    const max = parseFloat(input.max);
                    if (!isNaN(max) && parseFloat(input.value) > max) input.value = max.toFixed(2);
                    calcularValores();
                }

                function toggleNotaFiscal(checkbox) {
                    document.getElementById('camposNotaFiscal').classList.toggle('hidden', !checkbox.checked);
                }

                // ─── Init ────────────────────────────────────────────────────────────────
                document.addEventListener('DOMContentLoaded', function () {
                    // Inicializa DataTransfer para formas já renderizadas pelo PHP
                    document.querySelectorAll('.forma-card').forEach(card => {
                        dtFormas[parseInt(card.dataset.index)] = new DataTransfer();
                    });

                    @if(old('formas_pagamento'))
                        document.querySelectorAll('.select-condicao').forEach(onCondicaoChange);
                    @endif

                    atualizarAlertaGlobal();
                    atualizarDesconto();
                    calcularValores();
                });
            </script>
        </div>
    </div>
</x-layouts.app>