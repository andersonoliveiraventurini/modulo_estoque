<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cpf',
        'cnpj',
        'nome',
        'nome_fantasia',
        'razao_social',
        'tratamento',
        'status',
        'email',
        'telefone' // novos campos
    ];
    
    protected $casts = [
        'data_abertura' => 'date',
        // outros campos date se houver
    ];

    // E um accessor para formatação
    public function getDataAberturaFormatadaAttribute()
    {
        return $this->data_abertura?->format('d/m/Y');
    }
}
