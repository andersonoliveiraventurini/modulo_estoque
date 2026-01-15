<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickingBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id','armazem_id','status','observacoes','started_at','finished_at','criado_por_id',
        'qtd_caixas',
        'qtd_sacos',
        'qtd_sacolas',
        'outros_embalagem',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'qtd_caixas' => 'integer',
        'qtd_sacos' => 'integer',
        'qtd_sacolas' => 'integer',
    ];

    public function orcamento() { return $this->belongsTo(Orcamento::class); }
    public function items() { return $this->hasMany(PickingItem::class, 'picking_batch_id'); }
    public function criador() { return $this->belongsTo(User::class, 'criado_por_id'); }

    public function armazem()
    {
        return $this->belongsTo(Armazem::class);
    }
    /**
     * Relação para buscar o usuário que criou o lote.
     * A chave estrangeira é 'criado_por_id'.
     */
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}