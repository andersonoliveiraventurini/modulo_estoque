<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimentacao extends Model
{
    /** @use HasFactory<\Database\Factories\MovimentacaoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacoes';
}
