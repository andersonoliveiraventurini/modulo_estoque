<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultaPreco extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultaPrecoFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'consulta_precos';

    protected $fillable = [
        'status',
        'descricao',
        'cor',
        'quantidade',
        'usuario_id',
        'orcamento_id',
        'preco_compra',
        'preco_venda',
        'observacao',
        'fornecedor_id',
        'comprador_id',
    ];
}


