<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcaoDeletar extends Model
{
    /** @use HasFactory<\Database\Factories\AcaoDeletarFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'descricao',
        'user_id',
    ];

    protected $table = 'acao_deletar';
}
