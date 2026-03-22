<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'return_id',
        'orcamento_id',
        'tipo',
        'valor',
        'descricao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productReturn()
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
