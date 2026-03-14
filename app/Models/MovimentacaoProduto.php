<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoProduto extends Model
{
    use HasFactory;

    protected $table = 'movimentacao_produtos';

    protected $fillable = [
        'movimentacao_id',
        'produto_id',
        'fornecedor_id',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'endereco',
        'corredor',
        'posicao',
        'observacao',
    ];

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
