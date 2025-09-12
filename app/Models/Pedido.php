<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoFactory> */
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'cliente_id', 'vendedor_id', 'vendedor_externo_id',
        'endereco_id', 'obra', 'valor_total', 'status', 'observacoes'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function vendedorExterno()
    {
        return $this->belongsTo(User::class, 'vendedor_externo_id');
    }
}
