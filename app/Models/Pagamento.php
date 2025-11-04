<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagamento extends Model
{
    /** @use HasFactory<\Database\Factories\PagamentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'pedido_id',
        'condicao_pagamento_id',
        'valor',
        'data_pagamento',
        'tipo_documento',
        'numero_documento',
        'cnpj_cpf_nota',
        'observacoes',
        'user_id',
    ];

    protected $casts = [
        'data_pagamento' => 'datetime',
        'valor' => 'decimal:2',
    ];

    /**
     * Relacionamento com Orçamento
     */
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    /**
     * Relacionamento com Pedido
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Relacionamento com Condição de Pagamento
     */
    public function condicaoPagamento()
    {
        return $this->belongsTo(CondicoesPagamento::class);
    }

    /**
     * Relacionamento com Usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para pagamentos de um orçamento específico
     */
    public function scopeDoOrcamento($query, $orcamentoId)
    {
        return $query->where('orcamento_id', $orcamentoId);
    }

    /**
     * Scope para pagamentos de um pedido específico
     */
    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    /**
     * Scope para pagamentos de uma condição específica
     */
    public function scopeComCondicao($query, $condicaoId)
    {
        return $query->where('condicao_pagamento_id', $condicaoId);
    }

    /**
     * Scope para pagamentos realizados em um período
     */
    public function scopeNoPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
    }

    /**
     * Accessor para formatar o valor
     */
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}
