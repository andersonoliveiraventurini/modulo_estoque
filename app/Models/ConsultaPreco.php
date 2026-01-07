<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultaPreco extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultaPrecoFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'consulta_precos';

    protected $fillable = [
        'status',
        'descricao',
        'cor_id',
        'quantidade',
        'usuario_id',
        'cliente_id',
        'preco_compra',
        'preco_venda',
        'observacao',
        'fornecedor_id',
        'comprador_id',
        'prazo_entrega',
        'pdf_path',
        'part_number'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function setPrecoCompraAttribute($value)
    {
        $this->attributes['preco_compra'] = str_replace(',', '.', $value);
    }

    public function setPrecoVendaAttribute($value)
    { {
            $this->attributes['preco_venda'] = str_replace(',', '.', $value);
        }
    }
    
    public function cor()
    {
        return $this->belongsTo(Cor::class, 'cor_id');
    }
    
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

}
