<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConferenciaItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conferencia_id',
        'picking_item_id',
        'produto_id',
        'consulta_preco_id',  // ✅
        'is_encomenda',       // ✅
        'descricao_encomenda', // ✅
        'qty_separada',
        'qty_conferida',
        'status',
        'divergencia',
        'motivo_divergencia',
        'conferido_por_id',
        'conferido_em',
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
        'is_encomenda' => 'bool', // ✅
    ];

// ✅ Relacionamento com item de cotação
    public function consultaPreco(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ConsultaPreco::class, 'consulta_preco_id');
    }

    // ─── Relações ──────────────────────────────────────────────────────────────

    public function conferencia(): BelongsTo
    {
        return $this->belongsTo(Conferencia::class);
    }

    public function pickingItem(): BelongsTo
    {
        return $this->belongsTo(PickingItem::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function conferidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conferido_por_id');
    }

    /**
     * Fotos associadas a este item de conferência.
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(ConferenciaItemFoto::class);
    }
}
