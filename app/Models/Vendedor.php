<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendedor extends Model
{
    /** @use HasFactory<\Database\Factories\VendedorFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'vendedores';

    protected $fillable = [
        'user_id',
        'desconto',
        'externo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'vendedor_id');
    }

    public function clientesExternos()
    {
        return $this->hasMany(Cliente::class, 'vendedor_externo_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'vendedor_id');
    }

    public function pedidosExternos()
    {
        return $this->hasMany(Pedido::class, 'vendedor_externo_id');
    }
}
