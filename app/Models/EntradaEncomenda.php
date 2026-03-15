<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntradaEncomenda extends Model {
    use SoftDeletes;
    protected $fillable = [
        'grupo_id',
        'recebido_por',
        'entregue_para',
        'cliente_id',
        'data_recebimento',
        'data_entrega',
        'status',
        'observacao',
    ];

    protected $casts = [
        'data_recebimento' => 'date',
        'data_entrega'     => 'date',
    ];

    // ── Relacionamentos ─────────────────────────────────────

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(ConsultaPrecoGrupo::class, 'grupo_id');
    }

    public function recebedor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recebido_por');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'entregue_para');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(EntradaEncomendaItem::class, 'entrada_id');
    }

    // ── Helpers ─────────────────────────────────────────────

    /** Verifica se todos os itens foram recebidos completamente */
    public function estaCompleto(): bool
    {
        return $this->itens->every(fn($item) => $item->recebido_completo);
    }

    /** Retorna itens pendentes (não recebidos completamente) */
    public function itensPendentes()
    {
        return $this->itens->filter(fn($item) => ! $item->recebido_completo);
    }
}