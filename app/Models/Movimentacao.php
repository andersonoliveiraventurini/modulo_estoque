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
        'pedido_id',
        'usuario_id',
        'nota_fiscal_fornecedor',
        'romaneiro',
        'observacao',
        'resumo_edicao',
        'usuario_editou_id',
    ];


    public function itens()
    {
        return $this->hasMany(MovimentacaoProduto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
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
