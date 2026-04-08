<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Orcamento extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'versao',
        'cliente_id',
        'vendedor_id',
        'usuario_logado_id',
        'endereco_id',
        'obra',
        'frete',
        'valor_total_itens',
        'guia_recolhimento',
        'status',
        'observacoes',
        'validade',
        'pdf_path',
        'prazo_entrega',
        'token_acesso',
        'tipo_documento',
        'token_expira_em',
        'workflow_status',
        'condicao_id',
        'outros_meios_pagamento',
        'venda_triangular',
        'cnpj_triangular',
        'homologacao',
        'desconto_total',
        'valor_com_desconto',
        'encomenda',
        'loading_day',
        'estoque_reservado_em',
    ];

    protected $casts = [
        'estoque_reservado_em' => 'datetime',
        'validade' => 'date',
    ];

    // Model Orcamento
    public function consultaPrecoGrupo()
    {
        return $this->hasOne(ConsultaPrecoGrupo::class, 'orcamento_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function itens()
    {
        return $this->hasMany(OrcamentoItens::class);
    }

    public function vidros()
    {
        return $this->hasMany(OrcamentoVidro::class);
    }

    /**
     * Relacionamento com Pagamentos
     */
    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    /**
     * Relacionamento com Descontos
     */
    public function descontos()
    {
        return $this->hasMany(Desconto::class);
    }


    public function totalDescontosAprovados(): float
    {
        return $this->descontos()
            ->whereNotNull('aprovado_por')
            ->sum('valor');
    }

    /**
     * Relacionamento com Condição de Pagamento
     */
    public function condicaoPagamento()
    {
        return $this->belongsTo(CondicoesPagamento::class, 'condicao_id');
    }

    /**
     * Scope para orçamentos prontos para entrega
     */
    public function scopeProntosParaEntrega($query)
    {
        return $query->where('status', 'Aprovado');
    }

    public function pagamento()
    {
        return $this->hasOne(Pagamento::class, 'orcamento_id')->where('estornado', false);
    }

    /**
     * Verificar se o orçamento tem desconto
     */
    public function temDesconto()
    {
        return $this->descontos()->exists();
    }

    /**
     * Calcular total com descontos
     */
    public function valorComDescontos()
    {
        return $this->valor_total_itens - $this->totalDescontosAprovados();
    }

    /**
     * Verificar se o pagamento foi finalizado
     */
    public function pagamentoFinalizado()
    {
        $totalPago = (float) $this->pagamentos()->ativos()->sum('valor_pago');
        
        // Usamos o accessor valor_total_final que já inclui o orç. base + cobranças residuais
        return $totalPago >= (float) $this->valor_total_final;
    }

    public function transportes()
    {
        return $this->belongsToMany(TipoTransporte::class, 'orcamento_transportes');
    }

    public function cancelarLoteDeSeparacaoAtivo(): bool
    {
        // A variável $this aqui se refere à instância do orçamento na qual o método foi chamado.
        $orcamento = $this;

        return DB::transaction(function () use ($orcamento) {
            // 1. Busque pela INSTÂNCIA do lote ativo.
            $loteExistente = PickingBatch::where('orcamento_id', $orcamento->id)
                ->whereIn('status', ['aberto', 'em_separacao']) // Filtro de segurança para cancelar apenas lotes ativos.
                ->first();

            // 2. Se nenhum lote ativo for encontrado, não há nada a fazer.
            if (!$loteExistente) {
                return false; // Indica que nenhuma ação foi tomada.
            }

            // 3. Cancele e arquive os ITENS primeiro.
            $itensDoLote = PickingItem::where('picking_batch_id', $loteExistente->id);

            // Atualiza o status de todos os itens em uma única query.
            $itensDoLote->update(['status' => 'cancelado']);

            // Aciona o SoftDelete para cada item individualmente.
            // O get() aqui re-executa a busca, mas é necessário para iterar e deletar.
            foreach ($itensDoLote->get() as $item) {
                $item->delete();
            }

            // 4. Opere na INSTÂNCIA do lote que você encontrou.
            $loteExistente->status = 'cancelado';
            $loteExistente->finished_at = now();
            $loteExistente->save();

            // 5. Acione o Soft Deletes no próprio lote.
            $loteExistente->delete();

            // 6. Libere o estoque que estava reservado.
            app(\App\Services\EstoqueService::class)->liberarReservaDoOrcamento($orcamento);

            // 7. Atualize o status do próprio orçamento.
            $orcamento->update(['workflow_status' => 'cancelado']);

            return true; // Indica que a operação de cancelamento foi bem-sucedida.
        });
    }

    /**
     * Reseta toda a logística do orçamento (limpa separações e conferências)
     * para que o processo possa ser reiniciado após uma edição.
     */
    public function resetLogistica(): void
    {
        DB::transaction(function () {
            // 1. Deletar Conferências e seus itens
            foreach ($this->conferencias as $conferencia) {
                foreach ($conferencia->itens as $item) {
                    $item->fotos()->delete();
                    $item->delete();
                }
                $conferencia->delete();
            }

            // 2. Deletar Lotes de Separação (PickingBatch) e seus itens
            $lotes = PickingBatch::where('orcamento_id', $this->id)->get();
            foreach ($lotes as $lote) {
                // Removemos os itens do lote (PickingItem)
                // Se houver soft deletes, o delete() cuidará disso.
                $lote->items()->delete();
                $lote->delete();
            }

            // 3. Resetar status de workflow do orçamento
            $this->update([
                'workflow_status' => 'aguardando_separacao'
            ]);

            Log::info("Logística resetada para o orçamento #{$this->id} devido a edição.");
        });
    }

    // solicitações de pagamento

    public function solicitacoesPagamento()
    {
        return $this->hasMany(SolicitacaoPagamento::class);
    }

    public function pagamentosResiduais()
    {
        return $this->hasMany(Pagamento::class)->residuais();
    }

    public function solicitacaoPagamentoPendente()
    {
        return $this->hasOne(SolicitacaoPagamento::class)
            ->where('status', 'Pendente')
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->latest();
    }

    public function temSolicitacaoPagamentoPendente()
    {
        return $this->solicitacoesPagamento()
            ->where('status', 'Pendente')
            ->whereNull('aprovado_em')
            ->whereNull('rejeitado_em')
            ->exists();
    }

    /**
     * Relacionamento com Conferências
     */
    public function conferencias()
    {
        return $this->hasMany(Conferencia::class);
    }
    /**
     * Acessador para compatibilidade com Pedido e FaturaService.
     * Retorna o valor com desconto se houver, caso contrário o valor total dos itens.
     */
    public function getValorTotalAttribute()
    {
        return $this->valor_com_desconto > 0 ? $this->valor_com_desconto : $this->valor_total_itens;
    }

    /**
     * Retorna o total de cobranças residuais ativas.
     */
    public function getValorResiduaisAttribute(): float
    {
        return (float) $this->pagamentosResiduais()->ativos()->sum('valor_final');
    }

    /**
     * Retorna o valor total final do orçamento somado aos residuais.
     */
    public function getValorTotalFinalAttribute(): float
    {
        return (float) $this->valor_total + $this->valor_residuais;
    }

    /**
     * Retorna o saldo que falta pagar (Total Final - Total Pago).
     */
    public function getValorRestanteAttribute(): float
    {
        $totalFinal = $this->valor_total_final;
        $totalPago = (float) $this->pagamentos()->ativos()->sum('valor_pago');
        return max(0, $totalFinal - $totalPago);
    }

    /**
     * Acessador para compatibilidade com Pedido e PagamentoService.
     */
    public function getDescontoAttribute()
    {
        return $this->desconto_total;
    }

    public function getLoadingDayFormattedAttribute()
    {
        $dias = [
            'monday' => 'Segunda-feira',
            'tuesday' => 'Terça-feira',
            'wednesday' => 'Quarta-feira',
            'thursday' => 'Quinta-feira',
            'friday' => 'Sexta-feira',
            'express' => 'Express',
            'sedex' => 'Sedex',
            'carrier' => 'Transportadora'
        ];

        return $this->loading_day ? ($dias[$this->loading_day] ?? $this->loading_day) : null;
    }

    public function routeBillingAttachments()
    {
        return $this->hasMany(RouteBillingAttachment::class);
    }

    public function routeBillingApprovals()
    {
        return $this->hasMany(RouteBillingApproval::class);
    }

    /**
     * Identifica o canal de venda para regras de faturamento e estoque.
     */
    public function getCanalVendaAttribute(): string
    {
        if ($this->encomenda) {
            return 'encomenda';
        }

        if (in_array($this->loading_day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
            return 'rota';
        }

        if (in_array($this->loading_day, ['express', 'sedex', 'carrier'])) {
            return 'entrega_terceiros';
        }

        // Default é Balcão/Retira
        return 'balcao';
    }

    public function isProntaEntrega(): bool
    {
        return $this->canal_venda === 'balcao';
    }

    public function isBalcao(): bool
    {
        return $this->isProntaEntrega();
    }

    public function isEncomenda(): bool
    {
        return $this->canal_venda === 'encomenda';
    }

    public function isEntregaAgendada(): bool
    {
        return in_array($this->canal_venda, ['rota', 'entrega_terceiros']);
    }

    public function isCanalEntrega(): bool
    {
        return $this->isEntregaAgendada();
    }

    // ─── Status helpers ───────────────────────────────────────────────────────

    /** O orçamento foi aprovado (estoque válido, pronto para separação). */
    public function isAprovado(): bool
    {
        return $this->status === 'Aprovado';
    }

    /** O orçamento foi faturado / concluído financeiramente. */
    public function isFinalizado(): bool
    {
        return in_array($this->status, ['Finalizado', 'Pago', 'Concluído']);
    }

    /** O orçamento foi cancelado, rejeitado, expirado ou estornado. */
    public function isCancelado(): bool
    {
        return in_array($this->status, ['Cancelado', 'Rejeitado', 'Expirado', 'Estornado']);
    }
}
