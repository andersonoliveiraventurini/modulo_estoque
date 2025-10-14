<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickingBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id','armazem_id','status','observacoes','started_at','finished_at','criado_por_id'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function orcamento() { return $this->belongsTo(Orcamento::class); }
    public function items() { return $this->hasMany(PickingItem::class, 'picking_batch_id'); }
    public function criador() { return $this->belongsTo(User::class, 'criado_por_id'); }
}