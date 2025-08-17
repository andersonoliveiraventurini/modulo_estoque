<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Telefone extends Model
{
    /** @use HasFactory<\Database\Factories\TelefoneFactory> */
    use HasFactory, SoftDeletes;
}
