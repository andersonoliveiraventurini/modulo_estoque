<div class="p-6 bg-white dark:bg-zinc-800 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-zinc-100 tracking-tight">Reposição Manual para HUB</h1>
            <a href="{{ route('estoque.reposicao.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">&larr; Voltar ao HUB</a>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('transferencia'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl flex items-center">
                <span class="font-medium">{{ $errors->first('transferencia') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-8">
            <form wire:submit.prevent="transferir" class="space-y-6">
                {{-- Seleção de Produto --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2 uppercase tracking-wide">Vincular a Orçamento (Opcional)</label>
                        <select wire:model="orcamentoId" class="block w-full rounded-xl border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 transition duration-200">
                            <option value="">Nenhum (Transferência Geral)</option>
                            @foreach($orcamentos as $o)
                                <option value="{{ $o->id }}">Orçamento #{{ $o->id }} - {{ $o->cliente?->nome ?? 'Cliente não informado' }}</option>
                            @endforeach
                        </select>
                        @error('orcamentoId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2 uppercase tracking-wide">Colaborador Executor <span class="text-red-500">*</span></label>
                        <select wire:model="colaboradorId" class="block w-full rounded-xl border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 transition duration-200">
                            <option value="">Quem está realizando a movimentação?</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        @error('colaboradorId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2 uppercase tracking-wide">Produto</label>
                        <select wire:model.live="produtoId" class="block w-full rounded-xl border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 transition duration-200">
                            <option value="">Selecione o Produto...</option>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }} (SKU: {{ $produto->sku }})</option>
                            @endforeach
                        </select>
                        @error('produtoId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2 uppercase tracking-wide">Quantidade a Transferir</label>
                        <input type="number" step="0.001" wire:model="quantidade" class="block w-full rounded-xl border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 transition duration-200">
                        @error('quantidade') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-zinc-800 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-zinc-200 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Endereçamento de Origem (Secundário)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 mb-1 uppercase">Armazém</label>
                            <select wire:model.live="armazemOrigemId" class="block w-full rounded-lg border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($armazensOrigem as $az)
                                    <option value="{{ $az->id }}">{{ $az->nome }}</option>
                                @endforeach
                            </select>
                            @error('armazemOrigemId') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 mb-1 uppercase">Corredor</label>
                            <select wire:model.live="corredorOrigemId" class="block w-full rounded-lg border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500" {{ empty($corredoresOrigem) ? 'disabled' : '' }}>
                                <option value="">Selecione...</option>
                                @foreach($corredoresOrigem as $c)
                                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-zinc-400 mb-1 uppercase">Posição</label>
                            <select wire:model="posicaoOrigemId" class="block w-full rounded-lg border-none ring-1 ring-gray-200 dark:ring-zinc-700 bg-gray-50 dark:bg-zinc-800 text-sm text-gray-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500" {{ empty($posicoesOrigem) ? 'disabled' : '' }}>
                                <option value="">Selecione...</option>
                                @foreach($posicoesOrigem as $p)
                                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl flex items-center justify-between border border-indigo-100 dark:border-indigo-800">
                    <div class="flex items-center">
                        <div class="p-2 bg-indigo-600 rounded-lg text-white mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Destino Fixo</p>
                            <p class="text-sm font-bold text-indigo-900 dark:text-indigo-200">HUB (Armazém Principal)</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-indigo-500 bg-white dark:bg-zinc-800 px-3 py-1 rounded-full border border-indigo-100 dark:border-indigo-800">Readonly</span>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-bold py-4 rounded-xl shadow-lg transition duration-200 transform hover:scale-[1.02] active:scale-[0.98] flex justify-center items-center space-x-2">
                        <span>Confirmar Transferência para HUB</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
