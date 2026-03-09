<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultaPrecoFornecedor extends Model
{
    use softDeletes;
    protected $table = 'consulta_preco_fornecedores';

    protected $fillable = [
        'consulta_preco_id',
        'fornecedor_id',
        'preco_compra',
        'preco_venda',
        'prazo_entrega',
        'selecionado',
        'observacao',
        'comprador_id', 
    ];

    protected $casts = [
        'selecionado' => 'boolean',
        'preco_compra'  => 'decimal:2',
        'preco_venda'   => 'decimal:2',
    ];

    public function consultaPreco()
    {
        return $this->belongsTo(ConsultaPreco::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

     public function comprador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'comprador_id');
    }
}
