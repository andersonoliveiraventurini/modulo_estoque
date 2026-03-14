<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Corredor extends Model
{
    protected $fillable = ['armazem_id', 'nome'];

    public function armazem()
    {
        return $this->belongsTo(Armazem::class);
    }

    public function posicoes()
    {
        return $this->hasMany(Posicao::class);
    }
}
