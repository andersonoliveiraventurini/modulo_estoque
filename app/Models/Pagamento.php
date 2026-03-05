<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'pedido_id',
        'condicao_pagamento_id',
        'desconto_aplicado',
        'desconto_balcao',
        'valor_final',
        'valor_pago',
        'troco',
        'data_pagamento',
        'tipo_documento',
        'numero_documento',
        'cnpj_cpf_nota',
        'observacoes',
        'user_id',
        'estornado',
        'data_estorno',
        'motivo_estorno',
        'usuario_estorno_id',
    ];

    protected $casts = [
        'data_pagamento'   => 'datetime',
        'data_estorno'     => 'datetime',
        'desconto_balcao'  => 'decimal:2',
        'desconto_aplicado'=> 'decimal:2',
        'valor_final'      => 'decimal:2',
        'valor_pago'       => 'decimal:2',
        'troco'            => 'decimal:2',
        'estornado'        => 'boolean',
    ];

    // ── Relacionamentos ──────────────────────────────────────────────────────

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function condicaoPagamento()
    {
        return $this->belongsTo(CondicoesPagamento::class, 'condicao_pagamento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usuarioEstorno()
    {
        return $this->belongsTo(User::class, 'usuario_estorno_id');
    }

    /** Formas de pagamento usadas neste pagamento */
    public function formas()
    {
        return $this->hasMany(PagamentoForma::class);
    }

    /** Todos os comprovantes do pagamento (independente da forma) */
    public function comprovantes()
    {
        return $this->hasMany(PagamentoComprovante::class);
    }

    public function movimentacoesCredito()
    {
        return $this->hasMany(ClienteCreditoMovimentacoes::class, 'referencia_id')
            ->where('referencia_tipo', 'orcamento');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAtivos($query)
    {
        return $query->where('estornado', false);
    }

    public function scopeEstornados($query)
    {
        return $query->where('estornado', true);
    }

    public function scopeDoOrcamento($query, $orcamentoId)
    {
        return $query->where('orcamento_id', $orcamentoId);
    }

    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    public function scopeNoPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function utilizouCreditos(): bool
    {
        return $this->formas()->where('usa_credito', true)->exists();
    }

    public function getValorCreditosAttribute()
    {
        return $this->formas()->where('usa_credito', true)->sum('valor');
    }

    public function getValorOutrosMetodosAttribute()
    {
        return $this->formas()->where('usa_credito', false)->sum('valor');
    }
}