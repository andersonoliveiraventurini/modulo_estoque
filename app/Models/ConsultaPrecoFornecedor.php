<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaPrecoFornecedor extends Model
{
    protected $table = 'consulta_preco_fornecedores';

    protected $fillable = [
        'consulta_preco_id',
        'fornecedor_id',
        'preco_compra',
        'preco_venda',
        'prazo_entrega',
        'selecionado',
        'observacao',
    ];

    protected $casts = [
        'selecionado' => 'boolean',
    ];

    public function consultaPreco()
    {
        return $this->belongsTo(ConsultaPreco::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
