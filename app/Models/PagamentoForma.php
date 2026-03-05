<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagamentoForma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagamento_formas';

    protected $fillable = [
        'pagamento_id',
        'condicao_pagamento_id',
        'valor',
        'usa_credito',
        'parcelas',
        'valor_parcela',
        'observacoes',
    ];

    protected $casts = [
        'valor'        => 'decimal:2',
        'valor_parcela'=> 'decimal:2',
        'usa_credito'  => 'boolean',
        'parcelas'     => 'integer',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function pagamento()
    {
        return $this->belongsTo(Pagamento::class);
    }

    public function condicaoPagamento()
    {
        return $this->belongsTo(CondicoesPagamento::class, 'condicao_pagamento_id');
    }

    public function comprovantes()
    {
        return $this->hasMany(PagamentoComprovante::class, 'pagamento_forma_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function isParcelado(): bool
    {
        return $this->parcelas > 1;
    }

    public function permiteDescontoBalcao(): bool
    {
        return $this->condicaoPagamento?->permiteDescontoBalcao() ?? false;
    }
}