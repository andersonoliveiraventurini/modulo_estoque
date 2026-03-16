<?php
// Converter SKUs vazios ou com espaço para null
App\Models\Produto::where('sku', '')->orWhere('sku', ' ')->update(['sku' => null]);

// Converter '*'-1, '*'-2, etc criados no script anterior e os próprios '*' isolados para null
App\Models\Produto::where('sku', 'like', '*%')->update(['sku' => null]);

echo "Invalid SKUs converted to NULL.\n";
