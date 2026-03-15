<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Faturas e Inadimplência</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Gerencia recebimentos e débitos dos clientes</p>
        </div>
        <flux:button variant="primary" href="{{ route('dashboard') }}">Voltar</flux:button>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4">
            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">A Receber</p>
            <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                R$ {{ number_format($stats['total_a_receber'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-neutral-400 mt-1">{{ $stats['count_pendentes'] }} fatura(s) pendentes</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-900/50 rounded-2xl p-4">
            <p class="text-xs font-medium text-red-500 uppercase tracking-wide">Vencido</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                R$ {{ number_format($stats['total_vencido'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-neutral-400 mt-1">Inadimplência acumulada</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-green-200 dark:border-green-900/50 rounded-2xl p-4">
            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Pago no Mês</p>
            <p class="text-2xl font-bold text-green-700 dark:text-green-400 mt-1">
                R$ {{ number_format($stats['total_pago_mes'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-neutral-400 mt-1">{{ now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 flex flex-col justify-between">
            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Ações Rápidas</p>
            <div class="mt-2">
                <a href="{{ route('faturamento.inadimplencia') }}" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Ver Inadimplência
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="p-6 space-y-4 bg-white border rounded-2xl dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700">
        <flux:heading>Filtros de Busca</flux:heading>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <flux:input wire:model.live.debounce.500ms="search" label="Cliente (Nome ou CPF/CNPJ)" placeholder="Buscar..." />
            
            <flux:select wire:model.live="status" label="Status">
                <flux:select.option value="">Todos</flux:select.option>
                <flux:select.option value="pendente">Pendente</flux:select.option>
                <flux:select.option value="parcial">Parcial</flux:select.option>
                <flux:select.option value="pago">Pago</flux:select.option>
                <flux:select.option value="vencido">Vencido</flux:select.option>
                <flux:select.option value="cancelado">Cancelado</flux:select.option>
            </flux:select>

            <flux:input type="date" wire:model.live="dataInicio" label="Vencimento Inicial" />
            <flux:input type="date" wire:model.live="dataFim" label="Vencimento Final" />
        </div>
    </div>

    <!-- Tabela -->
    <div class="bg-white border text-sm rounded-2xl dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Fatura #</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Cliente</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Orçamento</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Vencimento</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Valor Total</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Valor Pago</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Saldo Devedor</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Status</th>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-neutral-700 dark:text-neutral-300">
                @forelse ($faturas as $fatura)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <td class="px-6 py-4">
                            FAT-{{ str_pad($fatura->id, 5, '0', STR_PAD_LEFT) }}
                            <div class="text-xs text-neutral-500">
                                Parc. {{ $fatura->numero_parcela }}/{{ $fatura->total_parcelas }}
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium">
                            {{ $fatura->cliente->nome }}
                        </td>
                        <td class="px-6 py-4">
                            @if ($fatura->orcamento_id)
                                <a href="{{ route('orcamentos.show', $fatura->orcamento_id) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                    Orç. #{{ $fatura->orcamento_id }}
                                </a>
                            @else
                                <span class="text-neutral-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="{{ $fatura->isAtrasada() ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                {{ $fatura->data_vencimento->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}</td>
                        <td class="px-6 py-4">R$ {{ number_format($fatura->valor_pago, 2, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @php $saldo = max(0, $fatura->valor_total - $fatura->valor_pago); @endphp
                            <span class="{{ $saldo > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-green-600 dark:text-green-400' }}">
                                R$ {{ number_format($saldo, 2, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pendente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'parcial' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    'pago' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'vencido' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    'cancelado' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                                ];
                                $color = $statusColors[$fatura->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                {{ ucfirst($fatura->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 flex items-center gap-2 border-none">
                            @if(in_array($fatura->status, ['pendente', 'parcial', 'vencido']))
                                <flux:button size="sm" variant="primary" icon="currency-dollar" wire:click="$dispatchTo('faturas.baixar-fatura-modal', 'abrir-baixa-fatura', { faturaId: {{ $fatura->id }} })">Baixar</flux:button>
                            @endif
                            @if ($fatura->orcamento_id)
                                <a href="{{ route('orcamentos.show', $fatura->orcamento_id) }}">
                                    <flux:button size="sm" variant="ghost" icon="document-text" />
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            Nenhuma fatura encontrada com os filtros atuais.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700">
            {{ $faturas->links() }}
        </div>
    </div>

    <!-- Modais -->
    <livewire:faturas.baixar-fatura-modal />
</div>
