<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Corredor extends Model {
    use SoftDeletes;

    protected $table = 'corredores';
    protected $fillable = ['armazem_id', 'nome', 'descricao'];

    public function armazem()
    {
        return $this->belongsTo(Armazem::class);
    }

    public function posicoes()
    {
        return $this->hasMany(Posicao::class);
    }
}
