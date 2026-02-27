<?php

// ============================================================
// MODEL: ConsultaPrecoGrupo
// app/Models/ConsultaPrecoGrupo.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ConsultaPrecoGrupo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consulta_preco_grupos';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'orcamento_id',
        'status',
        'validade',
        'observacao',
    ];

    protected $casts = [
        'validade' => 'datetime',
    ];

    // ── Relacionamentos ──────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function itens()
    {
        return $this->hasMany(ConsultaPreco::class, 'grupo_id');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function estaExpirado(): bool
    {
        return $this->validade && $this->validade->isPast();
    }

    public function todosItensDisponiveis(): bool
    {
        // Todos os itens precisam ter fornecedor selecionado
        return $this->itens()->whereDoesntHave('fornecedorSelecionado')->doesntExist();
    }
    /**
     * Marca o grupo como Disponível e inicia o prazo de 48h.
     */
    public function marcarDisponivel(): void
    {
        $this->update([
            'status'   => 'Disponível',
            'validade' => Carbon::now()->addHours(48),
        ]);
    }

    /**
     * Verifica e expira o grupo se passou do prazo.
     */
    public function verificarExpiracao(): void
    {
        if ($this->status === 'Disponível' && $this->estaExpirado()) {
            $this->update(['status' => 'Expirado']);
        }
    }
}
