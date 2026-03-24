<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FaltaItem extends Model
{
    protected $fillable = [
        'falta_id', 'produto_id', 'descricao_produto',
        'quantidade', 'valor_unitario', 'valor_total',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'valor_total'    => 'decimal:2',
    ];

    public function falta() { return $this->belongsTo(Falta::class); }
    public function produto() { return $this->belongsTo(Produto::class); }
}
