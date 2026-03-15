<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisicaoCompraItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requisicao_compra_id',
        'produto_id',
        'descricao_livre',
        'quantidade',
        'valor_unitario_estimado',
    ];

    public function requisicao()
    {
        return $this->belongsTo(RequisicaoCompra::class, 'requisicao_compra_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
