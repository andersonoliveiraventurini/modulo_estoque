<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickingItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'picking_batch_id','orcamento_item_id','produto_id',
        'qty_solicitada','qty_separada','status','localizacao',
        'separado_por_id','separado_em','motivo_nao_separado',
        'inconsistencia_reportada','inconsistencia_por_id','inconsistencia_obs'
    ];

    protected $casts = [
        'separado_em' => 'datetime',
        'inconsistencia_reportada' => 'bool',
    ];

    public function batch() { return $this->belongsTo(PickingBatch::class, 'picking_batch_id'); }
    public function produto() { return $this->belongsTo(Produto::class); }
    public function orcamentoItem() { return $this->belongsTo(OrcamentoItem::class, 'orcamento_item_id'); }
    public function separador() { return $this->belongsTo(User::class, 'separado_por_id'); }
    public function autorInconsistencia() { return $this->belongsTo(User::class, 'inconsistencia_por_id'); }
}