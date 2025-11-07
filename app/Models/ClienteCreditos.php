<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteCreditos extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteCreditosFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'cliente_creditos';
    protected $fillable = [
        'cliente_id',
        'valor_original',
        'valor_disponivel',
        'data_validade',
        'status',
        'criado_por_id',
        'tipo',
        'motivo_origem',
        'origem_tipo',
        'origem_id',
        'usuario_criacao_id'
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_disponivel' => 'decimal:2',
        'data_validade' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com Usuário que criou o crédito
     */
    public function usuarioCriacao()
    {
        return $this->belongsTo(User::class, 'usuario_criacao_id');
    }

    /**
     * Relacionamento com Movimentações
     */
    public function movimentacoes()
    {
        return $this->hasMany(ClienteCreditoMovimentacoes::class, 'credito_id');
    }

    /**
     * Scope para créditos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0);
    }

    /**
     * Scope para créditos válidos (não expirados)
     */
    public function scopeValidos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('data_validade')
                ->orWhere('data_validade', '>=', now());
        });
    }

    /**
     * Scope para créditos disponíveis (ativos e válidos)
     */
    public function scopeDisponiveis($query)
    {
        return $query->ativos()->validos();
    }

    /**
     * Verifica se o crédito está expirado
     */
    public function isExpirado()
    {
        if (!$this->data_validade) {
            return false;
        }

        return $this->data_validade < now();
    }

    /**
     * Verifica se o crédito está disponível para uso
     */
    public function isDisponivel()
    {
        return $this->status === 'ativo'
            && $this->valor_disponivel > 0
            && !$this->isExpirado();
    }

    /**
     * Obtém o percentual utilizado do crédito
     */
    public function getPercentualUtilizadoAttribute()
    {
        if ($this->valor_original == 0) {
            return 0;
        }

        $valorUtilizado = $this->valor_original - $this->valor_disponivel;
        return ($valorUtilizado / $this->valor_original) * 100;
    }

    /**
     * Obtém a descrição do tipo de crédito
     */
    public function getTipoDescricaoAttribute()
    {
        $tipos = [
            'devolucao' => 'Devolução',
            'troco' => 'Troco',
            'bonificacao' => 'Bonificação',
            'ajuste' => 'Ajuste',
            'outro' => 'Outro',
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }

    /**
     * Obtém a descrição do status
     */
    public function getStatusDescricaoAttribute()
    {
        $status = [
            'ativo' => 'Ativo',
            'utilizado' => 'Utilizado',
            'expirado' => 'Expirado',
            'cancelado' => 'Cancelado',
        ];

        return $status[$this->status] ?? $this->status;
    }

    /**
     * Obtém a cor do badge de status
     */
    public function getStatusCorAttribute()
    {
        $cores = [
            'ativo' => 'green',
            'utilizado' => 'gray',
            'expirado' => 'red',
            'cancelado' => 'red',
        ];

        return $cores[$this->status] ?? 'gray';
    }
}
