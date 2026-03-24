<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoCompraFollowup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pedido_compra_id', 'user_id', 'descricao', 'previsao_nova', 'tipo',
    ];

    protected $casts = ['previsao_nova' => 'date'];

    public function pedidoCompra() { return $this->belongsTo(PedidoCompra::class); }
    public function user() { return $this->belongsTo(User::class); }
}
