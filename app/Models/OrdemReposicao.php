<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemReposicao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordens_reposicao';

    protected $fillable = [
        'produto_id',
        'quantidade_solicitada',
        'status',
        'solicitado_por_id',
        'executor_id',
        'armazem_origem_id',
        'corredor_origem_id',
        'posicao_origem_id',
        'impresso_em',
        'concluido_em',
    ];

    protected $casts = [
        'impresso_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function armazemOrigem()
    {
        return $this->belongsTo(Armazem::class, 'armazem_origem_id');
    }

    public function corredorOrigem()
    {
        return $this->belongsTo(Corredor::class, 'corredor_origem_id');
    }

    public function posicaoOrigem()
    {
        return $this->belongsTo(Posicao::class, 'posicao_origem_id');
    }
}
