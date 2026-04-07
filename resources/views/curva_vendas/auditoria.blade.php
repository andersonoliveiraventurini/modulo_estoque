<x-layouts.app :title="__('Histórico de Curva de Vendas')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Histórico de Alterações Manuais') }}</flux:heading>
            <flux:button icon="arrow-left" variant="outline" href="{{ route('curva_vendas.index') }}">
                {{ __('Voltar') }}
            </flux:button>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b dark:border-neutral-700">
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Data') }}</th>
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Usuário') }}</th>
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Produto') }}</th>
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('De') }}</th>
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Para') }}</th>
                            <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Justificativa') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $audit)
                            <tr class="border-b dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-900/50 transition-colors">
                                <td class="py-3 px-4 text-sm">{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 text-sm">{{ $audit->user->name }}</td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="font-medium text-neutral-900 dark:text-white">{{ $audit->produto->nome }}</div>
                                    <div class="text-xs text-neutral-500">SKU: {{ $audit->produto->sku }}</div>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <flux:badge color="zinc" variant="outline">{{ $audit->de ?? 'N/A' }}</flux:badge>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <flux:badge color="indigo" variant="solid">{{ $audit->para }}</flux:badge>
                                </td>
                                <td class="py-3 px-4 text-sm italic text-neutral-600 dark:text-neutral-400">
                                    "{{ $audit->justificativa }}"
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-neutral-500">
                                    {{ __('Nenhuma alteração manual registrada.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $auditorias->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
