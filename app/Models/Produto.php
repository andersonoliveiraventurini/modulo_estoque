<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    /** @use HasFactory<\Database\Factories\ProdutoFactory> */
    use HasFactory, SoftDeletes;

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    protected $fillable = [
        'id',
        'sku',
        'nome',
        'status',
        'tipo_produto_sped',
        'ncm',
        'liberar_desconto',
        'codigo_barras',
        'fornecedor_id',
        'preco_custo',
        'preco_venda',
        'estoque_minimo',
        'estoque_atual',
        'unidade_medida',
        'marca',
        'modelo',
        'cor_id',
        'peso',
        'descricao',
        'observacoes',
        'imagem_principal',
        'ativo',
    ];

    public function images()
    {
        return $this->hasMany(Imagem::class, 'produto_id');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cor_id');
    }
}
