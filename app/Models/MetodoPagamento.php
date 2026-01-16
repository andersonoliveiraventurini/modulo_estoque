<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetodoPagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'metodos_pagamento';

    protected $fillable = [
        'nome',
        'codigo',
        'tipo',
        'permite_parcelamento',
        'max_parcelas',
        'ativo',
        'ordem',
        'observacoes',
    ];

    protected $casts = [
        'permite_parcelamento' => 'boolean',
        'ativo' => 'boolean',
        'max_parcelas' => 'integer',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com Pagamentos que usaram este método
     */
    public function pagamentoMetodos()
    {
        return $this->hasMany(PagamentoMetodo::class);
    }

    /**
     * Scope para métodos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para métodos que permitem parcelamento
     */
    public function scopeParcelaveis($query)
    {
        return $query->where('permite_parcelamento', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Verifica se é crédito do cliente
     */
    public function isCreditoCliente()
    {
        return $this->tipo === 'credito_cliente';
    }

    /**
     * Obtém a descrição do tipo
     */
    public function getTipoDescricaoAttribute()
    {
        $tipos = [
            'dinheiro' => 'Dinheiro',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'pix' => 'PIX',
            'boleto1' => 'Boleto 07 dias',
            'boleto2' => 'Boleto 14 dias',
            'boleto3' => 'Boleto 21 dias',
            'boleto4' => 'Boleto 28 dias',
            'boleto5' => 'Boleto 28/56 dias',
            'boleto6' => 'Boleto 28/42/56 dias',
            'boleto7' => 'Boleto 28/56/84 dias',
            'cheque1' => 'Cheque 07 dias',
            'cheque2' => 'Cheque 14 dias',
            'cheque3' => 'Cheque 21 dias',
            'cheque4' => 'Cheque 28 dias',
            'cheque5' => 'Cheque 28/56 dias',
            'cheque6' => 'Cheque 28/42/56 dias',
            'cheque7' => 'Cheque 28/56/84 dias',
            'transferencia' => 'Transferência',
            'credito_cliente' => 'Crédito do Cliente',
            'outros' => 'Outros',
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }

    /**
     * Obtém o ícone do método
     */
    public function getIconeAttribute()
    {
        $icones = [
            'dinheiro' => 'currency-dollar',
            'cartao_credito' => 'credit-card',
            'cartao_debito' => 'credit-card',
            'pix' => 'qrcode',
            'boleto' => 'document-text',
            'transferencia' => 'arrows-right-left',
            'credito_cliente' => 'gift',
            'outro' => 'ellipsis-horizontal',
        ];

        return $icones[$this->tipo] ?? 'ellipsis-horizontal';
    }
}