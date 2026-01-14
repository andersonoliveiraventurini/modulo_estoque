<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tipo',
        'titulo',
        'descricao',
        'caminho_arquivo',
        'user_id',
        'cliente_id',
        'fornecedor_id',
    ];
}
