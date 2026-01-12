<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConferenciaItem extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'conferencia_id',
        'picking_item_id',
        'produto_id',
        'qty_separada',
        'qty_conferida',
        'status',
        'divergencia',
        'motivo_divergencia',
        'conferido_por_id',
        'conferido_em'
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
    ];

    public function conferencia()
    {
        return $this->belongsTo(Conferencia::class);
    }
    public function pickingItem()
    {
        return $this->belongsTo(PickingItem::class);
    }
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function batch()
    {
        return $this->belongsTo(PickingBatch::class, 'picking_batch_id');
    }

    public function itens()
    {
        return $this->hasMany(ConferenciaItem::class);
    }

    public function conferente()
    {
        return $this->belongsTo(User::class, 'conferente_id');
    }

    public function conferidoPor()
    {
        return $this->belongsTo(User::class, 'conferido_por_id');
    }
}
