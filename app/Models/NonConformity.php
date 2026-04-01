<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonConformity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nr',
        'produto_id',
        'produto_nome',
        'quantidade',
        'baixar_estoque',
        'armazem_id',
        'fornecedor_id',
        'fornecedor_nome',
        'data_ocorrencia',
        'nota_fiscal',
        'romaneio_recebimento',
        'acoes_tomadas',
        'observacoes',
        'usuario_id',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
        'baixar_estoque' => 'boolean',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function armazem()
    {
        return $this->belongsTo(Armazem::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
