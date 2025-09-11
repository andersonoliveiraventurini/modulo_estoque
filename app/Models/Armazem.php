<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Armazem extends Model
{
    /** @use HasFactory<\Database\Factories\ArmazemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'armazens';
}
