<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteCreditoMovimentacoes extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteCreditoMovimentacoesFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'cliente_credito_movimentacoes';
    protected $fillable = [
        'credito_id',
        'cliente_id',
        'tipo_movimentacao',
        'valor_movimentado',
        'saldo_anterior',
        'saldo_posterior',
        'motivo',
        'referencia_tipo',
        'referencia_id',
        'usuario_id',
        'credito_troco_gerado_id',
    ];

    protected $casts = [
        'valor_movimentado' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_posterior' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Crédito
     */
    public function credito()
    {
        return $this->belongsTo(ClienteCreditos::class, 'credito_id');
    }

    /**
     * Relacionamento com Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com Usuário que realizou a movimentação
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Crédito de Troco Gerado (se aplicável)
     */
    public function creditoTrocoGerado()
    {
        return $this->belongsTo(ClienteCreditos::class, 'credito_troco_gerado_id');
    }

    /**
     * Obtém a descrição do tipo de movimentação
     */
    public function getTipoMovimentacaoDescricaoAttribute()
    {
        $tipos = [
            'utilizacao' => 'Utilização',
            'estorno' => 'Estorno',
            'expiracao' => 'Expiração',
            'cancelamento' => 'Cancelamento',
            'geracao_troco' => 'Geração de Crédito',
        ];

        return $tipos[$this->tipo_movimentacao] ?? $this->tipo_movimentacao;
    }

    /**
     * Obtém a cor do tipo de movimentação
     */
    public function getTipoMovimentacaoCorAttribute()
    {
        $cores = [
            'utilizacao' => 'red',
            'estorno' => 'green',
            'expiracao' => 'orange',
            'cancelamento' => 'red',
            'geracao_troco' => 'green',
        ];

        return $cores[$this->tipo_movimentacao] ?? 'gray';
    }

    /**
     * Obtém o ícone do tipo de movimentação
     */
    public function getTipoMovimentacaoIconeAttribute()
    {
        $icones = [
            'utilizacao' => 'arrow-down',
            'estorno' => 'arrow-up',
            'expiracao' => 'clock',
            'cancelamento' => 'x-circle',
            'geracao_troco' => 'plus-circle',
        ];

        return $icones[$this->tipo_movimentacao] ?? 'circle';
    }

    /**
     * Verifica se é uma movimentação de saída (diminui saldo)
     */
    public function isSaida()
    {
        return in_array($this->tipo_movimentacao, ['utilizacao', 'cancelamento', 'expiracao']);
    }

    /**
     * Verifica se é uma movimentação de entrada (aumenta saldo)
     */
    public function isEntrada()
    {
        return in_array($this->tipo_movimentacao, ['estorno', 'geracao_troco']);
    }
}
