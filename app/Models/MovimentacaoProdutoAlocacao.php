<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimentacaoProdutoAlocacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacao_produto_alocacoes';

    protected $fillable = [
        'movimentacao_produto_id',
        'posicao_id',
        'quantidade',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
    ];

    public function item()
    {
        return $this->belongsTo(MovimentacaoProduto::class, 'movimentacao_produto_id');
    }

    public function posicao()
    {
        return $this->belongsTo(Posicao::class);
    }
}
