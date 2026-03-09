<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaEncomendaItem extends Model
{
    protected $table = 'entrada_encomenda_itens';

    protected $fillable = [
        'entrada_id',
        'consulta_preco_id',
        'quantidade_solicitada',
        'quantidade_recebida',
        'recebido_completo',
        'observacao',
    ];

    protected $casts = [
        'recebido_completo' => 'boolean',
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(EntradaEncomenda::class, 'entrada_id');
    }

    public function consultaPreco(): BelongsTo
    {
        return $this->belongsTo(ConsultaPreco::class, 'consulta_preco_id');
    }

    /** Quantidade ainda pendente de recebimento */
    public function quantidadePendente(): float
    {
        return max(0, (float) $this->quantidade_solicitada - (float) $this->quantidade_recebida);
    }
}