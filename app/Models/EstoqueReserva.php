<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstoqueReserva extends Model
{
    use SoftDeletes;

    protected $fillable = ['orcamento_id','produto_id','quantidade','status','criado_por_id'];

    public function orcamento() { return $this->belongsTo(Orcamento::class); }
    public function produto() { return $this->belongsTo(Produto::class); }
    public function criador() { return $this->belongsTo(User::class, 'criado_por_id'); }
}