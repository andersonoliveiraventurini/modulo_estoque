<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ConferenciaItemFoto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conferencia_item_id',
        'path',
        'disk',
        'mime_type',
        'size',
        'legenda',
        'enviado_por_id',
    ];

    // ─── Relações ──────────────────────────────────────────────────────────────

    public function conferenciaItem(): BelongsTo
    {
        return $this->belongsTo(ConferenciaItem::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por_id');
    }

    // ─── Acessores ─────────────────────────────────────────────────────────────

    /**
     * URL pública para exibição/download.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Tamanho formatado (KB / MB).
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        if (!$this->size) return '—';

        if ($this->size >= 1_048_576) {
            return number_format($this->size / 1_048_576, 2) . ' MB';
        }

        return number_format($this->size / 1_024, 1) . ' KB';
    }
}