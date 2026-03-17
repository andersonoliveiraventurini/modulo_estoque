<x-layouts.app title="Romaneios (Entregas)">
    <div class="space-y-6">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Romaneios de Entrega</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Gerencie as cargas e rotas de entrega dos pedidos concluídos.</p>
            </div>
            <flux:button href="{{ route('romaneios.create') }}" variant="primary" icon="plus">
                Novo Romaneio
            </flux:button>
        </div>

        {{-- Banner informativo --}}
        <div class="relative overflow-hidden rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/10 border border-indigo-100/60 dark:border-indigo-900/30 p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Controle de Frota e Entregas</h4>
                    <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                        Esta tela exibe apenas orçamentos destinados a <strong>Entrega Própria ou Transportadora</strong>.
                        Pedidos do tipo <em>Retirada</em> ou <em>Balcão</em> não aparecem aqui.
                    </p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-[0.07]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4">
            <form method="GET" action="{{ route('romaneios.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-40">
                    <flux:select name="status" label="Status">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="aberto" :selected="request('status') == 'aberto'">Aberto</flux:select.option>
                        <flux:select.option value="em_transito" :selected="request('status') == 'em_transito'">Em Trânsito</flux:select.option>
                        <flux:select.option value="concluido" :selected="request('status') == 'concluido'">Concluído</flux:select.option>
                        <flux:select.option value="cancelado" :selected="request('status') == 'cancelado'">Cancelado</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex-1 min-w-40">
                    <flux:input type="date" name="data_entrega" label="Data de Entrega" value="{{ request('data_entrega') }}" />
                </div>
                <div class="flex gap-2 pb-0.5">
                    <flux:button type="submit" variant="primary" icon="magnifying-glass">Filtrar</flux:button>
                    <flux:button href="{{ route('romaneios.index') }}" variant="ghost">Limpar</flux:button>
                </div>
            </form>
        </div>

        {{-- Lista de Romaneios em cards --}}
        @forelse ($romaneios as $romaneio)
            @php
                $statusConfig = match($romaneio->status) {
                    'aberto'      => ['label' => 'Aberto',      'dot' => 'bg-amber-400',  'text' => 'text-amber-700 dark:text-amber-400',  'bg' => 'bg-amber-50 dark:bg-amber-900/20',   'border' => 'border-amber-200 dark:border-amber-800/50'],
                    'em_transito' => ['label' => 'Em Trânsito', 'dot' => 'bg-blue-400',   'text' => 'text-blue-700 dark:text-blue-400',    'bg' => 'bg-blue-50 dark:bg-blue-900/20',     'border' => 'border-blue-200 dark:border-blue-800/50'],
                    'concluido'   => ['label' => 'Concluído',   'dot' => 'bg-emerald-400','text' => 'text-emerald-700 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-900/20','border' => 'border-emerald-200 dark:border-emerald-800/50'],
                    'cancelado'   => ['label' => 'Cancelado',   'dot' => 'bg-red-400',    'text' => 'text-red-700 dark:text-red-400',      'bg' => 'bg-red-50 dark:bg-red-900/20',       'border' => 'border-red-200 dark:border-red-800/50'],
                    default       => ['label' => $romaneio->status, 'dot' => 'bg-zinc-400', 'text' => 'text-zinc-600 dark:text-zinc-400', 'bg' => 'bg-zinc-50 dark:bg-zinc-800', 'border' => 'border-zinc-200 dark:border-zinc-700'],
                };
            @endphp

            <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-zinc-300 dark:hover:border-zinc-600 transition-colors">
                <div class="flex items-center gap-4 p-4">

                    {{-- Ícone / Status visual --}}
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center {{ $statusConfig['bg'] }} {{ $statusConfig['border'] }} border">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ $statusConfig['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                    </div>

                    {{-- Informações principais --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <a href="{{ route('romaneios.show', $romaneio) }}" class="text-base font-semibold text-neutral-900 dark:text-neutral-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                                {{ $romaneio->descricao }}
                            </a>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} border {{ $statusConfig['border'] }} shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                {{ $romaneio->motorista ?: 'Sem motorista' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25" /></svg>
                                {{ $romaneio->veiculo ?: 'Sem veículo' }}
                            </span>
                            @if($romaneio->data_entrega)
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" /></svg>
                                    Entrega: {{ $romaneio->data_entrega->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Qtd pedidos --}}
                    <div class="hidden sm:flex flex-col items-center px-4 border-l border-zinc-100 dark:border-zinc-700">
                        <span class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $romaneio->batches_count }}</span>
                        <span class="text-[10px] font-medium text-neutral-400 uppercase tracking-wide">Pedidos</span>
                    </div>

                    {{-- Ações --}}
                    <div class="flex items-center gap-2 pl-2">
                        <flux:button href="{{ route('romaneios.show', $romaneio) }}" variant="ghost" size="sm" icon="eye">
                            Gerenciar
                        </flux:button>
                    </div>
                </div>
            </div>

        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-2xl">
                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Nenhum romaneio encontrado</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1 mb-6">Crie um novo romaneio para organizar as entregas.</p>
                <flux:button href="{{ route('romaneios.create') }}" variant="primary" icon="plus">Novo Romaneio</flux:button>
            </div>
        @endforelse

        @if($romaneios->hasPages())
            <div class="mt-2">
                {{ $romaneios->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
