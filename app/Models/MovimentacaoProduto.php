<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoProduto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacao_produtos';

    protected $fillable = [
        'movimentacao_id',
        'produto_id',
        'armazem_id',
        'corredor_id',
        'posicao_id',
        'fornecedor_id',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'endereco',
        'corredor',
        'posicao',
        'observacao',
        'data_vencimento',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'quantidade' => 'integer',
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

    public function armazem()
    {
        return $this->belongsTo(Armazem::class);
    }

    public function corredor()
    {
        return $this->belongsTo(Corredor::class);
    }

    public function posicao()
    {
        return $this->belongsTo(Posicao::class);
    }
}
