<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompra extends Model
{
    use SoftDeletes;

    protected $table = 'pedido_compras';

    protected $fillable = [
        'fornecedor_id',
        'requisicao_compra_id',
        'usuario_id',
        'data_pedido',
        'previsao_entrega',
        'status',
        'numero_pedido',
        'arquivo_pedido',
        'condicao_pagamento_id',
        'forma_pagamento_descricao',
        'valor_total',
        'observacao',
        'editor_usuario_id',
        'editado_em',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'previsao_entrega' => 'date',
        'editado_em' => 'datetime',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function requisicaoCompra(): BelongsTo
    {
        return $this->belongsTo(RequisicaoCompra::class, 'requisicao_compra_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_usuario_id');
    }

    public function condicaoPagamento(): BelongsTo
    {
        return $this->belongsTo(CondicoesPagamento::class, 'condicao_pagamento_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoCompraItem::class, 'pedido_compra_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class, 'pedido_compra_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(PedidoCompraFollowup::class);
    }
}

