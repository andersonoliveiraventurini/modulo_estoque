<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    /** @use HasFactory<\Database\Factories\FornecedorFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'tratamento',
        'cnpj',
        'inscricao_estadual',
        'inscricao_municipal',
        'data_abertura',
        'cnae_principal',
        'inscricao_estadual',
        'inscricao_municipal',
        'regime_tributario',
        'beneficio',
        'certidoes_negativas',
        'certificacoes_qualidade',
        'status'
    ];

    protected $casts = [
        'data_abertura' => 'date',
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

    public function contatos()
    {
        return $this->hasMany(Contato::class);
    }

    public function endereco()
    {
        return $this->hasOne(Endereco::class);
    }
}
