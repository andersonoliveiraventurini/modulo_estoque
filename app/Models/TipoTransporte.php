<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTransporte extends Model
{
    /** @use HasFactory<\Database\Factories\TipoTransporteFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_transportes';

    protected $fillable = ['nome'];

    public function orcamentos()
    {
        return $this->belongsToMany(Orcamento::class, 'orcamento_transportes');
    }
}
