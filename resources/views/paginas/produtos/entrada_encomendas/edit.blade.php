<x-layouts.app :title="__('Complementar Entrada #' . $entradaEncomenda->id)">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-blue-600" />
                Complementar Entrada #{{ $entradaEncomenda->id }}
                <span class="text-sm font-normal text-zinc-400 ml-2">
                    (Cotação #{{ $entradaEncomenda->grupo_id }} — {{ $entradaEncomenda->grupo->cliente->nome_fantasia ?? '?' }})
                </span>
            </h2>

            <form action="{{ route('entrada_encomendas.update', $entradaEncomenda->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Entrega --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Entregue para</label>
                        <select name="entregue_para"
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Não entregue ainda</option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('entregue_para', $entradaEncomenda->entregue_para) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data de Entrega</label>
                        <input type="date" name="data_entrega"
                               value="{{ old('data_entrega', $entradaEncomenda->data_entrega?->format('Y-m-d')) }}"
                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    </div>
                </div>

                {{-- Itens --}}
                <div class="space-y-3 mb-6">
                    <h3 class="font-semibold text-zinc-700 dark:text-zinc-300 text-sm uppercase tracking-wider">Itens</h3>

                    @foreach ($entradaEncomenda->itens as $itemEntrada)
                        <div class="border {{ $itemEntrada->recebido_completo ? 'border-emerald-200 dark:border-emerald-700 opacity-60' : 'border-amber-200 dark:border-amber-700' }} rounded-xl p-4">
                            <input type="hidden" name="itens[{{ $loop->index }}][id]" value="{{ $itemEntrada->id }}">

                            <div class="flex items-center justify-between mb-2">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                                    {{ $itemEntrada->consultaPreco->descricao }}
                                </p>
                                @if ($itemEntrada->recebido_completo)
                                    <span class="text-xs text-emerald-600 font-medium">✓ Completo</span>
                                @else
                                    <span class="text-xs text-amber-600 font-medium">
                                        Pendente: {{ number_format($itemEntrada->quantidadePendente(), 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">Qtd Solicitada</label>
                                    <input type="number" readonly value="{{ $itemEntrada->quantidade_solicitada }}"
                                           class="w-full border border-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm bg-zinc-50 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">Qtd Recebida <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0"
                                           name="itens[{{ $loop->index }}][quantidade_recebida]"
                                           value="{{ old("itens.{$loop->index}.quantidade_recebida", $itemEntrada->quantidade_recebida) }}"
                                           required
                                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">Observação</label>
                                    <input type="text"
                                           name="itens[{{ $loop->index }}][observacao]"
                                           value="{{ old("itens.{$loop->index}.observacao", $itemEntrada->observacao) }}"
                                           placeholder="Item faltante, avariado..."
                                           class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Observação geral --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação Geral</label>
                    <textarea name="observacao" rows="2"
                              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old('observacao', $entradaEncomenda->observacao) }}</textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit" variant="primary">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Salvar Atualização
                    </x-button>
                    <a href="{{ route('entrada_encomendas.show', $entradaEncomenda->id) }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>