<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'encomenda'
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
        $totalDescontos = $this->descontos()->sum('valor');
        return $this->valor_total_itens - $totalDescontos;
    }

    /**
     * Verificar se o pagamento foi finalizado
     */
    public function pagamentoFinalizado()
    {
        $totalPago = $this->pagamentos()->sum('valor');
        return $totalPago >= $this->valorComDescontos();
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

    // solicitações de pagamento

    public function solicitacoesPagamento()
    {
        return $this->hasMany(SolicitacaoPagamento::class);
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
}
