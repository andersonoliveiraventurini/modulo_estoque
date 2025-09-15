<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    /** @use HasFactory<\Database\Factories\EnderecoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'tipo',
        'cliente_id',
        'fornecedor_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

}
