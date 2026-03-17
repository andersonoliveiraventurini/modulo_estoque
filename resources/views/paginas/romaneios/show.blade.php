<x-layouts.app title="Gerenciar Romaneio">
    <div class="space-y-6">

        {{-- Cabeçalho --}}
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('romaneios.index') }}" variant="ghost" icon="chevron-left" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 truncate">{{ $romaneio->descricao }}</h1>
                    @php
                        $statusConfig = match($romaneio->status) {
                            'aberto'      => ['label' => 'Aberto',      'dot' => 'bg-amber-400',   'text' => 'text-amber-700 dark:text-amber-400',   'bg' => 'bg-amber-50 dark:bg-amber-900/20',    'border' => 'border-amber-200 dark:border-amber-800/50'],
                            'em_transito' => ['label' => 'Em Trânsito', 'dot' => 'bg-blue-400',    'text' => 'text-blue-700 dark:text-blue-400',     'bg' => 'bg-blue-50 dark:bg-blue-900/20',      'border' => 'border-blue-200 dark:border-blue-800/50'],
                            'concluido'   => ['label' => 'Concluído',   'dot' => 'bg-emerald-400', 'text' => 'text-emerald-700 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-800/50'],
                            'cancelado'   => ['label' => 'Cancelado',   'dot' => 'bg-red-400',     'text' => 'text-red-700 dark:text-red-400',       'bg' => 'bg-red-50 dark:bg-red-900/20',        'border' => 'border-red-200 dark:border-red-800/50'],
                            default       => ['label' => $romaneio->status, 'dot' => 'bg-zinc-400','text' => 'text-zinc-600 dark:text-zinc-400',  'bg' => 'bg-zinc-50 dark:bg-zinc-800',         'border' => 'border-zinc-200 dark:border-zinc-700'],
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} border {{ $statusConfig['border'] }} shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ $romaneio->motorista ?: 'Sem motorista' }}
                    @if($romaneio->veiculo) · {{ $romaneio->veiculo }} @endif
                    @if($romaneio->data_entrega) · Entrega: {{ $romaneio->data_entrega->format('d/m/Y') }} @endif
                </p>
            </div>

            <div class="flex gap-2 shrink-0">
                <flux:button href="{{ route('romaneios.pdf', $romaneio) }}" variant="ghost" icon="printer" size="sm">
                    Imprimir
                </flux:button>
                <form action="{{ route('romaneios.update_status', $romaneio) }}" method="POST" class="flex items-center">
                    @csrf
                    <flux:select name="status" onchange="this.form.submit()" size="sm">
                        <flux:select.option value="aberto" :selected="$romaneio->status == 'aberto'">Aberto</flux:select.option>
                        <flux:select.option value="em_transito" :selected="$romaneio->status == 'em_transito'">Em Trânsito</flux:select.option>
                        <flux:select.option value="concluido" :selected="$romaneio->status == 'concluido'">Concluído</flux:select.option>
                        <flux:select.option value="cancelado" :selected="$romaneio->status == 'cancelado'">Cancelado</flux:select.option>
                    </flux:select>
                </form>
            </div>
        </div>

        {{-- Corpo principal: 2 colunas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Coluna principal: pedidos no romaneio --}}
            <div class="lg:col-span-2 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        Pedidos no Romaneio
                        <span class="ml-1.5 px-2 py-0.5 bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-full text-sm font-medium">{{ $romaneio->batches->count() }}</span>
                    </h2>
                </div>

                @forelse ($romaneio->batches as $batch)
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        {{-- Cabeçalho do card --}}
                        <div class="p-4 bg-zinc-50/80 dark:bg-zinc-700/30 border-b border-zinc-100 dark:border-zinc-700 flex justify-between items-start">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Lote #{{ $batch->id }}</span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-600 text-zinc-600 dark:text-zinc-300 rounded text-[10px] font-medium">
                                        Orç. #{{ $batch->orcamento?->id }}
                                    </span>
                                    @foreach($batch->orcamento?->transportes as $transp)
                                        <span class="inline-flex items-center px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-[10px] font-medium border border-indigo-100 dark:border-indigo-800/50">
                                            {{ $transp->nome }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                    {{ $batch->orcamento?->cliente?->nome }}
                                </div>
                                @php
                                    $endereco = $batch->orcamento?->cliente?->enderecos?->where('tipo', 'entrega')->first()
                                             ?? $batch->orcamento?->cliente?->enderecos?->where('tipo', 'comercial')->first();
                                @endphp
                                @if($endereco)
                                    <div class="flex items-start gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        <span>{{ $endereco->logradouro }}, {{ $endereco->numero }} - {{ $endereco->bairro }}, {{ $endereco->cidade }}/{{ $endereco->uf }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-2 ml-3 shrink-0">
                                <form action="{{ route('romaneios.remove_batch', [$romaneio, $batch]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="xs" icon="trash" class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" />
                                </form>
                                @if($batch->finished_at)
                                    <span class="text-[10px] text-neutral-400">{{ $batch->finished_at->format('d/m H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Itens do lote --}}
                        <div class="px-4 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">Itens Separados</div>
                            <div class="space-y-1.5">
                                @foreach($batch->items as $item)
                                    <div class="flex justify-between items-center text-sm py-1 border-b border-zinc-50 dark:border-zinc-700/50 last:border-0">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                            <span class="text-neutral-700 dark:text-neutral-300">{{ $item->produto?->nome }}</span>
                                        </div>
                                        <span class="font-mono text-xs font-semibold text-neutral-900 dark:text-white bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded">
                                            {{ number_format($item->quantidade, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Rodapé do card --}}
                        <div class="px-4 py-2.5 bg-zinc-50/50 dark:bg-zinc-700/20 border-t border-zinc-100 dark:border-zinc-700 flex gap-4 text-xs text-neutral-500 dark:text-neutral-400">
                            @if($batch->qtd_caixas)
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                                    {{ $batch->qtd_caixas }} cx
                                </span>
                            @endif
                            @if($batch->qtd_sacos)
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
                                    {{ $batch->qtd_sacos }} sc
                                </span>
                            @endif
                            @if($batch->qtd_sacolas)
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006-3.75 4.5M5.25 14.15v4.25" /></svg>
                                    {{ $batch->qtd_sacolas }} sl
                                </span>
                            @endif
                            @if($batch->outros_embalagem)
                                <span>{{ $batch->outros_embalagem }}</span>
                            @endif
                            <span class="ml-auto text-[10px]">Sep. por: {{ $batch->criadoPor?->name ?? '—' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Nenhum pedido adicionado</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Use a lista ao lado para adicionar pedidos pendentes de entrega.</p>
                    </div>
                @endforelse
            </div>

            {{-- Coluna lateral: pedidos disponíveis --}}
            <div class="space-y-3 sticky top-6">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Pedidos Pendentes</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Prontos para carga. Retiradas/Balcão não inclusos.</p>
                </div>

                <div class="flex items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 rounded-xl px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <p class="text-[10px] text-amber-800 dark:text-amber-300 leading-snug">
                        Pedidos do tipo <strong>Retirada</strong> ou <strong>Balcão</strong> são ocultados automaticamente.
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <form action="{{ route('romaneios.add_batches', $romaneio) }}" method="POST">
                        @csrf
                        <div class="max-h-[560px] overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-700/50">
                            @forelse ($availableBatches as $batch)
                                <label class="flex items-start gap-3 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 cursor-pointer transition-colors">
                                    <input type="checkbox" name="batch_ids[]" value="{{ $batch->id }}"
                                        class="mt-1 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-2">
                                            <span class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400">Lote #{{ $batch->id }}</span>
                                            <span class="text-[10px] text-zinc-400 dark:text-zinc-500 shrink-0">{{ $batch->finished_at?->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate mt-0.5">
                                            {{ $batch->orcamento?->cliente?->nome }}
                                        </div>
                                        @php
                                            $end = $batch->orcamento?->cliente?->enderecos?->where('tipo', 'entrega')->first()
                                                ?? $batch->orcamento?->cliente?->enderecos?->where('tipo', 'comercial')->first();
                                        @endphp
                                        <div class="flex justify-between items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">{{ $end?->cidade }}{{ $end?->bairro ? ' — '.$end->bairro : '' }}</span>
                                            <div class="flex gap-1 shrink-0">
                                                @foreach($batch->orcamento?->transportes as $transp)
                                                    <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 rounded text-[9px] font-medium">{{ $transp->nome }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="p-10 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Tudo adicionado!</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Sem pedidos pendentes no momento.</p>
                                </div>
                            @endforelse
                        </div>

                        @if($availableBatches->isNotEmpty())
                            <div class="p-3 bg-zinc-50/80 dark:bg-zinc-700/30 border-t border-zinc-100 dark:border-zinc-700">
                                <flux:button type="submit" variant="primary" class="w-full" icon="plus">
                                    Adicionar Selecionados
                                </flux:button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
