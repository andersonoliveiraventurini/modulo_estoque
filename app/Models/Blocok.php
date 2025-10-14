<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blocok extends Model
{
    /** @use HasFactory<\Database\Factories\BlocokFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'bloco_k';
}
