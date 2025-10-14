<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Desconto extends Model
{
    /** @use HasFactory<\Database\Factories\DescontoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'motivo',
        'valor',
        'tipo',
        'cliente_id',
        'orcamento_id',
        'pedido_id',
        'user_id',
        'porcentagem'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Orçamento associado ao desconto
     */
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    /**
     * Pedido associado ao desconto
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Usuário que aplicou o desconto
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
