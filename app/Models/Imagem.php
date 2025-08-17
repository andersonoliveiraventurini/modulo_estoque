<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Imagem extends Model
{
    /** @use HasFactory<\Database\Factories\ImagemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'imagens';
}
