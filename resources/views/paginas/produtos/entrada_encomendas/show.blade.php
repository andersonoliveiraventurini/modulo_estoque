<x-layouts.app :title="__('Entrada #' . $entradaEncomenda->id)">
    <div class="flex flex-col gap-6">

        {{-- ── Cabeçalho ──────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-6 shadow">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">Entrada de Encomenda</p>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">#{{ $entradaEncomenda->id }}</h2>
                    <p class="text-sm text-zinc-500 mt-1">
                        Cotação: <a href="{{ route('consulta_preco.show_grupo', $entradaEncomenda->grupo_id) }}"
                                    class="font-medium text-blue-600 hover:underline">#{{ $entradaEncomenda->grupo_id }}</a>
                        &nbsp;·&nbsp;
                        Cliente: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->cliente->nome_fantasia ?? '—' }}</span>
                    </p>
                    <p class="text-sm text-zinc-500">
                        Recebido por: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->recebedor->name }}</span>
                        em <span class="font-medium">{{ $entradaEncomenda->data_recebimento->format('d/m/Y') }}</span>
                    </p>
                    @if ($entradaEncomenda->destinatario)
                        <p class="text-sm text-zinc-500">
                            Entregue para: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $entradaEncomenda->destinatario->name }}</span>
                            @if ($entradaEncomenda->data_entrega)
                                em <span class="font-medium">{{ $entradaEncomenda->data_entrega->format('d/m/Y') }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-3">
                    @php
                        $statusMap = [
                            'Recebido parcialmente' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                            'Recebido completo'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                            'Entregue'              => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusMap[$entradaEncomenda->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                        {{ $entradaEncomenda->status }}
                    </span>

                    @if ($entradaEncomenda->status === 'Recebido parcialmente')
                        <a href="{{ route('entrada_encomendas.complementar', $entradaEncomenda->id) }}">
                            <x-button size="sm" variant="primary">
                                <x-heroicon-o-inbox-arrow-down class="w-4 h-4" />
                                Registrar Entrada Complementar
                            </x-button>
                        </a>
                    @endif
                </div>
            </div>

            @if ($entradaEncomenda->observacao)
                <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg text-sm text-zinc-600 dark:text-zinc-400">
                    <strong>Observação:</strong> {{ $entradaEncomenda->observacao }}
                </div>
            @endif
        </div>

        {{-- ── Itens ───────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Itens desta Entrada</h3>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($entradaEncomenda->itens as $item)
                    @php
                        $pendente   = $item->quantidadePendente();
                        $temProduto = $item->ncm || $item->codigo_barras || $item->sku
                                   || $item->unidade_medida || $item->peso
                                   || $item->categoria_id || $item->sub_categoria_id;
                    @endphp

                    <div class="px-6 py-5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">

                        {{-- Nome + status --}}
                        <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $item->consultaPreco->descricao }}
                                </span>
                                @if ($item->consultaPreco->cor)
                                    <span class="text-zinc-400 text-xs"> · {{ $item->consultaPreco->cor->nome }}</span>
                                @endif
                                @if ($item->consultaPreco->part_number)
                                    <span class="text-zinc-400 text-xs"> · PN: {{ $item->consultaPreco->part_number }}</span>
                                @endif
                            </div>

                            @if ($item->recebido_completo)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <x-heroicon-o-check-circle class="w-3 h-3" /> Completo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    <x-heroicon-o-clock class="w-3 h-3" /> Parcial
                                </span>
                            @endif
                        </div>

                        {{-- Quantidades + peso --}}
                        <div class="flex flex-wrap gap-6 text-sm mb-3">
                            <div>
                                <span class="text-xs text-zinc-400 block">Qtd Solicitada</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($item->quantidade_solicitada, 0, ',', '.') }}
                                    @if ($item->unidade_medida) <span class="text-xs text-zinc-400">{{ $item->unidade_medida }}</span> @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-400 block">Qtd Recebida</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ number_format($item->quantidade_recebida, 0, ',', '.') }}
                                    @if ($item->unidade_medida) <span class="text-xs text-zinc-400">{{ $item->unidade_medida }}</span> @endif
                                </span>
                            </div>
                            @if ($pendente > 0)
                                <div>
                                    <span class="text-xs text-zinc-400 block">Pendente</span>
                                    <span class="font-semibold text-amber-600">
                                        {{ number_format($pendente, 0, ',', '.') }}
                                        @if ($item->unidade_medida) <span class="text-xs">{{ $item->unidade_medida }}</span> @endif
                                    </span>
                                </div>
                            @endif
                            @if ($item->peso)
                                <div>
                                    <span class="text-xs text-zinc-400 block">Peso</span>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ number_format($item->peso, 3, ',', '.') }} kg</span>
                                </div>
                            @endif
                        </div>

                        {{-- Observação do recebimento --}}
                        @if ($item->observacao)
                            <p class="text-xs italic text-zinc-500 dark:text-zinc-400 mb-2">
                                📝 Observação: {{ $item->observacao }}
                            </p>
                        @endif

                        {{-- Descrição do Produto --}}
                        @if ($item->descricao)
                            <div class="mb-3 p-3 bg-blue-50/50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-800/30">
                                <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">
                                    Descrição do Produto
                                </p>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                    {{ $item->descricao }}
                                </p>
                            </div>
                        @endif

                        {{-- Informações do produto --}}
                        @if ($temProduto)
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-700">
                                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">
                                    Informações do Produto
                                </p>
                                <div class="flex flex-wrap gap-x-8 gap-y-3">

                                    @if ($item->ncm)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">NCM</span>
                                            <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-zinc-700 dark:text-zinc-300">
                                                {{ $item->ncm }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($item->codigo_barras)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">Código de Barras</span>
                                            <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-zinc-700 dark:text-zinc-300">
                                                {{ $item->codigo_barras }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($item->sku)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">SKU / Código</span>
                                            <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-zinc-700 dark:text-zinc-300">
                                                {{ $item->sku }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($item->unidade_medida)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">Unidade de Medida</span>
                                            <span class="text-xs text-zinc-700 dark:text-zinc-300 font-medium">
                                                {{ $item->unidade_medida }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($item->categoria)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">Categoria</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ $item->categoria->nome }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($item->subCategoria)
                                        <div>
                                            <span class="text-xs text-zinc-400 block mb-0.5">Subcategoria</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                                                {{ $item->subCategoria->nome }}
                                            </span>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @else
                            <p class="text-xs text-zinc-400 italic">Nenhuma informação de produto registrada.</p>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Rodapé ──────────────────────────────────────────────────────── --}}
        <div class="flex gap-3">
            <a href="{{ route('entrada_encomendas.aprovadas') }}">
                <x-button size="sm" variant="primary">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Voltar ao Painel
                </x-button>
            </a>
            <a href="{{ route('entrada_encomendas.edit', $entradaEncomenda->id) }}">
                <x-button size="sm" variant="warning">
                    <x-heroicon-o-pencil-square class="w-4 h-4" /> Editar Recebimento
                </x-button>
            </a>
            <a href="{{ route('entrada_encomendas.index') }}">
                <x-button size="sm" variant="orange">Ver todas as entradas</x-button>
            </a>
        </div>

    </div>
</x-layouts.app>
