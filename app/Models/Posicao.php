<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Posicao extends Model {
    use SoftDeletes;
    protected $fillable = ['corredor_id', 'nome'];

    public function corredor()
    {
        return $this->belongsTo(Corredor::class);
    }
}
