<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequisicaoCompra extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'solicitante_id',
        'aprovador_id',
        'data_requisicao',
        'status',
        'nivel_aprovacao',
        'aprovacoes_json',
        'observacao',
        'valor_estimado',
        'aprovado_em',
        'rejeitado_em',
        'rejeitado_por_id',
    ];

    protected $casts = [
        'data_requisicao' => 'datetime',
        'aprovado_em'     => 'datetime',
        'rejeitado_em'    => 'datetime',
        'aprovacoes_json' => 'array',
    ];

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }

    public function rejeitadoPor()
    {
        return $this->belongsTo(User::class, 'rejeitado_por_id');
    }

    public function itens()
    {
        return $this->hasMany(RequisicaoCompraItem::class);
    }

    public function pedidoCompra()
    {
        return $this->hasOne(PedidoCompra::class, 'requisicao_compra_id');
    }
}
