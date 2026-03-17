<!-- Container -->
<div class="bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-200 dark:border-zinc-700">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
            Listagem de Clientes
        </h2>
        <div class="flex items-end gap-4">
            <!-- Pesquisa (flexível, ocupa 2x mais espaço) -->
            <div class="flex flex-col flex-[2]">
                <label for="search" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Pesquisar
                </label>
                <x-input id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome fantasia, razão social, tratamento, CNPJ, limite ou desconto..." />
            </div>

            <!-- Itens por página (largura fixa) -->
            <div class="flex flex-col w-28">
                <label for="perPage" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Itens por página:
                </label>
                <x-select id="perPage" wire:model.live="perPage">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-select>
            </div>
        </div>
    </div>
    {{-- Cole este bloco logo após o div de cabeçalho (após o fechamento do primeiro div.flex) --}}
   <div class="flex items-end gap-4" style="padding: 0.5rem 1.5rem 1rem;">

        <!-- Vendedor -->
        <div class="flex flex-col flex-[2] min-w-[180px]">
            <label for="vendedor" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                Vendedor
            </label>
            <x-input id="vendedor" wire:model.live.debounce.300ms="vendedor" placeholder="Nome do vendedor..." />
        </div>

        <!-- Cidade -->
        <div class="flex flex-col flex-[2] min-w-[180px]">
            <label for="cidade" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                Cidade
            </label>
            <x-input id="cidade" wire:model.live.debounce.300ms="cidade" placeholder="Nome da cidade..." />
        </div>

        <!-- Botão limpar filtros -->
        <div class="flex flex-col">
            <label class="text-sm text-transparent select-none mb-1">.</label>
            <x-button wire:click="limparFiltros" variant="secondary">
                <x-heroicon-o-x-mark class="w-4 h-4" />
                Limpar filtros
            </x-button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-2 py-3 text-left">Código</th>
                    <th class="px-2 py-3 text-left">
                        <x-heroicon-o-building-office-2 class="w-4 h-4 inline mr-1" />
                        Nome Brcom
                    </th>
                    <th class="px-2 py-3 text-left">Nome Fantasia</th>
                    <th class="px-2 py-3 text-left">Razão Social</th>
                    <th class="px-2 py-3 text-left">Tratamento</th>
                    <th class="px-2 py-3 text-left">CNPJ</th>
                    <th class="px-2 py-3 text-left">Comercial</th>
                    <th class="px-2 py-3 text-left">WhatsApp</th>
                    <th class="px-2 py-3 text-left">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($clientes as $c)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">

                        <td class="px-2 py-4">
                            <div class="flex flex-col gap-1.5">
                                 <a href="/clientes/{{ $c->id }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline flex items-center gap-1">
                                    {{ $c->id }}
                                </a>
                            </div>
                        </td>
                        <td class="px-2 py-4">
                            <div class="flex flex-col">
                                <a href="/clientes/{{ $c->id }}" class="font-bold text-zinc-900 dark:text-zinc-50 hover:underline flex items-center gap-1">
                                    @if($c->bloqueado)
                                        <x-heroicon-s-lock-closed class="w-4 h-4 text-red-600" />
                                    @endif
                                    {{ $c->nome }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <a href="{{ route('orcamentos.criar', $c->id) }}" class="text-[9px] bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100 hover:bg-blue-100 transition font-bold uppercase text-center whitespace-nowrap">CRIAR ORÇAMENTO</a>
                                    <a href="{{ route('consulta_preco.criar', $c->id) }}" class="text-[9px] bg-amber-50 text-amber-700 px-2 py-1 rounded border border-amber-100 hover:bg-amber-100 transition font-bold uppercase text-center whitespace-nowrap">CRIAR ENCOMENDA</a>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-4 text-zinc-800 dark:text-zinc-200">
                            {{ $c->nome_fantasia ?: '-' }}
                        </td>
                        <td class="px-2 py-4">
                            <span class="text-xs text-zinc-500 break-words line-clamp-2 max-w-[150px]">{{ $c->razao_social }}</span>
                        </td>
                        <td class="px-2 py-4 text-xs text-zinc-500">
                            {{ $c->tratamento ?: '-' }}
                        </td>
                        <td class="px-2 py-4 font-mono text-xs">
                            {{ $c->cnpj_formatado }}
                        </td>
                        <td class="px-2 py-4 text-xs">
                            <div class="flex flex-col font-medium">
                                <span class="text-zinc-700 dark:text-zinc-300">Limite: R$ {{ number_format($c->limite, 2, ',', '.') }}</span>
                                <span class="text-zinc-600 dark:text-zinc-400">Desconto: {{ $c->desconto }}%</span>
                            </div>
                        </td>
                        <td class="px-2 py-4 text-center">
                            @if($c->whatsapp_url)
                                <a href="{{ $c->whatsapp_url }}" target="_blank" title="Conversar no WhatsApp" 
                                   class="inline-flex items-center justify-center p-2 rounded-full shadow-sm border transition-all hover:scale-110 active:scale-95 group w-10 h-10
                                          bg-white border-zinc-200 hover:shadow-green-500/20
                                          dark:bg-green-950/30 dark:border-green-500/30 dark:hover:border-green-400">
                                    <svg class="w-6 h-6 text-[#25D366] drop-shadow-[0_0_8px_rgba(37,211,102,0.3)]" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.658 1.43 5.63 1.432h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                </a>
                            @else
                                <span class="text-zinc-300">-</span>
                            @endif
                        </td>
                        <td class="px-2 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('clientes.edit', $c->id) }}" title="Editar" class="p-1.5 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 rounded-lg transition">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </a>

                                <form action="{{ route('clientes.destroy', $c->id) }}" method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Excluir" class="p-1.5 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Nenhum cliente encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    @if ($clientes->hasPages())
        <div
            class="p-6 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
            <div>
                {{ $clientes->links() }}
            </div>
        </div>
    @endif
</div>
