<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrcamentoItens extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoItensFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id', 'produto_id', 'quantidade',
        'valor_unitario', 'desconto', 'valor_com_desconto', 'user_id'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function usuarioDesconto()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
