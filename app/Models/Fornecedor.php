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
    ];

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
}
