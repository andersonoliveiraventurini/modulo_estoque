<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'mensagem',
        'produto_id',
        'orcamento_id',
        'lida',
    ];

    protected $casts = [
        'lida' => 'boolean',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
