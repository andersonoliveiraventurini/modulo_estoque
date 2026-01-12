<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conferencia extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'picking_batch_id',
        'status',
        'conferente_id',
        'observacoes',
        'started_at',
        'finished_at',
        'qtd_caixas',
        'qtd_sacos',
        'qtd_sacolas',
        'outros_embalagem'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Relações
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
}