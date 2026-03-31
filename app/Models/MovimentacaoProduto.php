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
        'wt_code',
        'cor',
        'codigo_fornecedor',
        'armazem_id',
        'corredor_id',
        'posicao_id',
        'fornecedor_id',
        'quantidade',
        'quantidade_vendida',
        'valor_unitario',
        'valor_total',
        'endereco',
        'corredor',
        'posicao',
        'observacao',
        'data_vencimento',
        'is_encomenda',
        'numero_pedido',
        'vendedor_id',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'quantidade' => 'integer',
        'quantidade_vendida' => 'decimal:3',
        'is_encomenda' => 'boolean',
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

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function alocacoes()
    {
        return $this->hasMany(MovimentacaoProdutoAlocacao::class, 'movimentacao_produto_id');
    }
}
