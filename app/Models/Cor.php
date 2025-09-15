<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cor extends Model
{
    /** @use HasFactory<\Database\Factories\CorFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'nome',
        'codigo_hex'
    ];

    protected $table = 'cores';
}
