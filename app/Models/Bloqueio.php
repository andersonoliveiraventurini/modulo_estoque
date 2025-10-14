<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bloqueio extends Model
{
    /** @use HasFactory<\Database\Factories\BloqueioFactory> */
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'cliente_id',
        'motivo',
        'user_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
