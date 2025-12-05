<x-layouts.app :title="__('Realizar pagamento')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-6xl mx-auto">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-lg border border-zinc-200 dark:border-zinc-700 p-6">

                        <!-- Header -->
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Pagamento no Balcão - Orçamento #{{ $orcamento->id }}
                            </h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Finalize o pagamento do orçamento selecionado
                            </p>
                        </div>

                        <!-- Alertas de Erro -->
                        @if ($errors->any())
                            <div
                                class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Atenção aos
                                            seguintes erros:</h4>
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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Informações do
                                Orçamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $orcamento->cliente->nome ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Vendedor</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $orcamento->vendedor->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Condição Original</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $orcamento->condicaoPagamento->nome ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Valor Total</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulário -->
                        <form action="{{ route('orcamentos.pagamento.salvar', $orcamento->id) }}" method="POST"
                            id="formPagamento">
                            @csrf

                            <!-- Formas de Pagamento -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Formas de Pagamento
                                    </h3>
                                    <button type="button" onclick="adicionarFormaPagamento()"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        + Adicionar Forma
                                    </button>
                                </div>

                                <div id="formasPagamentoContainer" class="space-y-3">
                                    @php
                                        $formasOld = old('formas_pagamento', [['metodo_id' => '', 'valor' => '']]);
                                    @endphp

                                    @foreach ($formasOld as $index => $formaOld)
                                        <!-- Forma de pagamento #{{ $index + 1 }} -->
                                        <div class="forma-pagamento bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma
                                                    #{{ $index + 1 }}</span>
                                                @if ($index > 0)
                                                    <button type="button" onclick="removerFormaPagamento(this)"
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                                        Remover
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        Método de Pagamento *
                                                    </label>
                                                    <select name="formas_pagamento[{{ $index }}][metodo_id]"
                                                        required
                                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="">Selecione...</option>
                                                        @foreach ($metodosPagamento as $metodo)
                                                            <option value="{{ $metodo->id }}"
                                                                {{ old("formas_pagamento.{$index}.metodo_id") == $metodo->id ? 'selected' : '' }}>
                                                                {{ $metodo->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error("formas_pagamento.{$index}.metodo_id")
                                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                            {{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        Valor *
                                                    </label>
                                                    <input type="number"
                                                        name="formas_pagamento[{{ $index }}][valor]"
                                                        step="0.01" min="0" required
                                                        value="{{ old("formas_pagamento.{$index}.valor", '') }}"
                                                        onchange="calcularValores()"
                                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        placeholder="0,00">
                                                    @error("formas_pagamento.{$index}.valor")
                                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                            {{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Resumo de Valores -->
                                <div
                                    class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Valor Total
                                            </p>
                                            <p class="text-xl font-bold text-blue-700 dark:text-blue-300"
                                                id="valorTotal">
                                                R$
                                                {{ number_format($orcamento->valor_total_itens - ($orcamento->desconto ?? 0), 2, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Valor Pago
                                            </p>
                                            <p class="text-xl font-bold text-green-700 dark:text-green-300"
                                                id="valorPago">R$ 0,00</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-900 dark:text-blue-200 font-medium">Troco</p>
                                            <p class="text-xl font-bold text-orange-700 dark:text-orange-300"
                                                id="valorTroco">R$ 0,00</p>
                                        </div>
                                    </div>
                                    <div id="alertaFaltando"
                                        class="mt-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-md p-3 hidden">
                                        <p class="text-sm text-red-800 dark:text-red-200 font-medium">
                                            ⚠️ Falta pagar: <span id="valorFaltando" class="font-bold">R$ 0,00</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- Desconto no Balcão -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Desconto no Balcão
                                    (até 3%)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Valor do Desconto
                                        </label>
                                        <input type="number" name="desconto_balcao" step="0.01" min="0"
                                            max="{{ $orcamento->valor_total_itens * 0.03 }}"
                                            onchange="calcularValores()"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0,00">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Máximo: R$
                                            {{ number_format($orcamento->valor_total_itens * 0.03, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- Documento Fiscal -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Documento Fiscal
                                </h3>
                                <div class="space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="precisa_nota_fiscal" value="1"
                                            {{ old('precisa_nota_fiscal') ? 'checked' : '' }}
                                            onchange="toggleNotaFiscal(this)"
                                            class="w-4 h-4 text-blue-600 bg-white dark:bg-zinc-800 border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Precisa de
                                            Nota Fiscal?</span>
                                    </label>

                                    <div id="camposNotaFiscal"
                                        class="{{ old('precisa_nota_fiscal') ? '' : 'hidden' }} pl-7">
                                        <div class="max-w-md">
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                CNPJ/CPF para a Nota
                                            </label>
                                            <input type="text" name="cnpj_cpf_nota"
                                                value="{{ old('cnpj_cpf_nota', '') }}"
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Digite o CNPJ ou CPF">
                                            @error('cnpj_cpf_nota')
                                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200 dark:border-gray-700">

                            <!-- Observações -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Observações
                                </label>
                                <textarea name="observacoes" rows="3"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Informações adicionais sobre o pagamento...">{{ old('observacoes', '') }}</textarea>
                                @error('observacoes')
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ações -->
                            <div class="flex gap-4">
                                <a href="{{ route('orcamentos.index') }}"
                                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-3 rounded-lg transition-colors">
                                    Cancelar
                                </a>
                                <button type="submit" id="btnFinalizar"
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg transition-colors">
                                    Finalizar Pagamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                let contadorFormas = {{ count(old('formas_pagamento', [['metodo_id' => '', 'valor' => '']])) }};
                const valorTotalOrcamento = {{ $orcamento->valor_total_itens - ($orcamento->desconto ?? 0) }};

                // Adicionar nova forma de pagamento
                function adicionarFormaPagamento() {
                    const container = document.getElementById('formasPagamentoContainer');
                    const novaForma = `
        <div class="forma-pagamento bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Forma #${contadorFormas + 1}</span>
                <button type="button" onclick="removerFormaPagamento(this)" 
                        class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                    Remover
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Método de Pagamento *
                    </label>
                    <select name="formas_pagamento[${contadorFormas}][metodo_id]" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        @foreach ($metodosPagamento as $metodo)
                            <option value="{{ $metodo->id }}">{{ $metodo->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Valor *
                    </label>
                    <input type="number" name="formas_pagamento[${contadorFormas}][valor]" step="0.01" min="0" required
                           onchange="calcularValores()"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0,00">
                </div>
            </div>
        </div>
    `;
                    container.insertAdjacentHTML('beforeend', novaForma);
                    contadorFormas++;
                }

                // Remover forma de pagamento
                function removerFormaPagamento(btn) {
                    const container = document.getElementById('formasPagamentoContainer');
                    const formas = container.querySelectorAll('.forma-pagamento');

                    // Não permitir remover se for a única forma
                    if (formas.length <= 1) {
                        return;
                    }

                    btn.closest('.forma-pagamento').remove();
                    calcularValores();
                }

                // Calcular valores
                function calcularValores() {
                    const descontoBalcao = parseFloat(document.querySelector('[name="desconto_balcao"]')?.value || 0);
                    const valorFinal = valorTotalOrcamento - descontoBalcao;

                    let valorPago = 0;
                    document.querySelectorAll('[name^="formas_pagamento"][name$="[valor]"]').forEach(input => {
                        valorPago += parseFloat(input.value || 0);
                    });

                    const troco = Math.max(0, valorPago - valorFinal);
                    const faltando = Math.max(0, valorFinal - valorPago);

                    // Atualiza displays
                    document.getElementById('valorTotal').textContent = 'R$ ' + valorFinal.toFixed(2).replace('.', ',');
                    document.getElementById('valorPago').textContent = 'R$ ' + valorPago.toFixed(2).replace('.', ',');
                    document.getElementById('valorTroco').textContent = 'R$ ' + troco.toFixed(2).replace('.', ',');

                    // Alerta de valor faltando
                    const alertaFaltando = document.getElementById('alertaFaltando');
                    const btnFinalizar = document.getElementById('btnFinalizar');

                    if (faltando > 0.01) {
                        alertaFaltando.classList.remove('hidden');
                        document.getElementById('valorFaltando').textContent = 'R$ ' + faltando.toFixed(2).replace('.', ',');
                        btnFinalizar.disabled = true;
                        btnFinalizar.classList.remove('bg-green-600', 'hover:bg-green-700');
                        btnFinalizar.classList.add('bg-gray-400', 'cursor-not-allowed');
                    } else {
                        alertaFaltando.classList.add('hidden');
                        btnFinalizar.disabled = false;
                        btnFinalizar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        btnFinalizar.classList.add('bg-green-600', 'hover:bg-green-700');
                    }
                }

                // Toggle nota fiscal
                function toggleNotaFiscal(checkbox) {
                    document.getElementById('camposNotaFiscal').classList.toggle('hidden', !checkbox.checked);
                }

                // Calcular valores ao carregar a página
                document.addEventListener('DOMContentLoaded', function() {
                    calcularValores();
                });
            </script>
        </div>
    </div>
</x-layouts.app>
