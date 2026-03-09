<x-layouts.app :title="__('Registrar Entrada de Encomenda')">
    <div class="flex flex-col gap-6">

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">

            <h2 class="text-xl font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-blue-600" />
                Registrar Entrada de Encomenda
            </h2>

            <form action="{{ route('entrada_encomendas.store') }}" method="POST" id="form-entrada">
                @csrf

                {{-- ── Seleção do grupo ──────────────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Cotação / Grupo <span class="text-red-500">*</span>
                        </label>
                        <select name="grupo_id" id="grupo_id" required
                                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                                onchange="carregarItens(this.value)">
                            <option value="">Selecione a cotação...</option>
                            @foreach ($gruposDisponiveis as $g)
                                <option value="{{ $g->id }}"
                                    {{ ($grupo && $grupo->id == $g->id) ? 'selected' : '' }}>
                                    #{{ $g->id }} — {{ $g->cliente->nome_fantasia ?? $g->cliente->nome ?? '?' }}
                                    ({{ $g->itens->count() }} iten(s))
                                </option>
                            @endforeach
                        </select>
                        @error('grupo_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

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
                            Entregue para (usuário/vendedor)
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

                {{-- ── Itens do grupo ────────────────────────────── --}}
                <div id="itens-container" class="mb-6">
                    @if ($grupo)
                        @foreach ($grupo->itens as $item)
                            @php
                                $forn = $item->fornecedorSelecionado;
                            @endphp
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 mb-3">
                                <input type="hidden" name="itens[{{ $loop->index }}][consulta_preco_id]" value="{{ $item->id }}">

                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                                        {{ $item->descricao }}
                                        @if ($item->cor) <span class="text-zinc-400 font-normal">· {{ $item->cor->nome }}</span> @endif
                                        @if ($item->part_number) <span class="text-zinc-400 font-normal">· PN: {{ $item->part_number }}</span> @endif
                                    </p>
                                    @if ($forn)
                                        <span class="text-xs text-zinc-500">
                                            Fornecedor: <strong>{{ $forn->fornecedor->nome_fantasia }}</strong>
                                            @if ($forn->prazo_entrega) · Prazo: {{ $forn->prazo_entrega }} @endif
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Qtd Solicitada</label>
                                        <input type="number" step="0.01"
                                               name="itens[{{ $loop->index }}][quantidade_solicitada]"
                                               value="{{ old("itens.{$loop->index}.quantidade_solicitada", $item->quantidade) }}"
                                               readonly
                                               class="w-full border border-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 rounded-lg px-3 py-2 text-sm bg-zinc-50 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">
                                            Qtd Recebida <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" step="0.01" min="0"
                                               name="itens[{{ $loop->index }}][quantidade_recebida]"
                                               value="{{ old("itens.{$loop->index}.quantidade_recebida", $item->quantidade) }}"
                                               required
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-500 mb-1">Observação do item</label>
                                        <input type="text"
                                               name="itens[{{ $loop->index }}][observacao]"
                                               value="{{ old("itens.{$loop->index}.observacao") }}"
                                               placeholder="Ex: item faltante, avariado..."
                                               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-6 text-center text-zinc-400 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-xl">
                            Selecione uma cotação acima para carregar os itens.
                        </div>
                    @endif
                </div>

                {{-- ── Observação geral ──────────────────────────── --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observação Geral</label>
                    <textarea name="observacao" rows="2" placeholder="Informações adicionais sobre o recebimento..."
                              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old('observacao') }}</textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-button type="submit" variant="primary">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Registrar Entrada
                    </x-button>
                    <a href="{{ route('entrada_encomendas.aprovadas') }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Recarrega página com o grupo_id selecionado para carregar os itens via Blade
        function carregarItens(grupoId) {
            if (!grupoId) return;
            const url = new URL(window.location.href);
            url.searchParams.set('grupo_id', grupoId);
            window.location.href = url.toString();
        }
    </script>
</x-layouts.app>