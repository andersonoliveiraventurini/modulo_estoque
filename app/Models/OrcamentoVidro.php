<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrcamentoVidro extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoVidroFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'orcamento_vidros';

    protected $fillable = [
        'orcamento_id',
        'descricao',
        'quantidade',
        'altura',
        'largura',
        'preco_metro_quadrado',
        'desconto',
        'valor_total',
        'valor_com_desconto',
        'user_id',
    ];
    
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function usuarioDesconto()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
