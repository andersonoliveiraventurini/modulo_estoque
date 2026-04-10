<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estorno extends Model
{
    use HasFactory, SoftDeletes;

    // ── Constantes de status ──────────────────────────────────────────────────

    const STATUS_PENDENTE  = 'pendente';
    const STATUS_APROVADO  = 'aprovado';
    const STATUS_REJEITADO = 'rejeitado';
    const STATUS_CONCLUIDO = 'concluido';

    // ── Configuração ──────────────────────────────────────────────────────────

    protected $fillable = [
        'pagamento_id',
        'solicitante_id',
        'aprovador_id',
        'motivo',
        'forma_estorno',
        'forma_estorno_detalhe',
        'valor',
        'status',
        'observacao_aprovador',
        'aprovado_em',
        'concluido_em',
    ];

    protected $casts = [
        'valor'        => 'decimal:2',
        'aprovado_em'  => 'datetime',
        'concluido_em' => 'datetime',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────────────

    /**
     * Pagamento que originou o estorno.
     */
    public function pagamento(): BelongsTo
    {
        return $this->belongsTo(Pagamento::class);
    }

    /**
     * Usuário que abriu a solicitação de estorno.
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * Usuário que aprovou ou rejeitou o estorno.
     */
    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }

    // ── Helpers de status ─────────────────────────────────────────────────────

    public function isPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function isAprovado(): bool
    {
        return $this->status === self::STATUS_APROVADO;
    }

    public function isRejeitado(): bool
    {
        return $this->status === self::STATUS_REJEITADO;
    }

    public function isConcluido(): bool
    {
        return $this->status === self::STATUS_CONCLUIDO;
    }
}
