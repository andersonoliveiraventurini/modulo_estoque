<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicoesPagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condicoes_pagamento';

    protected $fillable = [
        'nome',
        'tipo',
        'permite_parcelamento',
        'max_parcelas',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'permite_parcelamento' => 'boolean',
        'ativo'                => 'boolean',
        'max_parcelas'         => 'integer',
        'ordem'                => 'integer',
    ];

    // ── Tipos com direito a desconto de balcão ───────────────────────────────
    const TIPOS_COM_DESCONTO = ['dinheiro', 'pix'];

    // ── ID da condição especial "Outros meios" ───────────────────────────────
    const ID_CONDICAO_ESPECIAL = 20;

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function pagamentoFormas()
    {
        return $this->hasMany(PagamentoForma::class, 'condicao_pagamento_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Se esta condição gera direito ao desconto de balcão (pix ou dinheiro)
     */
    public function permiteDescontoBalcao(): bool
    {
        return in_array($this->tipo, self::TIPOS_COM_DESCONTO);
    }

    /**
     * Se esta condição usa crédito do cliente
     */
    public function isCreditoCliente(): bool
    {
        return $this->tipo === 'credito_cliente' || $this->id == 2;
    }

    /**
     * Se esta condição é a especial "outros meios"
     */
    public function isEspecial(): bool
    {
        return $this->id === self::ID_CONDICAO_ESPECIAL;
    }

    /**
     * Rótulo legível do tipo
     */
    public function getTipoDescricaoAttribute(): string
    {
        return [
            'dinheiro'        => 'Dinheiro',
            'cartao_credito'  => 'Cartão de Crédito',
            'cartao_debito'   => 'Cartão de Débito',
            'pix'             => 'PIX',
            'boleto'          => 'Boleto',
            'cheque'          => 'Cheque',
            'credito_cliente' => 'Crédito do Cliente',
            'transferencia'   => 'Transferência',
            'outros'          => 'Outros',
        ][$this->tipo] ?? $this->tipo;
    }

    /**
     * Ícone Heroicon para o tipo
     */
    public function getIconeAttribute(): string
    {
        return [
            'dinheiro'        => 'currency-dollar',
            'cartao_credito'  => 'credit-card',
            'cartao_debito'   => 'credit-card',
            'pix'             => 'qrcode',
            'boleto'          => 'document-text',
            'cheque'          => 'document-text',
            'credito_cliente' => 'gift',
            'transferencia'   => 'arrows-right-left',
            'outros'          => 'ellipsis-horizontal',
        ][$this->tipo] ?? 'ellipsis-horizontal';
    }
}