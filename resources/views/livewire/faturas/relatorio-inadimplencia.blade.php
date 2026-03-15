<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Visão Geral de Inadimplência</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Totalizadores e Maiores Devedores</p>
        </div>
        <flux:button variant="primary" href="{{ route('dashboard') }}">Voltar</flux:button>
    </div>

    <!-- Cards Superiores -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="p-6 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-2xl flex flex-col justify-center">
            <span class="text-red-600 dark:text-red-400 font-semibold mb-1">Total Recebíveis em Atraso</span>
            <span class="flex items-center gap-2">
                <flux:icon.banknotes class="text-red-600 dark:text-red-400" />
                <span class="text-3xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totalVencido, 2, ',', '.') }}</span>
            </span>
            <span class="text-sm text-red-500 dark:text-red-500 mt-2">Corresponde a {{ $totalAtrasados }} fatura(s) vencida(s) no momento.</span>
        </div>
    </div>

    <!-- Lista de Inadimplentes (Por Cliente) -->
    <div class="bg-white border text-sm rounded-2xl dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 font-bold text-neutral-800 dark:text-neutral-200 bg-zinc-50 dark:bg-zinc-800">
            Top Clientes Inadimplentes
        </div>
        <table class="w-full text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400">Cliente</th>
                    <th class="px-6 py-3 font-medium text-center text-neutral-500 dark:text-neutral-400">Qtd. Faturas em Atraso</th>
                    <th class="px-6 py-3 font-medium text-right text-neutral-500 dark:text-neutral-400">Total Devido</th>
                    <th class="px-6 py-3 font-medium text-center text-neutral-500 dark:text-neutral-400">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-neutral-700 dark:text-neutral-300">
                @forelse($porCliente as $c)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <td class="px-6 py-4 font-semibold">{{ $c->cliente->nome }}</td>
                    <td class="px-6 py-4 text-center">{{ $c->faturas_count }}</td>
                    <td class="px-6 py-4 text-right text-red-600 font-bold">R$ {{ number_format($c->devendo, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center">
                        <flux:button size="sm" variant="ghost" href="{{ route('faturamento.cliente', $c->cliente_id) }}" icon="arrow-right">Ver Extrato</flux:button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                        Nenhum cliente em inadimplência! Parabéns.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
