<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    /** @use HasFactory<\Database\Factories\EnderecoFactory> */
    use HasFactory, SoftDeletes;
}
