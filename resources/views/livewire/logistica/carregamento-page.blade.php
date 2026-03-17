<div class="flex flex-col gap-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Carregamento de Rota</h1>
            <p class="text-sm text-zinc-500">Pedidos com aprovação financeira prontos para expedição.</p>
        </div>
        
        <div class="flex items-center gap-2">
            {{-- Botão de imprimir PDF do dia pode ir aqui futuramente --}}
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-wrap gap-4 items-end">
        <div class="w-full md:w-64">
            <x-label for="f_search">Buscar Pedido/Cliente</x-label>
            <x-input id="f_search" wire:model.live.debounce.300ms="search" placeholder="Ex: 1234 ou Nome do Cliente..." />
        </div>

        <div class="w-full md:w-48">
            <x-label for="f_day">Dia de Carregamento</x-label>
            <x-select id="f_day" wire:model.live="loadingDay" class="w-full">
                <option value="">Todos os dias</option>
                <option value="monday">Segunda-feira</option>
                <option value="tuesday">Terça-feira</option>
                <option value="wednesday">Quarta-feira</option>
                <option value="thursday">Quinta-feira</option>
                <option value="friday">Sexta-feira</option>
                <option value="express">Express</option>
                <option value="sedex">Sedex</option>
                <option value="carrier">Transportadora</option>
            </x-select>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500">Dia</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500">Orc #</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500">Cliente</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500">Transporte</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-zinc-500 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($orcamentos as $orc)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors text-sm">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg text-[10px] font-bold uppercase tracking-tight">
                                    {{ $orc->loading_day_formatted }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">#{{ $orc->id }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $orc->cliente->nome ?? $orc->cliente->nome_fantasia }}</div>
                                <div class="text-[10px] text-zinc-500">{{ $orc->vendedor->name ?? 'Sem vendedor' }}</div>
                            </td>
                            <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                {{ $orc->transportes->first()->nome ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('orcamentos.gerenciar', $orc->id) }}" class="p-2 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition inline-block text-zinc-500" title="Ver Detalhes">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p>Nenhum pedido de rota aprovado encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orcamentos->hasPages())
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                {{ $orcamentos->links() }}
            </div>
        @endif
    </div>
</div>
