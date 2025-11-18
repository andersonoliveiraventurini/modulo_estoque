<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagamentoMetodo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagamento_metodos';

    protected $fillable = [
        'pagamento_id',
        'metodo_pagamento_id',
        'valor',
        'usa_credito',
        'parcelas',
        'valor_parcela',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'usa_credito' => 'boolean',
        'parcelas' => 'integer',
    ];

    /**
     * Relacionamento com Pagamento
     */
    public function pagamento()
    {
        return $this->belongsTo(Pagamento::class);
    }

    /**
     * Relacionamento com Método de Pagamento
     */
    public function metodoPagamento()
    {
        return $this->belongsTo(MetodoPagamento::class, 'metodo_pagamento_id');
    }

    /**
     * Accessor para formatar o valor
     */
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Verifica se o método é parcelado
     */
    public function isParcelado()
    {
        return $this->parcelas > 1;
    }
}