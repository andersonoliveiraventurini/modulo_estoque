{{--
    Partial: _campos_produto_item.blade.php
    Variáveis esperadas:
      $index         — índice do item no array itens[]
      $categorias    — Collection de Categoria
      $subCategorias — Collection de SubCategoria
--}}

<div class="mt-4 border-t border-zinc-100 dark:border-zinc-700 pt-4">
    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">
        Informações do produto <span class="font-normal normal-case text-zinc-400">(opcionais)</span>
    </p>

    {{-- Linha 1: NCM · Código de Barras · SKU --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">NCM</label>
            <input type="text"
                   name="itens[{{ $index }}][ncm]"
                   value="{{ old("itens.{$index}.ncm") }}"
                   placeholder="Ex: 7610.10.00"
                   maxlength="20"
                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Código de Barras (EAN)</label>
            <input type="text"
                   name="itens[{{ $index }}][codigo_barras]"
                   value="{{ old("itens.{$index}.codigo_barras") }}"
                   placeholder="Ex: 7891234567890"
                   maxlength="50"
                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">SKU / Código</label>
            <input type="text"
                   name="itens[{{ $index }}][sku]"
                   value="{{ old("itens.{$index}.sku") }}"
                   placeholder="Ex: ALU-PERF-3M"
                   maxlength="50"
                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
        </div>
    </div>

    {{-- Linha 2: Unidade · Peso · Categoria · Subcategoria --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">
                Validade @if($item->produto && $item->produto->is_perishable)<span class="text-red-500">*</span>@endif
            </label>
            <input type="date"
                   name="itens[{{ $index }}][data_vencimento]"
                   value="{{ old("itens.{$index}.data_vencimento") }}"
                   {{ ($item->produto && $item->produto->is_perishable) ? 'required' : '' }}
                   {{ (!$item->produto || !$item->produto->is_perishable) ? 'disabled' : '' }}
                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none disabled:bg-zinc-100 dark:disabled:bg-zinc-700">
        </div>

        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Unidade de Medida</label>
            <select name="itens[{{ $index }}][unidade_medida]"
                    class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                <option value="">Selecione...</option>
                @foreach ([
                    'UN'  => 'UN — Unidade',
                    'PC'  => 'PC — Peça',
                    'CX'  => 'CX — Caixa',
                    'KG'  => 'KG — Quilograma',
                    'G'   => 'G — Grama',
                    'M'   => 'M — Metro',
                    'M2'  => 'M² — Metro quadrado',
                    'M3'  => 'M³ — Metro cúbico',
                    'L'   => 'L — Litro',
                    'ML'  => 'ML — Mililitro',
                    'PAR' => 'PAR — Par',
                    'RL'  => 'RL — Rolo',
                    'PT'  => 'PT — Pacote',
                ] as $val => $label)
                    <option value="{{ $val }}"
                        {{ old("itens.{$index}.unidade_medida") == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Peso (kg)</label>
            <input type="number"
                   step="1"
                   min="0"
                   name="itens[{{ $index }}][peso]"
                   value="{{ old("itens.{$index}.peso") }}"
                   placeholder="Ex: 1.250"
                   class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
        </div>

        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Categoria</label>
            <select name="itens[{{ $index }}][categoria_id]"
                    data-index="{{ $index }}"
                    class="categoria-select w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none"
                    onchange="filtrarSubCategorias(this)">
                <option value="">Selecione...</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old("itens.{$index}.categoria_id") == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Subcategoria</label>
            <select name="itens[{{ $index }}][sub_categoria_id]"
                    id="sub_categoria_{{ $index }}"
                    class="w-full border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                <option value="">Selecione a categoria primeiro</option>
                @foreach ($subCategorias as $sub)
                    <option value="{{ $sub->id }}"
                            data-categoria="{{ $sub->categoria_id }}"
                            {{ old("itens.{$index}.sub_categoria_id") == $sub->id ? 'selected' : '' }}
                            @if (old("itens.{$index}.categoria_id") && old("itens.{$index}.categoria_id") != $sub->categoria_id)
                                hidden disabled
                            @endif>
                        {{ $sub->nome }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
