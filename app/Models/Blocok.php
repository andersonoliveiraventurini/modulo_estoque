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
    protected $fillable = ['k001', 'k010', 'k100', 'k200', 'k220', 'k230', 'k235', 'k250', 'k255', 'k260', 'k265', 'k270', 'k275', 'k280', 'k990', 'arquivo_path'];
}
