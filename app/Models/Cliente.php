<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cpf',
        'cnpj',
        'nome',
        'nome_fantasia',
        'razao_social',
        'tratamento',
        'data_nascimento',
        'cnae',
        'inscricao_estadual',
        'inscricao_municipal',
        'data_abertura',
        'regime_tributario',
        'vendedor_id',
        'vendedor_externo_id',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        // outros campos date se houver
    ];

    // E um accessor para formatação
    public function getDataAberturaFormatadaAttribute()
    {
        return $this->data_abertura?->format('d/m/Y');
    }

    public function getCnpjFormatadoAttribute()
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj); // remove tudo que não é número
        if (strlen($cnpj) !== 14) {
            return $this->cnpj; // retorna cru se não for válido
        }

        return substr($cnpj, 0, 2) . '.' .
            substr($cnpj, 2, 3) . '.' .
            substr($cnpj, 5, 3) . '/' .
            substr($cnpj, 8, 4) . '-' .
            substr($cnpj, 12, 2);
    }

    public function bloqueios()
    {
        return $this->hasMany(Bloqueio::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function vendedorExterno()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_externo_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    

    public function contatos()
    {
        return $this->hasMany(Contato::class);
    }

    public function enderecos()
    {
        return $this->hasMany(Endereco::class);
    }

}
