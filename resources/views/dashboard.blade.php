<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        {{-- Cards de Métricas --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <a href="{{ route('clientes.index') }}" class="block p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-accent transition-colors">
                <flux:heading size="sm" class="mb-2">Clientes</flux:heading>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $stats['clientes_count'] }}</span>
                    <flux:badge color="lime">Ativos</flux:badge>
                </div>
            </a>

            <a href="{{ route('produtos.index') }}" class="block p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-accent transition-colors">
                <flux:heading size="sm" class="mb-2">Total de Produtos</flux:heading>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $stats['produtos_count'] }}</span>
                    <flux:badge color="zinc">SKUs</flux:badge>
                </div>
            </a>

            <a href="{{ route('relatorios.estoque_critico') }}" class="block p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm ring-2 ring-red-500/20 hover:border-red-500 transition-colors">
                <flux:heading size="sm" class="mb-2 text-red-600 dark:text-red-400">Estoque Crítico</flux:heading>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['estoque_critico'] }}</span>
                    <flux:badge color="red" variant="solid">Atenção</flux:badge>
                </div>
            </a>

            <a href="{{ route('requisicao_compras.index') }}" class="block p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm hover:border-accent transition-colors">
                <flux:heading size="sm" class="mb-2">Req. Compras</flux:heading>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-bold text-neutral-900 dark:text-white">{{ $stats['requisicoes_pendentes'] }}</span>
                    <flux:badge color="orange">Pendentes</flux:badge>
                </div>
            </a>
        </div>

        {{-- Nova Seção: Saldos e Lucratividade --}}
        <livewire:dashboard.saldos-lucratividade />

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Lista de Alertas de Estoque --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">Alertas de Estoque Mínimo</flux:heading>
                    <flux:button variant="ghost" size="sm" href="{{ route('relatorios.estoque_critico') }}">Ver todos</flux:button>
                </div>
                
                @if($alertas->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 text-neutral-500">
                        <flux:icon.check-circle class="size-12 mb-2 text-lime-500 opacity-50" />
                        <p>Nenhum alerta crítico no momento.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($alertas as $produto)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600">
                                        <flux:icon.exclamation-triangle class="size-5" />
                                    </div>
                                    <div>
                                        <flux:text class="font-medium text-neutral-900 dark:text-white">{{ $produto->nome }}</flux:text>
                                        <flux:text size="xs" class="text-neutral-500">SKU: {{ $produto->sku }} | Cor: {{ $produto->cor?->nome ?? '-' }}</flux:text>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-red-600">{{ $produto->estoque_atual }} / {{ $produto->estoque_minimo }}</div>
                                    <flux:text size="xs" class="text-neutral-500 uppercase tracking-wider">Saldo / Mín</flux:text>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Estoque por Endereço --}}
            <livewire:dashboard.estoque-por-endereco />
        </div>

        {{-- Vendas e Atividade --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Desempenho de Vendas (Mensal)</flux:heading>
                <div class="flex gap-2">
                    <flux:button variant="filled" color="indigo" size="sm" href="{{ route('orcamentos.index') }}">Orçamentos</flux:button>
                    <flux:button variant="outline" size="sm" href="{{ route('vendas.index') }}">Histórico</flux:button>
                </div>
            </div>
            <div class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-neutral-200 dark:border-neutral-700 rounded-lg">
                <span class="text-4xl font-bold text-neutral-900 dark:text-white">{{ $stats['vendas_mensal'] }}</span>
                <flux:text>Vendas concluídas este mês</flux:text>
            </div>
        </div>
    </div>
</x-layouts.app>
