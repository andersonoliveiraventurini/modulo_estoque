<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitacaoPagamento extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitacaoPagamentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'descricao_pagamento',
        'justificativa_solicitacao',
        'numero_parcelas',
        'valor_entrada',
        'data_primeiro_vencimento',
        'intervalo_dias',
        'solicitado_por',
        'aprovado_em',
        'aprovado_por',
        'justificativa_aprovacao',
        'rejeitado_em',
        'rejeitado_por',
        'justificativa_rejeicao',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'aprovado_em' => 'datetime',
        'rejeitado_em' => 'datetime',
        'data_primeiro_vencimento' => 'date',
        'valor_entrada' => 'decimal:2',
    ];

    // Relacionamentos
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function rejeitador()
    {
        return $this->belongsTo(User::class, 'rejeitado_por');
    }

    // Scopes
    public function scopePendentes($query)
    {
        return $query->where('status', 'Pendente')
                    ->whereNull('aprovado_em')
                    ->whereNull('rejeitado_em');
    }

    public function scopeAprovados($query)
    {
        return $query->where('status', 'Aprovado')
                    ->whereNotNull('aprovado_em');
    }

    public function scopeRejeitados($query)
    {
        return $query->where('status', 'Rejeitado')
                    ->whereNotNull('rejeitado_em');
    }

    // Métodos auxiliares
    public function isPendente()
    {
        return $this->status === 'Pendente' && !$this->aprovado_em && !$this->rejeitado_em;
    }

    public function isAprovado()
    {
        return $this->status === 'Aprovado' && $this->aprovado_em;
    }

    public function isRejeitado()
    {
        return $this->status === 'Rejeitado' && $this->rejeitado_em;
    }

}
