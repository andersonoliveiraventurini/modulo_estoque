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
        'porcentagem'
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
