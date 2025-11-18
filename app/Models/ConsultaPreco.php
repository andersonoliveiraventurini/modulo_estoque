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
        'preco_compra',
        'preco_venda',
        'observacao',
        'fornecedor_id',
        'comprador_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    
    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }
}


