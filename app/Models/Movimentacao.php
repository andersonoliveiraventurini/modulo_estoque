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
        'status',
        'data_movimentacao',
        'pedido_id',
        'pedido_compra_id',
        'nota_fiscal_fornecedor',
        'arquivo_nota_fiscal',
        'romaneiro',
        'observacao',
        'usuario_id',
        'usuario_editou_id',
        'supervisor_id',
        'aprovado_em',
        'is_reposicao',
        'is_devolucao',
    ];

    protected $casts = [
        'data_movimentacao' => 'date',
        'aprovado_em' => 'datetime',
        'is_reposicao' => 'boolean',
        'is_devolucao' => 'boolean',
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
