<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimentacao extends Model
{
    /** @use HasFactory<\Database\Factories\MovimentacaoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'tipo',
        'data_movimentacao',
        'pedido_compra_id',
        'usuario_id',
        'nota_fiscal_fornecedor',
        'arquivo_nota_fiscal',
        'romaneiro',
        'observacao',
        'resumo_edicao',
        'usuario_editou_id',
    ];

    protected $casts = [
        'data_movimentacao' => 'date',
    ];


    public function itens()
    {
        return $this->hasMany(MovimentacaoProduto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function usuarioEditou()
    {
        return $this->belongsTo(User::class, 'usuario_editou_id');
    }
}
