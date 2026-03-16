<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
                {{ __('Histórico de Faturas: ') }} {{ $cliente->nome }}
            </h2>
            <flux:button variant="primary" href="{{ route('faturamento.index') }}">Voltar</flux:button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Cards de Informação -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-info-card label="Total Pago" value="R$ {{ number_format($stats['total_pago'], 2, ',', '.') }}" class="text-green-600 dark:text-green-400 font-semibold" />
                <x-info-card label="Total Pendente" value="R$ {{ number_format($stats['total_pendente'], 2, ',', '.') }}" class="text-yellow-600 dark:text-yellow-400 font-semibold" />
                <x-info-card label="Total Vencido" value="R$ {{ number_format($stats['total_vencido'], 2, ',', '.') }}" class="text-red-600 dark:text-red-400 font-bold" />
            </div>

            <!-- Tabela de Histórico -->
            <div class="bg-white border text-sm rounded-2xl dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Origem</th>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Fatura #</th>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Valor Total</th>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Vencimento</th>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Pagamento</th>
                            <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-neutral-700 dark:text-neutral-300">
                        @forelse ($faturas as $fatura)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                <td class="px-6 py-4">
                                    @if($fatura->orcamento_id)
                                        Orc. #{{ $fatura->orcamento_id }}
                                    @elseif($fatura->pedido_id)
                                        Ped. #{{ $fatura->pedido_id }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">FAT-{{ str_pad($fatura->id, 5, '0', STR_PAD_LEFT) }} ({{ $fatura->numero_parcela }}/{{ $fatura->total_parcelas }})</td>
                                <td class="px-6 py-4 font-medium">R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 {{ $fatura->isAtrasada() ? 'text-red-600 font-bold dark:text-red-400' : '' }}">
                                    {{ $fatura->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $fatura->data_pagamento ? $fatura->data_pagamento->format('d/m/Y') : '-' }} <br/>
                                    <span class="text-xs text-neutral-500">R$ {{ number_format($fatura->valor_pago, 2, ',', '.') }}</span>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                    Nenhuma fatura encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-layouts.app>
