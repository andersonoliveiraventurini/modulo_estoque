<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnaliseCredito extends Model
{
    /** @use HasFactory<\Database\Factories\AnaliseCreditoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'limite_boleto',
        'limite_carteira',
        'observacoes',
        'user_id'
    ];

    
}
