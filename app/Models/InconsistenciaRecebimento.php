<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InconsistenciaRecebimento extends Model
{
    protected $table = 'inconsistencia_recebimentos';

    protected $fillable = [
        'pedido_compra_id',
        'produto_id',
        'quantidade_esperada',
        'quantidade_recebida',
        'usuario_id',
        'movimentacao_id',
        'observacao',
    ];

    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class);
    }
}
