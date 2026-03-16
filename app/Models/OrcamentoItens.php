<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrcamentoItens extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoItensFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id', 'produto_id', 'quantidade',
        'valor_unitario', 'desconto', 'valor_com_desconto', 'user_id', 'valor_unitario_com_desconto'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function pickingItems()
    {
        return $this->hasMany(PickingItem::class, 'orcamento_item_id');
    }

    public function getQuantidadeSeparadaAttribute()
    {
        return $this->pickingItems()
            ->whereHas('batch', function ($query) {
                $query->where('status', 'concluido');
            })
            ->sum('qty_separada');
    }

    public function getQuantidadeRestanteAttribute()
    {
        return max(0, $this->quantidade - $this->quantidade_separada);
    }

    public function getQuantidadeConferidaAttribute()
    {
        return ConferenciaItem::whereHas('conferencia', function ($query) {
                $query->where('orcamento_id', $this->orcamento_id)
                      ->where('status', 'concluida');
            })
            ->whereHas('pickingItem', function ($query) {
                $query->where('orcamento_item_id', $this->id);
            })
            ->sum('qty_conferida');
    }

    public function usuarioDesconto()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
