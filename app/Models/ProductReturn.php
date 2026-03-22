<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'nr',
        'orcamento_id',
        'cliente_id',
        'vendedor_id',
        'usuario_id',
        'status',
        'troca_produto',
        'data_ocorrencia',
        'nota_fiscal',
        'romaneio_recebimento',
        'observacoes',
        'observacoes_estoque',
        'valor_total_credito',
        'finalizado_at',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
        'troca_produto' => 'boolean',
        'finalizado_at' => 'datetime',
        'valor_total_credito' => 'decimal:2',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function authorizations()
    {
        return $this->hasMany(ReturnAuthorization::class, 'return_id');
    }

    public function credits()
    {
        return $this->hasMany(ClientCredit::class, 'return_id');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pendente_supervisor' => 'Pendente Supervisor',
            'pendente_estoque' => 'Pendente Estoque',
            'finalizado' => 'Finalizado',
            'negado' => 'Negado',
            'em_troca' => 'Em Troca',
            default => $this->status,
        };
    }
}
