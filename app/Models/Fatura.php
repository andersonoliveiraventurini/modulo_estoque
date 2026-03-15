<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fatura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'orcamento_id',
        'pedido_id',
        'valor_total',
        'valor_pago',
        'numero_parcela',
        'total_parcelas',
        'data_vencimento',
        'data_pagamento',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'datetime',
        'valor_total' => 'decimal:2',
        'valor_pago' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    public function isAtrasada(): bool
    {
        return $this->status !== 'pago' && \Carbon\Carbon::parse($this->data_vencimento)->isPast();
    }
}
