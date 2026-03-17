<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cpf',
        'cnpj',
        'nome',
        'nome_fantasia',
        'razao_social',
        'tratamento',
        'data_nascimento',
        'cnae',
        'inscricao_estadual',
        'inscricao_municipal',
        'data_abertura',
        'regime_tributario',
        'vendedor_id',
        'vendedor_externo_id',
        'vendedor_assistente_id',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'data_nascimento' => 'date',
        // outros campos date se houver
    ];

    // E um accessor para formatação
    public function getDataAberturaFormatadaAttribute()
    {
        if ($this->data_abertura === null) {
            return null;
        } else {
            return $this->data_abertura->format('d/m/Y');
        }
    }

    public function getDataNascimentoFormatadaAttribute()
    {
        if ($this->data_nascimento === null) {
            return null;
        } else {
            return $this->data_nascimento->format('d/m/Y');
        }
    }

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

    public function bloqueios()
    {
        return $this->hasMany(Bloqueio::class);
    }

    public function analisesCredito()
    {
        return $this->hasMany(AnaliseCredito::class);
    }

    public function faturas()
    {
        return $this->hasMany(Fatura::class);
    }

    public function ultimoBloqueio()
    {
        return $this->hasOne(Bloqueio::class)->latestOfMany();
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function vendedorExterno()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_externo_id');
    }
public function vendedorAssistente()
{
    return $this->belongsTo(Vendedor::class, 'vendedor_assistente_id');
}

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }


    public function contatos()
    {
        return $this->hasMany(Contato::class);
    }

    public function enderecos()
    {
        return $this->hasMany(Endereco::class);
    }

    public function enderecoComercial()
    {
        return $this->hasOne(Endereco::class)->where('tipo', 'comercial');
    }

    public function enderecoEntrega()
    {
        return $this->hasOne(Endereco::class)->where('tipo', 'entrega');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function certidoesNegativas()
    {
        return $this->documentos()->where('tipo', 'certidao_negativa');
    }

    public function descontos()
    {
        return $this->hasMany(Desconto::class);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $contato = $this->contatos()->first();
        $telefone = $contato?->telefone;

        if (!$telefone) {
            return null;
        }

        $numero = preg_replace('/\D/', '', $telefone);
        
        if ($numero && (strlen($numero) === 10 || strlen($numero) === 11)) {
            $numero = '55' . $numero;
        }

        return $numero ? "https://wa.me/{$numero}" : null;
    }

    public function getLimiteBoletoAttribute(): float
    {
        /** @var \App\Models\AnaliseCredito|null $ultimaAnalise */
        $ultimaAnalise = $this->analisesCredito()->latest()->first();
        return $ultimaAnalise ? (float) $ultimaAnalise->limite_boleto : 0.0;
    }

    public function getLimiteCarteiraAttribute(): float
    {
        /** @var \App\Models\AnaliseCredito|null $ultimaAnalise */
        $ultimaAnalise = $this->analisesCredito()->latest()->first();
        return $ultimaAnalise ? (float) $ultimaAnalise->limite_carteira : 0.0;
    }

    public function getLimiteTotalAttribute(): float
    {
        return $this->getLimiteBoletoAttribute() + $this->getLimiteCarteiraAttribute();
    }

    public function getLimiteUtilizadoAttribute(): float
    {
        // Limite contabilizado por: Boletos em aberto + Pedidos em aberto pendentes de faturamento
        // 1. Faturas em aberto (boletos pendentes)
        $valorFaturasAberto = $this->faturas()
            ->where('status', '!=', 'pago')
            ->sum('valor_total');
        
        // 2. Pedidos pendentes (Aprovado, Em Expedição, etc. que ainda não foram faturados)
        // Isso pode variar conforme a regra de negócio do app. 
        // Assumiremos status != Cancelado e != Faturado.
        $valorPedidosAberto = $this->pedidos()
            ->whereNotIn('status', ['Cancelado', 'Entregue', 'Faturado'])
            ->sum('valor_total');

        return (float) ($valorFaturasAberto + $valorPedidosAberto);
    }

    public function getLimiteDisponivelAttribute(): float
    {
        $disponivel = $this->getLimiteTotalAttribute() - $this->getLimiteUtilizadoAttribute();
        return $disponivel > 0 ? $disponivel : 0.0;
    }
}
