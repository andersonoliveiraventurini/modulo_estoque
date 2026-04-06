<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'is_perishable',
    ];

    protected $casts = [
        'is_perishable' => 'boolean',
        'ativo' => 'boolean',
        'liberar_desconto' => 'boolean',
    ];

    /**
     * Quantidade disponível para novos orçamentos:
     * estoque_atual menos a soma das reservas ativas na tabela estoque_reservas.
     */
    public function getEstoqueDisponivelAttribute(): int
    {
        $reservado = (int) \App\Models\EstoqueReserva::where('produto_id', $this->id)
            ->where('status', 'ativa')
            ->sum('quantidade');

        $minimo = (int) ($this->estoque_minimo ?? 0);

        return (int) max(0, $this->estoque_atual - $reservado - $minimo);
    }

    public function addEstoque($quantidade)
    {
        $this->increment('estoque_atual', $quantidade);
    }

    public function removerEstoque($quantidade)
    {
        $this->decrement('estoque_atual', $quantidade);
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoProduto::class);
    }

    public function images()
    {
        return $this->hasMany(Imagem::class, 'produto_id');
    }

    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cor_id');
    }
    
}
