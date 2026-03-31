<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Posicao extends Model {
    use SoftDeletes;
    protected $table = 'posicoes';
    protected $fillable = ['corredor_id', 'nome', 'descricao'];

    public function corredor()
    {
        return $this->belongsTo(Corredor::class);
    }

    public function getNomeCompletoAttribute()
    {
        return ($this->corredor?->armazem?->nome ?? '?') . ' - ' . ($this->corredor?->nome ?? '?') . ' - ' . $this->nome;
    }
}
