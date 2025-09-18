<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategoria extends Model
{
    /** @use HasFactory<\Database\Factories\SubCategoriaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'sub_categorias';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria_id'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
