<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'orcamento_item_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'subtotal',
    ];

    protected $casts = [
        'quantidade' => 'decimal:4',
        'valor_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function return()
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    public function orcamentoItem()
    {
        return $this->belongsTo(OrcamentoItens::class, 'orcamento_item_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
