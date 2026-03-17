<div>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Análise e Histórico de Descontos</h3>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white ring-1 ring-black ring-opacity-5 dark:border-neutral-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Data / Origem</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Valor / Porcentagem</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">Observações / Motivo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse($descontos as $desconto)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-300">
                            {{ $desconto->created_at->format('d/m/Y H:i') }}
                            <br>
                            <span class="text-xs text-neutral-500">Por: {{ $desconto->user->name ?? 'Sistema' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                            @if($desconto->tipo === 'percentual')
                                {{ number_format($desconto->porcentagem, 2, ',', '.') }}%
                            @else
                                R$ {{ number_format($desconto->valor, 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @if($desconto->status === 'aprovado')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Aprovado
                                </span>
                                <br><span class="text-xs text-neutral-500">em {{ \Carbon\Carbon::parse($desconto->aprovado_em)->format('d/m/y') }}</span>
                                <br><span class="text-xs text-neutral-500">por {{ $desconto->aprovadoPor->name ?? '-' }}</span>
                            @elseif($desconto->status === 'rejeitado')
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Rejeitado
                                </span>
                                <br><span class="text-xs text-neutral-500">em {{ \Carbon\Carbon::parse($desconto->rejeitado_em)->format('d/m/y') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Pendente
                                </span>
                            @endif
                        </td>
                        <td class="max-w-xs px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                            <strong>Motivo:</strong> {{ $desconto->motivo }}
                            @if($desconto->observacao)
                                <br>
                                <strong>Obs:</strong> {{ $desconto->observacao }}
                            @endif
                            @if($desconto->status === 'aprovado' && $desconto->justificativa_aprovacao)
                                <br>
                                <strong class="text-green-600">Justificativa Aprovação:</strong> {{ $desconto->justificativa_aprovacao }}
                            @endif
                            @if($desconto->status === 'rejeitado' && $desconto->justificativa_rejeicao)
                                <br>
                                <strong class="text-red-600">Justificativa Rejeição:</strong> {{ $desconto->justificativa_rejeicao }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-neutral-500">Nenhum histórico de desconto ou análise encontrado para este cliente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($descontos->hasPages())
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $descontos->links() }}
            </div>
        @endif
    </div>
</div>
