<x-layouts.app :title="'Nova Requisição de Compra'">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Nova Requisição de Compra</h1>

        <form action="{{ route('requisicao_compras.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Data da Requisição</label>
                        <input type="date" name="data_requisicao" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Observações</label>
                    <textarea name="observacao" rows="3" class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white"></textarea>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <h2 class="mb-4 text-lg font-semibold text-neutral-800 dark:text-white">Itens da Requisição</h2>
                <div id="itens-container" class="space-y-4">
                    <div class="item-row grid grid-cols-1 gap-4 md:grid-cols-4 border-b border-neutral-100 dark:border-neutral-700 pb-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Produto</label>
                            <select name="itens[0][produto_id]" class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white sm:text-sm">
                                <option value="">Descrição livre...</option>
                                @foreach($produtos as $produto)
                                    <option value="{{ $produto->id }}">{{ $produto->nome }} ({{ $produto->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Descrição Livre</label>
                            <input type="text" name="itens[0][descricao_livre]" class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Quantidade</label>
                            <input type="number" step="1" name="itens[0][quantidade]" required class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">V. Unit. Estimado (R$)</label>
                            <input type="number" step="1" name="itens[0][valor_unitario_estimado]" class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-600 dark:bg-neutral-900 dark:text-white sm:text-sm">
                        </div>
                    </div>
                </div>
                <button type="button" onclick="adicionarItem()" class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    <x-heroicon-o-plus class="mr-1 h-4 w-4" /> Adicionar mais um item
                </button>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('requisicao_compras.index') }}" class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-900">Cancelar</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Enviar Requisição</button>
            </div>
        </form>
    </div>
</x-layouts.app>

@push('scripts')
<script>
    let itemIndex = 1;
    function adicionarItem() {
        const container = document.getElementById('itens-container');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        
        // Limpar valores e atualizar names
        newRow.querySelectorAll('input, select').forEach(el => {
            el.value = '';
            el.name = el.name.replace('[0]', `[${itemIndex}]`);
        });
        
        // Adicionar botão de remoção
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'mt-7 text-red-500 hover:text-red-700';
        removeBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
        removeBtn.onclick = function() { newRow.remove(); };
        
        // Envolver em grid com ações
        newRow.classList.add('relative');
        newRow.appendChild(removeBtn);
        newRow.classList.remove('md:grid-cols-4');
        newRow.classList.add('md:grid-cols-5');
        
        container.appendChild(newRow);
        itemIndex++;
    }
</script>
@endpush
