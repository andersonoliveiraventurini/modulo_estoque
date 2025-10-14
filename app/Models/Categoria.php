<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'descricao'
    ];

    public function subcategorias()
    {
        return $this->hasMany(SubCategoria::class, 'categoria_id');
    }
}
