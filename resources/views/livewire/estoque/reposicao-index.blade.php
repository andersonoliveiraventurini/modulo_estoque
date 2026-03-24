<div class="p-6 bg-white dark:bg-zinc-800 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-zinc-100 tracking-tight">HUB – Controle e Reposição</h1>
        <button wire:click="abrirModalSolicitar()" class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-lg transition duration-200 flex items-center space-x-2 transform hover:scale-105 active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span class="font-semibold">Nova Solicitação</span>
        </button>
    </div>

    <!-- Abas -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-1 mb-6 inline-flex">
        <button wire:click="$set('tab', 'hub')" class="py-2.5 px-6 rounded-xl text-sm font-semibold transition duration-200 {{ $tab === 'hub' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 shadow-sm' : 'text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
            Produtos no HUB
        </button>
        <button wire:click="$set('tab', 'ordens')" class="py-2.5 px-6 rounded-xl text-sm font-semibold transition duration-200 {{ $tab === 'ordens' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 shadow-sm' : 'text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
            Histórico de Reposição
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl flex items-center animate-fade-in-down">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Aba 1: HUB Stock -->
    @if($tab === 'hub')
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/50">
                <div class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="searchProduto" type="text" placeholder="Filtrar por nome ou SKU..." class="block w-full pl-10 pr-3 py-2.5 border-none bg-white dark:bg-zinc-800 rounded-xl ring-1 ring-gray-200 dark:ring-zinc-700 text-gray-900 dark:text-zinc-300 focus:ring-2 focus:ring-indigo-500 sm:text-sm transition duration-200">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-zinc-800">
                    <thead class="bg-gray-50/50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Produto</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Saldo no HUB</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-50 dark:divide-zinc-800">
                        @if($saldoHub->isNotEmpty())
                            @foreach($saldoHub as $item)
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition duration-150 group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-800 dark:text-zinc-200">{{ $item->produto?->nome ?? '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 rounded-md">{{ $item->produto?->sku ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 inline-block px-3 py-1 rounded-full">{{ number_format($item->quantidade, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <button wire:click="abrirModalSolicitar({{ $item->produto_id }})" class="p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-lg transition-colors" title="Solicitar Reposição">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button wire:click="abrirModalDevolver({{ $item->produto_id }})" class="p-2 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition-colors" title="Devolver ao Estoque">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-zinc-500 font-medium italic">Nenhum produto com saldo no HUB.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-gray-50/30 dark:bg-zinc-800/30 border-t border-gray-50 dark:border-zinc-800">{{ $saldoHub->links() }}</div>
        </div>
    @endif

    <!-- Aba 2: Ordens -->
    @if($tab === 'ordens')
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-zinc-800">
                    <thead class="bg-gray-50/50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Produto</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Qtd</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Data/Hora</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-50 dark:divide-zinc-800">
                        @if($ordens->isNotEmpty())
                            @foreach($ordens as $ordem)
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-zinc-500">#{{ str_pad($ordem->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-800 dark:text-zinc-200">{{ $ordem->produto->nome }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">{{ number_format($ordem->quantidade_solicitada, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold tracking-wide
                                            {{ $ordem->status === 'pendente' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                            {{ $ordem->status === 'concluida' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                            {{ $ordem->status === 'cancelada' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : '' }}">
                                            {{ strtoupper($ordem->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400 font-medium">
                                        {{ $ordem->created_at->format('d/m/Y') }}
                                        <span class="block text-xs text-gray-400 dark:text-zinc-500 font-normal mt-0.5">{{ $ordem->created_at->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-1">
                                            @if($ordem->status === 'pendente')
                                                <button wire:click="marcarComoImpresso({{ $ordem->id }})" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-600 hover:text-white transition duration-200">
                                                    Imprimir
                                                </button>
                                                <button wire:click="abrirModalExecutar({{ $ordem->id }})" class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg hover:bg-emerald-600 dark:hover:bg-emerald-600 hover:text-white transition duration-200">
                                                    Repor
                                                </button>
                                                <button wire:click="cancelarOrdem({{ $ordem->id }})" onclick="confirm('Deseja realmente cancelar esta ordem?') || event.stopImmediatePropagation()" class="p-1.5 text-rose-400 dark:text-rose-500 hover:text-rose-600 transition duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            @else
                                                <a href="{{ route('reposicao.pdf', ['ordem' => $ordem->id]) }}" target="_blank" class="px-3 py-1.5 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 font-semibold underline underline-offset-4 decoration-dotted">
                                                    PDF
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-zinc-500 font-medium italic">Nenhuma ordem de reposição registrada.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-gray-50/30 dark:bg-zinc-800/30 border-t border-gray-50 dark:border-zinc-800">{{ $ordens->links() }}</div>
        </div>
    @endif

    <!-- MODAL SOLICITAR -->
    @if($modalSolicitar)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/40 dark:bg-zinc-950/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl max-w-lg w-full p-8 relative animate-scale-in">
                <button wire:click="fecharModalSolicitar()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-zinc-100 mb-6">Solicitar Reposição</h2>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Produto</label>
                        <select wire:model="produtoSolicitarId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                            <option value="">Selecione o produto...</option>
                            @foreach($produtos as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                        @error('produtoSolicitarId') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Quantidade</label>
                        <div class="relative">
                            <input wire:model="quantidadeSolicitar" type="number" step="1" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5 pr-12">
                            <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 dark:text-zinc-500 text-xs font-bold">UN</span>
                        </div>
                        @error('quantidadeSolicitar') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-8 flex space-x-3">
                    <button wire:click="fecharModalSolicitar()" class="flex-1 py-3 text-gray-500 dark:text-zinc-400 font-bold hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-2xl transition duration-200">Cancelar</button>
                    <button wire:click="solicitarReposicao()" class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 transition duration-200">Confirmar</button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL EXECUTAR -->
    @if($modalExecutar)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/40 dark:bg-zinc-950/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl max-w-2xl w-full p-8 relative animate-scale-in">
                <button wire:click="fecharModalExecutar()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-zinc-100 mb-2">Executar Reposição</h2>
                <p class="text-sm text-gray-500 dark:text-zinc-400 mb-8 font-medium">Informe os detalhes da retirada física dos itens.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-2xl">
                            <h3 class="flex items-center text-sm font-bold text-amber-800 dark:text-amber-400 mb-1">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Dica de Reposição
                            </h3>
                            <p class="text-xs text-amber-700/80 dark:text-amber-500/80 leading-relaxed font-medium">
                                Se você não possui os produtos em outro armazém físico, selecione <strong>"Entrada Direta"</strong>. Isso registrará apenas a entrada no HUB sem retirar de outro local.
                            </p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Armazém de Origem</label>
                        <select wire:model.live="armazemOrigemId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                            <option value="">Entrada Direta (Sem origem física)</option>
                            @foreach($armazens->where('id', '!=', 1) as $a)
                                <option value="{{ $a->id }}">{{ $a->nome }}</option>
                            @endforeach
                        </select>
                        @error('armazemOrigemId') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Corredor (Opcional)</label>
                        <select wire:model.live="corredorOrigemId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5 {{ !$armazemOrigemId ? 'opacity-50 pointer-events-none' : '' }}">
                            <option value="">Selecione...</option>
                            @foreach($corredores as $c)
                                <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Posição (Opcional)</label>
                        <select wire:model="posicaoOrigemId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5 {{ !$corredorOrigemId ? 'opacity-50 pointer-events-none' : '' }}">
                            <option value="">Selecione...</option>
                            @foreach($posicoes as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Executor Físico</label>
                        <select wire:model="executorId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                            <option value="">Quem executou?</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('executorId') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                @error('executar') <div class="mt-6 p-3 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold border border-rose-100 dark:border-rose-800">{{ $message }}</div> @enderror

                <div class="mt-8 flex space-x-3">
                    <button wire:click="fecharModalExecutar()" class="flex-1 py-3 text-gray-500 dark:text-zinc-400 font-bold hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-2xl transition duration-200">Voltar</button>
                    <button wire:click="confirmarReposicao()" class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-100 dark:shadow-none hover:bg-emerald-700 transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Efetivar Movimentação
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DEVOLVER -->
    @if($modalDevolver)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/40 dark:bg-zinc-950/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl max-w-2xl w-full p-8 relative animate-scale-in">
                <button wire:click="fecharModalDevolver()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-zinc-100 mb-2">Devolver ao Estoque</h2>
                <p class="text-sm text-gray-500 dark:text-zinc-400 mb-8 font-medium">O produto sairá do HUB para o endereço físico escolhido.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Quantidade</label>
                        <input wire:model="quantidadeDevolver" type="number" step="1" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                        @error('quantidadeDevolver') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Armazém Destino</label>
                        <select wire:model.live="armazemDestinoId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                            <option value="">Selecione...</option>
                            @foreach($armazens->where('id', '!=', 1) as $a)
                                <option value="{{ $a->id }}">{{ $a->nome }}</option>
                            @endforeach
                        </select>
                        @error('armazemDestinoId') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Corredor</label>
                        <select wire:model.live="corredorDestinoId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5 {{ !$armazemDestinoId ? 'opacity-50 pointer-events-none' : '' }}">
                            <option value="">Selecione...</option>
                            @foreach($corredoresDestino as $c)
                                <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Posição</label>
                        <select wire:model="posicaoDestinoId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5 {{ !$corredorDestinoId ? 'opacity-50 pointer-events-none' : '' }}">
                            <option value="">Selecione...</option>
                            @foreach($posicoesDestino as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Executor</label>
                        <select wire:model="executorDevolucaoId" class="block w-full border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300 rounded-xl focus:ring-2 focus:ring-indigo-500 py-2.5">
                            <option value="">Selecione...</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('executorDevolucaoId') <span class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                @error('devolver') <div class="mt-6 p-3 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold border border-rose-100 dark:border-rose-800">{{ $message }}</div> @enderror

                <div class="mt-8 flex space-x-3">
                    <button wire:click="fecharModalDevolver()" class="flex-1 py-3 text-gray-500 dark:text-zinc-400 font-bold hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-2xl transition duration-200">Cancelar</button>
                    <button wire:click="confirmarDevolucao()" class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-2xl shadow-lg shadow-rose-100 dark:shadow-none hover:bg-rose-700 transition duration-200">Devolver Agora</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fade-in-down {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes scale-in {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in-down { animation: fade-in-down 0.4s ease-out; }
        .animate-scale-in { animation: scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
    </style>

    <script>
        // O redirecionamento agora é feito via redirecionamento direto do Livewire para evitar bloqueio de popup.
    </script>
</div>
