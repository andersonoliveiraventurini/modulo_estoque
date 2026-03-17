<div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
    <flux:heading size="lg" class="mb-4">Saldos e Lucratividade Estimada</flux:heading>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Card Custo --}}
        <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800">
            <flux:text size="sm" class="text-neutral-500 mb-1">Custo Total em Estoque</flux:text>
            <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                R$ {{ number_format($totalCusto, 2, ',', '.') }}
            </div>
            <flux:text size="xs" class="text-neutral-400 mt-1">Patrimônio imobilizado</flux:text>
        </div>

        {{-- Card Venda --}}
        <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800">
            <flux:text size="sm" class="text-neutral-500 mb-1">Previsão de Venda Total</flux:text>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                R$ {{ number_format($totalVenda, 2, ',', '.') }}
            </div>
            <flux:text size="xs" class="text-neutral-400 mt-1">Estimativa de faturamento</flux:text>
        </div>

        {{-- Card Margem --}}
        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-800/50">
            <flux:text size="sm" class="text-green-700 dark:text-green-400 mb-1">Margem Bruta Potencial</flux:text>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-green-700 dark:text-green-400">
                    R$ {{ number_format($lucroPotencial, 2, ',', '.') }}
                </span>
                <flux:badge color="lime" variant="solid" size="sm">
                    {{ number_format($markup, 1) }}%
                </flux:badge>
            </div>
            <flux:text size="xs" class="text-green-600/70 dark:text-green-500/50 mt-1">Markup médio global</flux:text>
        </div>
    </div>
</div>
