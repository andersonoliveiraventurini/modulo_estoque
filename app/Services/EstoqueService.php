<?php

namespace App\Services;

use App\Models\Conferencia;
use App\Models\EstoqueReserva;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EstoqueService
{
    public function reservarParaOrcamento(Orcamento $orcamento): void
    {
        /* 
        if ($orcamento->estoque_reservado_em !== null) {
            return;
        } 
        */

        Log::info("Iniciando reserva de estoque para Orçamento #{$orcamento->id}");
        $orcamento->load('itens.produto');

        try {
            DB::transaction(function () use ($orcamento) {
                // Garante que apenas um processo mexa neste orçamento por vez
                Orcamento::where('id', $orcamento->id)->lockForUpdate()->first();

                // Se já existir reserva ativa, cancela para criar nova (atualizada)
                if (EstoqueReserva::where('orcamento_id', $orcamento->id)->where('status', 'ativa')->exists()) {
                    Log::info("Orçamento #{$orcamento->id} já possui reserva ativa. Re-calculando.");
                    $this->liberarReservaDoOrcamento($orcamento);
                }

                foreach ($orcamento->itens->whereNotNull('produto_id') as $oi) {
                    $produto    = $oi->produto;
                    $quantidade = (float) $oi->quantidade;

                    if (!$produto) continue;

                    if (!$this->checarEstoqueMinimo($produto, $quantidade)) {
                        throw new \Exception("Estoque insuficiente para o produto {$produto->nome} (SKU: {$produto->sku}). Disponível: {$produto->estoque_atual}");
                    }

                    EstoqueReserva::create([
                        'orcamento_id'  => $orcamento->id,
                        'produto_id'    => $produto->id,
                        'quantidade'    => $quantidade,
                        'status'        => 'ativa',
                        'criado_por_id' => auth()->id(),
                    ]);
                    
                    Log::info("Item reservado: Produto #{$produto->id}, Qtd: {$quantidade}");
                }

                $orcamento->update(['estoque_reservado_em' => now()]);
            });
            Log::info("Reserva concluída com sucesso para Orçamento #{$orcamento->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao reservar estoque para Orçamento #{$orcamento->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function liberarReservas(Orcamento $orcamento, array $consumos): void
    {
        Log::info("Liberando reservas para Orçamento #{$orcamento->id}");
        
        foreach ($consumos as $produtoId => $quantidadeConsumida) {
            if ($quantidadeConsumida <= 0) continue;

            $reservas = EstoqueReserva::where('orcamento_id', $orcamento->id)
                ->where('produto_id', $produtoId)
                ->where('status', 'ativa')
                ->get();

            foreach ($reservas as $reserva) {
                // Para simplificar, estamos marcando a reserva como consumida.
                // Em um cenário perfeito, poderíamos subtrair a quantidade se fosse parcial,
                // mas aqui o sistema parece tratar a reserva como um bloco por orçamento/produto.
                $reserva->update(['status' => 'consumida']);
                Log::info("Reserva consumida: Produto #{$produtoId}, Qtd Consumida: {$quantidadeConsumida}");
            }
        }
    }

    public function baixarSaida(Conferencia $conf): void
    {
        Log::info("Processando baixa de saída para Conferência #{$conf->id}");
        $conf->load('itens.produto', 'orcamento');

        try {
            DB::transaction(function () use ($conf) {
                // Garante que apenas um processo mexa no orçamento relacionado por vez
                Orcamento::where('id', $conf->orcamento_id)->lockForUpdate()->first();

                $consumos = [];

                foreach ($conf->itens as $ci) {
                    if ($ci->is_encomenda ?? false) continue;
                    if (!$ci->produto) continue;

                    $produto = $ci->produto;
                    $q       = (float) $ci->qty_conferida;

                    if ($q <= 0) continue;

                    $produto->decrement('estoque_atual', $q);
                    Log::info("Estoque atualizado (Saída): Produto #{$produto->id}, Qtd: -{$q}");
                    
                    $this->verificarAlertaEstoqueBaixo($produto);

                    $consumos[$produto->id] = ($consumos[$produto->id] ?? 0) + $q;
                }

                $this->liberarReservas($conf->orcamento, $consumos);
            });
            Log::info("Baixa de saída concluída para Conferência #{$conf->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar baixa de saída da Conferência #{$conf->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function checarEstoqueMinimo(Produto $produto, float $quantidadeReservar): bool
    {
        $reservado = (float) EstoqueReserva::where('produto_id', $produto->id)
            ->where('status', 'ativa')
            ->sum('quantidade');

        $disponivelAposReserva = (float) $produto->estoque_atual - $reservado - $quantidadeReservar;
        $min = (float) ($produto->estoque_minimo ?? 0);

        return $disponivelAposReserva >= $min;
    }

    /**
     * Verifica e dispara alerta se o estoque estiver baixo após uma movimentação.
     */
    public function verificarAlertaEstoqueBaixo(Produto $produto): void
    {
        if ($produto->estoque_atual <= $produto->estoque_minimo) {
            // 1. Enviar E-mail Alerta
            $admins = \App\Models\User::role('admin')->get();
            $compras = \App\Models\User::role('compras')->get();
            $recipients = $admins->concat($compras)->unique('id');

            if ($recipients->isNotEmpty()) {
                \Illuminate\Support\Facades\Mail::to($recipients)->queue(new \App\Mail\EstoqueBaixoMail($produto));
            }

            // 2. Gerar Requisição de Compra Automática (se não houver pendente)
            $jaTemPendente = \App\Models\RequisicaoCompra::whereHas('itens', function($q) use ($produto) {
                $q->where('produto_id', $produto->id);
            })->whereIn('status', ['pendente', 'em_aprovacao_v1', 'em_aprovacao_v2', 'em_aprovacao_v3'])->exists();

            if (!$jaTemPendente) {
                $requisicao = \App\Models\RequisicaoCompra::create([
                    'solicitante_id' => auth()->id() ?? User::role('admin')->first()?->id,
                    'data_requisicao' => now(),
                    'status' => 'pendente',
                    'observacao' => 'Gerada automaticamente pelo sistema devido a estoque baixo.',
                    'valor_estimado' => $produto->preco_custo * ($produto->estoque_minimo * 2), // Sugestão rascunho
                ]);

                $requisicao->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $produto->estoque_minimo * 2,
                    'valor_estimado' => $produto->preco_custo,
                ]);

                Log::info('Requisição de compra automática gerada por estoque baixo', [
                    'produto' => $produto->nome,
                    'requisicao_id' => $requisicao->id
                ]);
            }
        }
    }

    public function liberarReservaDoOrcamento(Orcamento $orcamento): void
    {
        Log::info("Cancelando todas as reservas ativas para Orçamento #{$orcamento->id}");
        
        EstoqueReserva::where('orcamento_id', $orcamento->id)
            ->where('status', 'ativa')
            ->update(['status' => 'cancelada']);
    }

    /**
     * Realiza a baixa definitiva do estoque para um orçamento pago.
     * Decrementa o estoque atual e marca a reserva como consumida.
     */
    public function baixarEstoqueDefinitivo(Orcamento $orcamento): void
    {
        Log::info("Iniciando baixa definitiva de estoque para Orçamento #{$orcamento->id}");
        $orcamento->load('itens.produto');

        DB::transaction(function () use ($orcamento) {
            // Garante que apenas um processo mexa neste orçamento por vez
            Orcamento::where('id', $orcamento->id)->lockForUpdate()->first();

            $consumos = [];

            foreach ($orcamento->itens->whereNotNull('produto_id') as $oi) {
                $produto = $oi->produto;
                $quantidade = (float) $oi->quantidade;

                if (!$produto || $quantidade <= 0) continue;

                // Decrementa o estoque real
                $produto->decrement('estoque_atual', $quantidade);
                
                // Log da movimentação (se houver tabela de logs específica, podemos usar aqui)
                Log::info("Baixa definitiva: Produto #{$produto->id}, Qtd: -{$quantidade}");

                $this->verificarAlertaEstoqueBaixo($produto);

                $consumos[$produto->id] = ($consumos[$produto->id] ?? 0) + $quantidade;
            }

            // Marca as reservas como consumidas
            $this->liberarReservas($orcamento, $consumos);
        });

        Log::info("Baixa definitiva concluída para Orçamento #{$orcamento->id}");
    }
}
