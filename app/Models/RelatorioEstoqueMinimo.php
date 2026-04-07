<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelatorioEstoqueMinimo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'relatorios_estoque_minimo';

    protected $fillable = [
        'codigo',
        'user_id',
        'parametros',
        'status',
        'total_itens',
    ];

    protected $casts = [
        'parametros' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
