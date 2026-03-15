<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompraItem extends Model {
    use SoftDeletes;
    protected $table = 'pedido_compra_itens';

    protected $fillable = [
        'pedido_compra_id',
        'produto_id',
        'descricao_livre',
        'quantidade',
        'valor_unitario',
        'preco_custo_anterior',
        'valor_total',
        'observacao',
    ];

    public function pedidoCompra(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
