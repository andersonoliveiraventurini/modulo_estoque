<x-layouts.app :title="__('Nova Entrada Complementar — Cotação #' . $entradaEncomenda->grupo_id)">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">

            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-semibold flex items-center gap-2">
                    <x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-blue-600" />
                    Nova Entrada Complementar
                </h2>
                <a href="{{ route('entrada_encomendas.show', $entradaEncomenda->id) }}">
                    <x-button size="sm" variant="secondary">
                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Voltar
                    </x-button>
                </a>
            </div>

            <p class="text-sm text-zinc-500 mb-6">
                Cotação <span class="font-medium text-zinc-700 dark:text-zinc-300">#{{ $entradaEncomenda->grupo_id }}</span>
                — Cliente: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->grupo->cliente->nome_fantasia ?? '—' }}</span>
                &nbsp;·&nbsp; Complementando a Entrada <span class="font-medium">#{{ $entradaEncomenda->id }}</span>
            </p>

            {{-- Resumo do que já foi recebido --}}
            <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">
                    Situação atual dos itens (todas as entradas anteriores)
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="text-xs text-zinc-400 border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left pb-2 font-medium">Item</th>
                            <th class="text-right pb-2 font-medium">Pedido</th>
                            <th class="text-right pb-2 font-medium">Já recebido</th>
                            <th class="text-right pb-2 font-medium text-amber-600">Pendente</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($itensPendentes as $resumo)
                            <tr>
                                <td class="py-2 text-zinc-700 dark:text-zinc-300">
                                    {{ $resumo['item']->descricao }}
                                    @if ($resumo['item']->cor)
                                        <span class="text-zinc-400 text-xs"> · {{ $resumo['item']->cor->nome }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right text-zinc-500">{{ number_format($resumo['solicitado'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-emerald-600 font-medium">{{ number_format($resumo['recebido'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-semibold {{ $resumo['pendente'] > 0 ? 'text-amber-600' : 'text-zinc-300' }}">
                                    {{ $resumo['pendente'] > 0 ? number_format($resumo['pendente'], 0, ',', '.') : '✓' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Formulário cria NOVA entrada --}}
            <form action="{{ route('entrada_encomendas.store') }}" method="POST">
                @csrf

                {{-- grupo_id vem do grupo da entrada original --}}
                <input type="hidden" name="grupo_id" value="{{ $entradaEncomenda->grupo_id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Recebimento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data_recebimento" required
                               value="{{ old('data_recebimento', now()->format('Y-m-d')) }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Recebido por
                        </label>
                        <select name="recebido_por"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    {{ auth()->id() == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Entregue para (vendedor)
                        </label>
                        <select name="entregue_para"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Não entregue ainda</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}" {{ old('entregue_para') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data de Entrega ao vendedor
                        </label>
                        <input type="date" name="data_entrega"
                               value="{{ old('data_entrega') }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>
                </div>

                {{-- Itens — todos os itens do grupo, com pendência destacada --}}
                <div class="space-y-3 mb-6">
                    <h3 class="font-semibold text-zinc-700 dark:text-zinc-300 text-sm">
                        Quantidades recebidas nesta entrada
                    </h3>

                    @foreach ($itensPendentes as $idx => $resumo)
                        <input type="hidden" name="itens[{{ $idx }}][consulta_preco_id]" value="{{ $resumo['item']->id }}">
                        <input type="hidden" name="itens[{{ $idx }}][quantidade_solicitada]" value="{{ $resumo['solicitado'] }}">

                        <div class="border {{ $resumo['pendente'] > 0 ? 'border-amber-200 dark:border-amber-700' : 'border-emerald-200 dark:border-emerald-700 opacity-60' }} rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                                    {{ $resumo['item']->descricao }}
                                    @if ($resumo['item']->cor)
                                        <span class="text-zinc-400 font-normal"> · {{ $resumo['item']->cor->nome }}</span>
                                    @endif
                                    @if ($resumo['item']->part_number)
                                        <span class="text-zinc-400 font-normal"> · PN: {{ $resumo['item']->part_number }}</span>
                                    @endif
                                </p>
                                @if ($resumo['pendente'] <= 0)
                                    <span class="text-xs text-emerald-600 font-medium">✓ Já completo</span>
                                @else
                                    <span class="text-xs text-amber-600 font-medium">
                                        Pendente: {{ number_format($resumo['pendente'], 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">
                                        Qtd Recebida Agora
                                        @if ($resumo['pendente'] > 0)<span class="text-red-500">*</span>@endif
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                           name="itens[{{ $idx }}][quantidade_recebida]"
                                           value="{{ old("itens.{$idx}.quantidade_recebida", $resumo['pendente'] > 0 ? $resumo['pendente'] : 0) }}"
                                           {{ $resumo['pendente'] <= 0 ? 'readonly' : 'required' }}
                                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none {{ $resumo['pendente'] <= 0 ? 'bg-zinc-50 dark:bg-zinc-700 cursor-not-allowed' : '' }}">
                                    @if ($resumo['pendente'] > 0)
                                        <p class="text-xs text-zinc-400 mt-1">Pendente: {{ number_format($resumo['pendente'], 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">Observação</label>
                                    <input type="text"
                                           name="itens[{{ $idx }}][observacao]"
                                           value="{{ old("itens.{$idx}.observacao") }}"
                                           placeholder="Ex: item chegou com avaria, faltou 1 unidade..."
                                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação Geral</label>
                    <textarea name="observacao" rows="2"
                              placeholder="Informações adicionais sobre este recebimento..."
                              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old('observacao') }}</textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit" variant="primary">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Registrar Esta Entrada
                    </x-button>
                    <a href="{{ route('entrada_encomendas.show', $entradaEncomenda->id) }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>