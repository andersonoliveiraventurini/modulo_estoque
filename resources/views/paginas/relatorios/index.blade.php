<x-layouts.app :title="'Relatórios de Estoque'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Relatórios de Estoque</flux:heading>
            <flux:subheading>Acesse informações detalhadas e métricas do seu estoque.</flux:subheading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {{-- Estoque Crítico --}}
            <a href="{{ route('relatorios.estoque_critico') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-red-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 group-hover:bg-red-200 dark:group-hover:bg-red-900/50 transition-colors">
                        <flux:icon.exclamation-triangle class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-red-600 transition-colors">Estoque Crítico</flux:heading>
                </div>
                <flux:text size="sm">Produtos com saldo abaixo do estoque mínimo. Reposição urgente.</flux:text>
            </a>

            {{-- Data de Vencimento --}}
            <a href="{{ route('relatorios.vencimento_produtos') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-indigo-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
                        <flux:icon.calendar class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-indigo-600 transition-colors">Vencimento</flux:heading>
                </div>
                <flux:text size="sm">Monitore a validade dos seus produtos por lote e evite perdas.</flux:text>
            </a>

            {{-- Reposição de Estoque --}}
            <a href="{{ route('relatorios.reposicao_estoque') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-indigo-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
                        <flux:icon.arrow-path class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-indigo-600 transition-colors">Reposição</flux:heading>
                </div>
                <flux:text size="sm">Histórico de rotinas de reposição e movimentações internas.</flux:text>
            </a>

            {{-- Recebimento de Produtos --}}
            <a href="{{ route('relatorios.recebimento_produtos') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-indigo-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
                        <flux:icon.arrow-down-tray class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-indigo-600 transition-colors">Recebimento</flux:heading>
                </div>
                <flux:text size="sm">Todos os produtos recebidos de fornecedores com detalhes de NF.</flux:text>
            </a>

            {{-- Devoluções --}}
            <a href="{{ route('relatorios.devolucoes') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-orange-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors">
                        <flux:icon.arrow-uturn-left class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-orange-600 transition-colors">Devoluções</flux:heading>
                </div>
                <flux:text size="sm">Gestão e relatório de produtos devolvidos ao estoque.</flux:text>
            </a>

            {{-- Não Conformidade --}}
            <a href="{{ route('relatorios.nao_conformidade') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-yellow-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-900/50 transition-colors">
                        <flux:icon.shield-exclamation class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-yellow-600 transition-colors">Não Conformidade</flux:heading>
                </div>
                <flux:text size="sm">Divergências encontradas durante a conferência de recebimento.</flux:text>
            </a>

            {{-- Saída de Produtos --}}
            <a href="{{ route('relatorios.saida_produtos') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-indigo-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition-colors">
                        <flux:icon.arrow-up-tray class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-indigo-600 transition-colors">Saída de Produtos</flux:heading>
                </div>
                <flux:text size="sm">Relatório detalhado de todas as saídas de estoque.</flux:text>
            </a>

            {{-- Vendas e Margem --}}
            <a href="{{ route('relatorios.vendas_margem') }}" class="group block p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-emerald-500 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition-colors">
                        <flux:icon.chart-bar-square class="size-6" />
                    </div>
                    <flux:heading size="lg" class="group-hover:text-emerald-600 transition-colors">Vendas e Margem</flux:heading>
                </div>
                <flux:text size="sm">Desempenho de vendas por produto, descontos e margens.</flux:text>
            </a>
        </div>
    </div>
</x-layouts.app>
