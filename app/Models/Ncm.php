<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ncm extends Model
{
    /** @use HasFactory<\Database\Factories\NcmFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'descricao',
        'data_inicio',
        'data_fim',
        'ato_legal',
        'numero',
        'ano'
    ];
    
}
