<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjecaoCompra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projecoes_compra';

    protected $fillable = [
        'codigo',
        'user_id',
        'data_pedido',
        'previsao_recebimento',
        'meses_compra',
        'abater_estoque_atual',
        'abater_consumo_ate_recebimento',
        'filtros',
        'valor_total_estimado',
        'total_itens',
    ];

    protected $casts = [
        'filtros' => 'array',
        'data_pedido' => 'date',
        'previsao_recebimento' => 'date',
        'abater_estoque_atual' => 'boolean',
        'abater_consumo_ate_recebimento' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(ProjecaoCompraItem::class, 'projecao_compra_id');
    }
}
