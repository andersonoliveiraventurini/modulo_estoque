<div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Aprovação de Faturamento (Financeiro)
    </h3>

    @can('route_billing_approve')
        <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Decisão</label>
                    <x-select wire:model="status" class="w-full">
                        <option value="approved">Aprovar Faturamento</option>
                        <option value="restrictions">Aprovar com Restrições (Ex: Pendência)</option>
                        <option value="rejected">Negar / Cancelar Faturamento</option>
                    </x-select>
                </div>
                <div class="flex flex-col">
                   <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Histórico de Decisões</label>
                   <div class="text-[11px] text-zinc-500 overflow-y-auto max-h-24 bg-white dark:bg-zinc-800 p-2 rounded-lg border border-zinc-100 dark:border-zinc-700">
                       @forelse($orcamento->routeBillingApprovals->sortByDesc('created_at') as $app)
                           <div class="mb-1 border-b border-zinc-50 dark:border-zinc-700 pb-1 last:border-0">
                               <span class="font-bold uppercase {{ $app->status === 'approved' ? 'text-green-600' : ($app->status === 'rejected' ? 'text-red-600' : 'text-orange-600') }}">
                                   {{ $app->status }}
                               </span>
                               <span class="text-zinc-400">by</span> {{ $app->user->name }}
                               <div class="text-[9px]">{{ $app->created_at->format('d/m H:i') }}</div>
                               @if($app->comments)
                                   <div class="text-[10px] text-zinc-600 dark:text-zinc-400 italic">"{{ $app->comments }}"</div>
                               @endif
                           </div>
                       @empty
                           <span class="italic">Nenhuma decisão registrada.</span>
                       @endforelse
                   </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Comentários / Motivo</label>
                <textarea wire:model="comments" rows="2" class="w-full text-sm border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Justificativa da decisão..."></textarea>
            </div>

            <div class="mt-4 flex justify-end">
                <x-button wire:click="approve" variant="primary" class="bg-emerald-600 hover:bg-emerald-700">
                    Registrar Decisão Financeira
                </x-button>
            </div>
        </div>
    @endcan

    {{-- Área de Validação de Anexos --}}
    @if($orcamento->routeBillingAttachments->count())
        <div class="mt-6 border-t pt-4 dark:border-zinc-800">
            <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-3">Conferência de Documentos</h4>
            <div class="space-y-2">
                @foreach($orcamento->routeBillingAttachments as $att)
                    <div class="flex items-center justify-between text-sm p-3 rounded-lg border {{ $att->is_valid ? 'bg-green-50 border-green-100 dark:bg-green-900/10 dark:border-green-900/30' : ($att->is_valid === false ? 'bg-red-50 border-red-100 dark:bg-red-900/10 dark:border-red-900/30' : 'bg-zinc-50 border-zinc-100 dark:bg-zinc-800/50 dark:border-zinc-700') }} transition-all">
                        <div class="flex items-center gap-3">
                             <div class="p-2 bg-white dark:bg-zinc-700 rounded shadow-sm">
                                <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                             </div>
                             <div>
                                <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:underline">
                                    Documento #{{ $att->id }}
                                </a>
                                <div class="text-[10px] text-zinc-500">Tipo: {{ $att->file_type }} | Por: {{ $att->user->name }}</div>
                             </div>
                        </div>

                        <div class="flex items-center gap-4">
                             @can('route_billing_approve')
                                <button wire:click="toggleAttachmentValidity({{ $att->id }})" class="p-1 px-2 rounded bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px] font-bold uppercase hover:bg-zinc-100 transition shadow-sm {{ $att->is_valid ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $att->is_valid ? 'Rejeitar' : 'Validar' }}
                                </button>
                             @endcan

                             @if($att->is_valid)
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                             @elseif($att->is_valid === false)
                                <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
                             @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
