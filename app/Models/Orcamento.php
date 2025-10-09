<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'endereco_id',
        'obra',
        'frete',
        'valor_total_itens',
        'guia_recolhimento',
        'status',
        'observacoes',
        'validade',
        'pdf_path',
        'prazo_entrega',
        'token_acesso',
        'token_expira_em'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function itens()
    {
        return $this->hasMany(OrcamentoItens::class);
    }

    public function vidros()
    {
        return $this->hasMany(OrcamentoVidro::class);
    }

    public function descontos()
    {
        return $this->hasMany(Desconto::class);
    }
    
    public function transportes()
    {
        return $this->belongsToMany(TipoTransporte::class, 'orcamento_transportes');
    }
}
