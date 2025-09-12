<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contato extends Model
{
    /** @use HasFactory<\Database\Factories\ContatoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cliente_id',
        'fornecedor_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
