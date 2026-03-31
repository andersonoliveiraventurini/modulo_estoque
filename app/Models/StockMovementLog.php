<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovementLog extends Model
{
    use HasFactory;

    protected $table = 'stock_movement_logs';

    protected $fillable = [
        'produto_id',
        'posicao_id',
        'tipo_movimentacao',
        'quantidade',
        'colaborador_id',
        'observacao',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function posicao()
    {
        return $this->belongsTo(Posicao::class);
    }

    public function colaborador()
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }
}
