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
        'data_pagamento' => 'datetime',
        'data_estorno' => 'datetime',
        'desconto_balcao' => 'decimal:2',
        'desconto_aplicado' => 'decimal:2',
        'valor_final' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'troco' => 'decimal:2',
        'estornado' => 'boolean',
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
     * Relacionamento com Usuário que registrou
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Usuário que estornou
     */
    public function usuarioEstorno()
    {
        return $this->belongsTo(User::class, 'usuario_estorno_id');
    }

    /**
     * Relacionamento com Métodos de Pagamento utilizados
     */
    public function metodos()
    {
        return $this->hasMany(PagamentoMetodo::class);
    }

    /**
     * Relacionamento com Movimentações de Crédito relacionadas
     */
    public function movimentacoesCredito()
    {
        return $this->hasMany(ClienteCreditoMovimentacoes::class, 'referencia_id')
            ->where('referencia_tipo', 'pagamento');
    }

    /**
     * Scope para pagamentos não estornados
     */
    public function scopeAtivos($query)
    {
        return $query->where('estornado', false);
    }

    /**
     * Scope para pagamentos estornados
     */
    public function scopeEstornados($query)
    {
        return $query->where('estornado', true);
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
     * Verifica se o pagamento utilizou créditos
     */
    public function utilizouCreditos()
    {
        return $this->metodos()->where('usa_credito', true)->exists();
    }

    /**
     * Obtém o valor total pago com créditos
     */
    public function getValorCreditosAttribute()
    {
        return $this->metodos()->where('usa_credito', true)->sum('valor');
    }

    /**
     * Obtém o valor total pago com outros métodos
     */
    public function getValorOutrosMetodosAttribute()
    {
        return $this->metodos()->where('usa_credito', false)->sum('valor');
    }

    /**
     * Accessor para formatar o valor final
     */
    public function getValorFinalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_final, 2, ',', '.');
    }

    /**
     * Accessor para formatar o valor pago
     */
    public function getValorPagoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_pago, 2, ',', '.');
    }

    /**
     * Accessor para formatar o troco
     */
    public function getTrocoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->troco, 2, ',', '.');
    }

    /**
     * Obtém o tipo de registro (orçamento ou pedido)
     */
    public function getTipoRegistroAttribute()
    {
        return $this->orcamento_id ? 'orcamento' : 'pedido';
    }

    /**
     * Obtém o número do registro
     */
    public function getNumeroRegistroAttribute()
    {
        return $this->orcamento_id ?? $this->pedido_id;
    }

    /**
     * Obtém o registro completo (orçamento ou pedido)
     */
    public function getRegistroAttribute()
    {
        return $this->orcamento ?? $this->pedido;
    }
}