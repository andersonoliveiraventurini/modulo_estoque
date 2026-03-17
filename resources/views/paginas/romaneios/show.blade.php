<x-layouts.app title="Gerenciar Romaneio">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <x-button href="{{ route('romaneios.index') }}" variant="ghost" icon="chevron-left" />
                <div>
                    <x-heading size="xl">{{ $romaneio->descricao }}</x-heading>
                    <x-subheading>
                        {{ $romaneio->motorista ?: 'Sem motorista' }} | {{ $romaneio->veiculo ?: 'Sem veículo' }} | 
                        Entregar em: {{ $romaneio->data_entrega?->format('d/m/Y') }}
                    </x-subheading>
                </div>
            </div>

            <div class="flex gap-2">
                <x-button href="{{ route('romaneios.pdf', $romaneio) }}" variant="secondary" icon="printer">
                    Imprimir Romaneio
                </x-button>
                
                <form action="{{ route('romaneios.update_status', $romaneio) }}" method="POST">
                    @csrf
                    <x-select name="status" onchange="this.form.submit()">
                        <option value="aberto" {{ $romaneio->status == 'aberto' ? 'selected' : '' }}>Aberto</option>
                        <option value="em_transito" {{ $romaneio->status == 'em_transito' ? 'selected' : '' }}>Em Trânsito</option>
                        <option value="concluido" {{ $romaneio->status == 'concluido' ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelado" {{ $romaneio->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </x-select>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Lista de Pedidos no Romaneio -->
            <div class="lg:col-span-2 space-y-4">
                <x-heading size="lg">Pedidos no Romaneio ({{ $romaneio->batches->count() }})</x-heading>
                
                @forelse ($romaneio->batches as $batch)
                    <x-card class="p-0 relative overflow-hidden ring-1 ring-zinc-200 dark:ring-zinc-700 shadow-sm">
                        <div class="p-4 bg-zinc-50/50 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-700 flex justify-between items-start">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-zinc-500">LOTE #{{ $batch->id }}</span>
                                    <x-badge variant="neutral" class="text-[10px]">Orç. #{{ $batch->orcamento?->id }}</x-badge>
                                    @foreach($batch->orcamento?->transportes as $transp)
                                        <x-badge variant="primary" class="text-[10px]">{{ $transp->nome }}</x-badge>
                                    @endforeach
                                </div>
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $batch->orcamento?->cliente?->nome }}
                                </div>
                                @php
                                    $endereco = $batch->orcamento?->cliente?->enderecos?->where('tipo', 'entrega')->first() 
                                             ?? $batch->orcamento?->cliente?->enderecos?->where('tipo', 'comercial')->first();
                                @endphp
                                <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-start gap-1">
                                    <x-heroicon-o-map-pin class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                                    <span>
                                        {{ $endereco ? "{$endereco->logradouro}, {$endereco->numero} - {$endereco->bairro}, {$endereco->cidade}/{$endereco->uf}" : 'Sem endereço' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <form action="{{ route('romaneios.remove_batch', [$romaneio, $batch]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm" class="text-red-500 hover:text-red-600" icon="trash" />
                                </form>
                                <span class="text-[10px] text-zinc-400 font-medium">Concluído em {{ $batch->finished_at?->format('d/m H:i') }}</span>
                            </div>
                        </div>
                        
                        <!-- Lista de Itens do Lote -->
                        <div class="px-4 py-3 bg-white dark:bg-zinc-900">
                            <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Itens Separados</div>
                            <div class="space-y-2">
                                @foreach($batch->items as $item)
                                    <div class="flex justify-between items-center text-sm border-b border-zinc-50 dark:border-zinc-800 pb-1 last:border-0">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span class="text-zinc-700 dark:text-zinc-300">{{ $item->produto?->nome }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="font-mono text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-800 px-1.5 rounded">{{ number_format($item->quantidade, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-3 bg-zinc-50/30 dark:bg-zinc-800/30 border-t border-zinc-100 dark:border-zinc-700 flex gap-4 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                            <span class="flex items-center gap-1"><x-heroicon-o-archive-box class="w-3 h-3" /> {{ $batch->qtd_caixas }} cx</span>
                            <span class="flex items-center gap-1"><x-heroicon-o-shopping-bag class="w-3 h-3" /> {{ $batch->qtd_sacos }} sc</span>
                            <span class="flex items-center gap-1"><x-heroicon-o-briefcase class="w-3 h-3" /> {{ $batch->qtd_sacolas }} sl</span>
                            @if($batch->outros_embalagem)
                                <span class="flex items-center gap-1"><x-heroicon-o-plus-circle class="w-3 h-3" /> {{ $batch->outros_embalagem }}</span>
                            @endif
                            <div class="ml-auto text-zinc-400">Sep. por: {{ $batch->criadoPor?->name ?? '—' }}</div>
                        </div>
                    </x-card>
                @empty
                    <div class="bg-zinc-50 border-2 border-dashed border-zinc-200 rounded-xl p-12 text-center text-zinc-500">
                        <x-heroicon-o-truck class="w-12 h-12 mx-auto mb-4 text-zinc-300" />
                        <p>Nenhum pedido adicionado a este romaneio ainda.</p>
                        <p class="text-sm">Use a lista ao lado para adicionar pedidos pendentes de entrega.</p>
                    </div>
                @endforelse
            </div>

            <!-- Coluna de Pedidos Disponíveis -->
            <div class="space-y-4">
                <div>
                    <x-heading size="lg">Pedidos Pendentes</x-heading>
                    <x-subheading>Prontos para carga. Retiradas/Balcão não inclusos.</x-subheading>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg p-3">
                    <p class="text-[10px] text-amber-800 dark:text-amber-400 leading-tight">
                        <strong>Nota:</strong> Esta lista oculta automaticamente pedidos do tipo <em>Retirada</em> ou <em>Balcão</em>.
                    </p>
                </div>

                <x-card class="p-0 overflow-hidden shadow-sm ring-1 ring-zinc-200 dark:ring-zinc-700">
                    <form action="{{ route('romaneios.add_batches', $romaneio) }}" method="POST">
                        @csrf
                        <div class="max-h-[600px] overflow-y-auto divide-y divide-zinc-100">
                            @forelse ($availableBatches as $batch)
                                <div class="p-3 hover:bg-zinc-50 flex items-start gap-3">
                                    <input type="checkbox" name="batch_ids[]" value="{{ $batch->id }}" class="mt-1 rounded border-zinc-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <span class="text-xs font-bold text-zinc-500">#{{ $batch->id }}</span>
                                            <span class="text-[10px] text-zinc-400">{{ $batch->finished_at?->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-sm font-bold text-zinc-900 truncate">
                                            {{ $batch->orcamento?->cliente?->nome }}
                                        </div>
                                        @php
                                            $end = $batch->orcamento?->cliente?->enderecos?->where('tipo', 'entrega')->first() 
                                                ?? $batch->orcamento?->cliente?->enderecos?->where('tipo', 'comercial')->first();
                                        @endphp
                                        <div class="text-[10px] text-zinc-500 flex justify-between items-center">
                                            <span class="truncate">{{ $end?->cidade }} - {{ $end?->bairro }}</span>
                                            <div class="flex gap-1 shrink-0">
                                                @foreach($batch->orcamento?->transportes as $transp)
                                                    <span class="bg-indigo-50 text-indigo-600 px-1 rounded-sm">{{ $transp->nome }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-sm text-zinc-500 italic">
                                    Sem pedidos pendentes no momento.
                                </div>
                            @endforelse
                        </div>

                        @if($availableBatches->isNotEmpty())
                            <div class="p-3 bg-zinc-50 border-t border-zinc-100">
                                <x-button type="submit" variant="primary" class="w-full" icon="plus">
                                    Adicionar Selecionados
                                </x-button>
                            </div>
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
