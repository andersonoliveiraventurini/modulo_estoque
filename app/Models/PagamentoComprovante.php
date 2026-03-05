<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PagamentoComprovante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagamento_comprovantes';

    protected $fillable = [
        'pagamento_id',
        'pagamento_forma_id',
        'nome_original',
        'path',
        'mime_type',
        'tamanho',
        'user_id',
    ];

    protected $casts = [
        'tamanho' => 'integer',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function pagamento()
    {
        return $this->belongsTo(Pagamento::class);
    }

    public function pagamentoForma()
    {
        return $this->belongsTo(PagamentoForma::class, 'pagamento_forma_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isImagem(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        if ($this->tamanho >= 1024 * 1024) {
            return number_format($this->tamanho / (1024 * 1024), 1) . ' MB';
        }
        return number_format($this->tamanho / 1024, 0) . ' KB';
    }

    /**
     * URL assinada para download seguro pelo disco private (30 min)
     */
    public function urlTemporaria(int $minutos = 30): string
    {
        return Storage::disk('private')->temporaryUrl(
            $this->path,
            now()->addMinutes($minutos)
        );
    }

    public function getIconeAttribute(): string
    {
        return $this->isPdf() ? '📄' : '🖼️';
    }
}