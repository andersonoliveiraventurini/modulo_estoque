<div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
        </svg>
        Documentos de Faturamento da Rota
    </h3>

    {{-- Formulário de Upload --}}
    @can('route_billing_attach', $orcamento)
        <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-600">
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipo de Documento</label>
                        <select wire:model="file_type" class="w-full text-sm border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="payment_proof">Comprovante de Pagamento (PIX/Depósito)</option>
                            <option value="bilhete_unico">Bilhete Único / Vale Transporte</option>
                            <option value="other">Outros</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Arquivos</label>
                        <input type="file" wire:model="files" multiple class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('files.*') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        @error('files') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Observações (opcional)</label>
                    <textarea wire:model="notes" rows="2" class="w-full text-sm border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 rounded-lg placeholder-zinc-400 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Detalhes sobre o comprovante..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                        <span wire:loading.remove>Anexar Documentos</span>
                        <span wire:loading>Enviando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Listagem Visual --}}
    <div class="space-y-3">
        @forelse($orcamento->routeBillingAttachments->sortByDesc('created_at') as $att)
            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white dark:bg-zinc-700 rounded shadow-sm border border-zinc-100 dark:border-zinc-600">
                        <svg class="w-5 h-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $att->file_type === 'payment_proof' ? 'Comprovante' : ($att->file_type === 'bilhete_unico' ? 'Bilhete' : 'Outro') }}
                            <span class="text-xs text-zinc-400 ml-1">#{{ $att->id }}</span>
                        </p>
                        <p class="text-[11px] text-zinc-500">
                            Por {{ $att->user->name }} em {{ $att->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($att->is_valid === true)
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700 rounded-full">Validado</span>
                    @elseif($att->is_valid === false)
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 rounded-full">Recusado</span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-yellow-100 text-yellow-700 rounded-full">Pendente</span>
                    @endif

                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="p-1.5 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-lg transition" title="Ver Arquivo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-8 border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-xl">
                <p class="text-sm text-zinc-500">Nenhum documento anexado ainda.</p>
            </div>
        @endforelse
    </div>
</div>
