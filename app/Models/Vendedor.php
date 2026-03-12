<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendedores';

    protected $fillable = [
        'user_id',
        'desconto',
        'externo',
    ];

    // -------------------------------------------------
    // Constantes para os tipos
    // -------------------------------------------------
    const INTERNO    = 0;
    const EXTERNO    = 1;
    const ASSISTENTE = 2;

    // -------------------------------------------------
    // Scopes
    // -------------------------------------------------
    public function scopeInternos($query)
    {
        return $query->where('externo', self::INTERNO);
    }

    public function scopeExternos($query)
    {
        return $query->where('externo', self::EXTERNO);
    }

    public function scopeAssistentes($query)
    {
        return $query->where('externo', self::ASSISTENTE);
    }

    // -------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'vendedor_id');
    }

    public function clientesExternos()
    {
        return $this->hasMany(Cliente::class, 'vendedor_externo_id');
    }

    public function clientesAssistidos()
    {
        return $this->hasMany(Cliente::class, 'vendedor_assistente_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'vendedor_id');
    }

    public function pedidosExternos()
    {
        return $this->hasMany(Pedido::class, 'vendedor_externo_id');
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class, 'vendedor_id');
    }

    // -------------------------------------------------
    // Helpers
    // -------------------------------------------------
    public function getTipoLabelAttribute(): string
    {
        return match((int) $this->externo) {
            self::INTERNO    => 'Interno',
            self::EXTERNO    => 'Externo',
            self::ASSISTENTE => 'Assistente',
            default          => 'Desconhecido',
        };
    }
}