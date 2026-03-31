<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-zinc-100">Logs de Movimentação de Estoque</h1>
        <p class="text-sm text-gray-500 dark:text-zinc-400">Rastreabilidade completa de todas as alterações físicas no estoque.</p>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Buscar Produto</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nome ou SKU..." class="w-full rounded-lg border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo</label>
                <select wire:model.live="tipo_movimentacao" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm">
                    <option value="">Todos</option>
                    <option value="entry">Entrada (NF)</option>
                    <option value="sale_output">Saída (Venda)</option>
                    <option value="replenishment">Reposição HUB</option>
                    <option value="stock_transfer">Transferência</option>
                    <option value="manual_adjustment">Ajuste Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Colaborador</label>
                <select wire:model.live="colaborador_id" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm">
                    <option value="">Todos</option>
                    @foreach($colaboradores as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Início</label>
                <input wire:model.live="data_inicio" type="date" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fim</label>
                <input wire:model.live="data_fim" type="date" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
            <thead class="bg-gray-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Data/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produto</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qtd</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Localização</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Responsável</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Obs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-zinc-400">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">{{ $log->produto->nome }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $log->produto->sku }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = match($log->tipo_movimentacao) {
                                    'entry' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'sale_output' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                    'replenishment' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                    'stock_transfer' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-400'
                                };
                                $tipoNome = match($log->tipo_movimentacao) {
                                    'entry' => 'ENTRADA',
                                    'sale_output' => 'SAÍDA',
                                    'replenishment' => 'REPOSIÇÃO',
                                    'stock_transfer' => 'TRANSFERÊNCIA',
                                    'manual_adjustment' => 'AJUSTE',
                                    default => strtoupper($log->tipo_movimentacao)
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                                {{ $tipoNome }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono text-sm {{ $log->quantidade > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $log->quantidade > 0 ? '+' : '' }}{{ number_format($log->quantidade, 3, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600 dark:text-zinc-400">
                            {{ $log->posicao ? $log->posicao->nome_completo : 'HUB (Geral)' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-gray-700 dark:text-zinc-300">
                            {{ $log->colaborador->name ?? 'Sistema' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500 italic max-w-xs truncate" title="{{ $log->observacao }}">
                            {{ $log->observacao }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">Nenhum log encontrado para os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $logs->links() }}
        </div>
    </div>
</div>
