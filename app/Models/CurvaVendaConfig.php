<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurvaVendaConfig extends Model
{
    protected $table = 'curva_vendas_configs';

    protected $fillable = [
        'periodo_inicio',
        'periodo_fim',
        'parametros',
    ];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fim' => 'date',
        'parametros' => 'array',
    ];
}
