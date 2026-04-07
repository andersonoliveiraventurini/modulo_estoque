<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurvaVendaAuditoria extends Model
{
    protected $table = 'curva_vendas_auditoria';

    protected $fillable = [
        'produto_id',
        'user_id',
        'de',
        'para',
        'justificativa',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
