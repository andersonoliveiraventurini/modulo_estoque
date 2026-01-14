<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Desconto extends Model
{
    /** @use HasFactory<\Database\Factories\DescontoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'motivo',
        'valor',
        'tipo',
        'cliente_id',
        'orcamento_id',
        'pedido_id',
        'user_id',
        'porcentagem',
        'aprovado_em',
        'aprovado_por',
        'justificativa_aprovacao',
        'rejeitado_em',
        'rejeitado_por',
        'justificativa_rejeicao',
        'observacao'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'porcentagem' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Orçamento associado ao desconto
     */
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    /**
     * Pedido associado ao desconto
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Usuário que aplicou o desconto
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o usuário que aprovou
     */
    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    /**
     * Relacionamento com o usuário que rejeitou
     */
    public function rejeitadoPor()
    {
        return $this->belongsTo(User::class, 'rejeitado_por');
    }

    /**
     * Scope para descontos pendentes
     */
    public function scopePendentes($query)
    {
        return $query->whereNull('aprovado_em')
            ->whereNull('rejeitado_em');
    }

    /**
     * Scope para descontos aprovados
     */
    public function scopeAprovados($query)
    {
        return $query->whereNotNull('aprovado_em');
    }

    /**
     * Scope para descontos rejeitados
     */
    public function scopeRejeitados($query)
    {
        return $query->whereNotNull('rejeitado_em');
    }

    /**
     * Verifica se o desconto está aprovado
     */
    public function isAprovado()
    {
        return !is_null($this->aprovado_em);
    }

    /**
     * Verifica se o desconto está rejeitado
     */
    public function isRejeitado()
    {
        return !is_null($this->rejeitado_em);
    }

    /**
     * Verifica se o desconto está pendente
     */
    public function isPendente()
    {
        return is_null($this->aprovado_em) && is_null($this->rejeitado_em);
    }

    /**
     * Retorna o status do desconto
     */
    public function getStatusAttribute()
    {
        if ($this->isAprovado()) {
            return 'aprovado';
        }

        if ($this->isRejeitado()) {
            return 'rejeitado';
        }

        return 'pendente';
    }

    /**
     * Retorna a cor do badge de status
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'aprovado' => 'green',
            'rejeitado' => 'red',
            'pendente' => 'yellow',
            default => 'gray',
        };
    }

    // Accessors para formatação
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getPorcentagemFormatadaAttribute()
    {
        return $this->porcentagem ? number_format($this->porcentagem, 2, ',', '.') . '%' : null;
    }

    public function getTipoLabelAttribute()
    {
        return $this->tipo === 'fixo' ? 'Fixo' : 'Percentual';
    }

    // Scopes úteis
    public function scopeFixo($query)
    {
        return $query->where('tipo', 'fixo');
    }

    public function scopePercentual($query)
    {
        return $query->where('tipo', 'percentual');
    }

    public function scopeDoCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopeDoOrcamento($query, $orcamentoId)
    {
        return $query->where('orcamento_id', $orcamentoId);
    }

    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }
}
