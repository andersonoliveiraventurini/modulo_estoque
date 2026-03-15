<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venda extends Model
{
    /** @use HasFactory<\Database\Factories\VendaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'cliente_id',
        'vendedor_id',
        'valor_total',
        'status',
        'data_venda',
    ];

    protected $casts = [
        'data_venda' => 'datetime',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}
