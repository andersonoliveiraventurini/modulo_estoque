<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjecaoCompraItem extends Model
{
    use HasFactory;

    protected $table = 'projecao_compra_itens';

    protected $fillable = [
        'projecao_compra_id',
        'produto_id',
        'consumo_mensal',
        'estoque_atual',
        'previsao_consumo_recebimento',
        'quantidade_sugerida',
        'valor_unitario',
        'abaixo_minimo',
    ];

    public function projecao()
    {
        return $this->belongsTo(ProjecaoCompra::class, 'projecao_compra_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
