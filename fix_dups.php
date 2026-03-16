<?php
$skus = App\Models\Produto::select('sku')->groupBy('sku')->havingRaw('count(*) > 1')->pluck('sku');
foreach($skus as $sku) {
    if (!$sku) continue;
    $prods = App\Models\Produto::where('sku', $sku)->orderBy('id')->get();
    foreach($prods as $index => $prod) {
        if ($index > 0) {
            $prod->sku = $sku . '-' . $index;
            $prod->save();
        }
    }
}

$combos = App\Models\Produto::select('nome', 'fornecedor_id', 'cor_id')->groupBy('nome', 'fornecedor_id', 'cor_id')->havingRaw('count(*) > 1')->get();
foreach($combos as $combo) {
    $prods = App\Models\Produto::where('nome', $combo->nome)->where('fornecedor_id', $combo->fornecedor_id)->where('cor_id', $combo->cor_id)->orderBy('id')->get();
    foreach($prods as $index => $prod) {
        if ($index > 0) {
            $prod->nome = $prod->nome . ' (Cópia ' . $index . ')';
            $prod->save();
        }
    }
}
echo "Duplicates resolved.\n";
