<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicoesPagamento extends Model
{
    /** @use HasFactory<\Database\Factories\CondicoesPagamentoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'condicoes_pagamento'; 
    
    protected $fillable = [
        'nome',
    ];
}
