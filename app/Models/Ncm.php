<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ncm extends Model
{
    /** @use HasFactory<\Database\Factories\NcmFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'descricao',
        'data_inicio',
        'data_fim',
        'ato_legal',
        'numero',
        'ano'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        // outros campos date se houver
    ];

    // E um accessor para formatação
    public function getDataInicioFormatadaAttribute()
    {
        if ($this->data_inicio === null) {
            return null;
        } else {
            return $this->data_inicio->format('d/m/Y');
        }
    }

    public function getDataFimFormatadaAttribute()
    {
        if ($this->data_fim === null) {
            return null;
        } else {
            return $this->data_fim->format('d/m/Y');
        }
    }
    
}
