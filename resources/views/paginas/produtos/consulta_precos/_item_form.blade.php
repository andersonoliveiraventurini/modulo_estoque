{{--
    Partial: _item_form.blade.php
    Caminho: resources/views/paginas/produtos/consulta_precos/_item_form.blade.php
    Usado tanto no create (template clonável) quanto inline.
--}}
<div class="flex items-center justify-between mb-3">
    <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">
        Item #<span class="item-numero">{{ is_numeric($index) ? $index + 1 : '?' }}</span>
    </span>
    <button type="button" onclick="removerItem(this)"
            class="btn-remover-item hidden items-center gap-1 text-xs text-red-500 hover:text-red-700 transition-colors">
        <x-heroicon-o-trash class="w-3.5 h-3.5" />
        Remover item
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    {{-- Descrição --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
            Descrição <span class="text-red-500">*</span>
        </label>
        <input type="text"
               name="itens[{{ $index }}][descricao]"
               placeholder="Descrição do produto/item"
               required
               value="{{ old("itens.{$index}.descricao") }}"
               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
    </div>

    {{-- Quantidade --}}
    <div>
        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
            Quantidade <span class="text-red-500">*</span>
        </label>
        <input type="number"
               name="itens[{{ $index }}][quantidade]"
               placeholder="Qtd"
               min="1"
               required
               value="{{ old("itens.{$index}.quantidade") }}"
               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
    </div>

    {{-- Cor --}}
    <div>
        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Cor</label>
        <select name="itens[{{ $index }}][cor_id]"
                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
            <option value="">Selecione...</option>
            @foreach ($cores as $cor)
                <option value="{{ $cor->id }}"
                    {{ old("itens.{$index}.cor_id") == $cor->id ? 'selected' : '' }}>
                    {{ $cor->nome }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Part Number --}}
    <div>
        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Part Number</label>
        <input type="text"
               name="itens[{{ $index }}][part_number]"
               placeholder="Part number (opcional)"
               value="{{ old("itens.{$index}.part_number") }}"
               class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
    </div>

    {{-- Fornecedor sugerido --}}
    <div>
        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
            Fornecedor sugerido (opcional)
        </label>
        <select name="itens[{{ $index }}][fornecedor_ids][]"
                class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
            <option value="">Sem preferência</option>
            @foreach ($fornecedores as $fornecedor)
                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Observação do item --}}
<div class="mt-3">
    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Observação do item</label>
    <textarea name="itens[{{ $index }}][observacao]"
              rows="2"
              placeholder="Detalhes adicionais para este item..."
              class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">{{ old("itens.{$index}.observacao") }}</textarea>
</div>
