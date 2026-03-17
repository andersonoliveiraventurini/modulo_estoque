<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlocokDescartes extends Model
{
    /** @use HasFactory<\Database\Factories\BlocokDescartesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'bloco_k_descartes';

    protected $fillable = [
        'produto_id',
        'produto_descartado_id',
        'quantidade_descarte',
        'unidade_medida_descarte',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function produtoDescartado()
    {
        return $this->belongsTo(Produto::class, 'produto_descartado_id');
    }
}
