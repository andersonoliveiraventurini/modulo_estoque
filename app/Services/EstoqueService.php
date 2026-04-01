<?php

namespace App\Services;

use App\Models\Conferencia;
use App\Models\EstoqueReserva;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\User;
use App\Models\SystemAlert;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\DB;
use App\Events\StockMovementRegistered;
use Illuminate\Support\Facades\Log;

final class EstoqueService
{
    /**
     * Registra o log de movimentação de estoque.
     */
    private function logMovement(array $data): void
    {
        StockMovementLog::create([
            'produto_id' => $data['produto_id'],
            'posicao_id' => $data['posicao_id'] ?? null,
            'colaborador_id' => $data['colaborador_id'] ?? auth()->id(),
            'orcamento_id' => $data['orcamento_id'] ?? null,
            'tipo_movimentacao' => $data['tipo_movimentacao'],
            'quantidade' => $data['quantidade'],
            'origem' => $data['origem'] ?? null,
            'destino' => $data['destino'] ?? null,
            'motivo' => $data['motivo'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        // Também dispara o evento se houver ouvintes
        event(new StockMovementRegistered($data));
    }

    public function reservarParaOrcamento(Orcamento $orcamento): void
    {
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

                    // Priorização do HUB (ID 1)
                    $hubStock = \App\Models\HubStock::where('produto_id', $produto->id)->lockForUpdate()->first();
                    $saldoHub = $hubStock ? (float) $hubStock->quantidade : 0;
                    
                    // Reservado no HUB já existente (para não contar duas vezes)
                    $reservadoHub = (float) EstoqueReserva::where('produto_id', $produto->id)
                        ->where('armazem_id', 1)
                        ->where('status', 'ativa')
                        ->sum('quantidade');
                    
                    $disponivelHub = max(0, $saldoHub - $reservadoHub);

                    if ($disponivelHub >= $quantidade) {
                        // Reserva total do HUB
                        EstoqueReserva::create([
                            'orcamento_id'  => $orcamento->id,
                            'produto_id'    => $produto->id,
                            'armazem_id'    => 1,
                            'quantidade'    => $quantidade,
                            'status'        => 'ativa',
                            'criado_por_id' => auth()->id(),
                        ]);
                    } else {
                        // HUB zerado ou insuficiente
                        if ($disponivelHub > 0) {
                            EstoqueReserva::create([
                                'orcamento_id'  => $orcamento->id,
                                'produto_id'    => $produto->id,
                                'armazem_id'    => 1,
                                'quantidade'    => $disponivelHub,
                                'status'        => 'ativa',
                                'criado_por_id' => auth()->id(),
                            ]);
                        }

                        $restante = $quantidade - $disponivelHub;
                        
                        // Reserva do estoque principal (armazém id != 1)
                        EstoqueReserva::create([
                            'orcamento_id'  => $orcamento->id,
                            'produto_id'    => $produto->id,
                            'armazem_id'    => null, // Indica estoque principal
                            'quantidade'    => $restante,
                            'status'        => 'ativa',
                            'criado_por_id' => auth()->id(),
                        ]);

                        // Notificação se HUB insuficiente ou zerado
                        if ($disponivelHub < $quantidade) {
                            $this->notificarHubInsuficiente($produto, $orcamento);
                        }
                    }
                    
                    Log::info("Item reservado: Produto #{$produto->id}, Qtd: {$quantidade}");
                }

                $orcamento->estoque_reservado_em = now();
                $orcamento->saveQuietly();
            });
            Log::info("Reserva concluída com sucesso para Orçamento #{$orcamento->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao reservar estoque para Orçamento #{$orcamento->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function notificarHubInsuficiente(Produto $produto, Orcamento $orcamento): void
    {
        SystemAlert::create([
            'tipo' => 'hub_zero',
            'mensagem' => "Item [{$produto->sku}] zerado ou insuficiente no HUB - reserva efetuada do estoque principal",
            'produto_id' => $produto_id ?? $produto->id,
            'orcamento_id' => $orcamento->id,
        ]);
        
        Log::warning("Item {$produto->sku} insuficiente no HUB para Orçamento #{$orcamento->id}");
    }

    public function liberarReservas(Orcamento $orcamento, array $consumos): void
    {
        Log::info("Liberando reservas para Orçamento #{$orcamento->id}");
        
        foreach ($consumos as $produtoId => $quantidadeConsumida) {
            if ($quantidadeConsumida <= 0) continue;

            $reservas = EstoqueReserva::where('orcamento_id', $orcamento->id)
                ->where('produto_id', $produtoId)
                ->where('status', 'ativa')
                ->orderBy('armazem_id', 'desc') // Consumir HUB primeiro (ID 1 > NULL)
                ->get();

            $restanteConsumir = $quantidadeConsumida;
            foreach ($reservas as $reserva) {
                if ($restanteConsumir <= 0) break;

                if ($reserva->quantidade <= $restanteConsumir) {
                    $restanteConsumir -= $reserva->quantidade;
                    $reserva->update(['status' => 'consumida']);
                } else {
                    // Consumo parcial da reserva
                    $novaReserva = $reserva->replicate();
                    $novaReserva->quantidade = $reserva->quantidade - $restanteConsumir;
                    $novaReserva->save();

                    $reserva->update([
                        'quantidade' => $restanteConsumir,
                        'status' => 'consumida'
                    ]);
                    $restanteConsumir = 0;
                }
            }
        }
    }

    public function baixarSaida(Conferencia $conf): void
    {
        Log::info("Processando baixa de saída para Conferência #{$conf->id}");
        $conf->load('itens.produto', 'orcamento');

        try {
            DB::transaction(function () use ($conf) {
                Orcamento::where('id', $conf->orcamento_id)->lockForUpdate()->first();

                $consumos = [];

                foreach ($conf->itens as $ci) {
                    if ($ci->is_encomenda ?? false) continue;
                    if (!$ci->produto) continue;

                    $produto = $ci->produto;
                    $q       = (float) $ci->qty_conferida;

                    if ($q <= 0) continue;

                    // Lógica de baixa: Primeiro HUB, depois outros.
                    // Mas as reservas já estão priorizadas.
                    $restanteBaixar = $q;

                    // 1. Baixar do HUB
                    $hubStock = \App\Models\HubStock::where('produto_id', $produto->id)->lockForUpdate()->first();
                    if ($hubStock && $hubStock->quantidade > 0) {
                        $baixaHub = min($hubStock->quantidade, $restanteBaixar);
                        $hubStock->decrement('quantidade', $baixaHub);
                        $restanteBaixar -= $baixaHub;
                        
                        $this->logMovement([
                            'produto_id' => $produto->id,
                            'tipo_movimentacao' => 'sale_output',
                            'quantidade' => -$baixaHub,
                            'origem' => 'HUB (Armazém 1)',
                            'destino' => 'Venda (Cliente)',
                            'orcamento_id' => $conf->orcamento_id,
                            'observacao' => "Saída HUB - Conferência #{$conf->id} / Orçamento #{$conf->orcamento_id}",
                        ]);
                    }

                    // 2. Baixar do estoque principal se sobrar
                    if ($restanteBaixar > 0) {
                        $this->logMovement([
                            'produto_id' => $produto->id,
                            'tipo_movimentacao' => 'sale_output',
                            'quantidade' => -$restanteBaixar,
                            'origem' => 'Estoque Principal (Outros)',
                            'destino' => 'Venda (Cliente)',
                            'orcamento_id' => $conf->orcamento_id,
                            'observacao' => "Saída Estoque Principal - Conferência #{$conf->id} / Orçamento #{$conf->orcamento_id}",
                        ]);
                    }

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

    public function baixarRnc(Produto $produto, float $quantidade, string $motivo, ?int $armazemId = null): void
    {
        Log::info("Processando baixa por RNC para Produto #{$produto->id}, Qtd: {$quantidade}");

        DB::transaction(function () use ($produto, $quantidade, $motivo, $armazemId) {
            $restanteBaixar = $quantidade;

            // Priorizar HUB se armazemId for 1 ou nulo
            if ($armazemId === 1 || is_null($armazemId)) {
                $hubStock = \App\Models\HubStock::where('produto_id', $produto->id)->lockForUpdate()->first();
                if ($hubStock && $hubStock->quantidade > 0) {
                    $baixaHub = min($hubStock->quantidade, $restanteBaixar);
                    $hubStock->decrement('quantidade', $baixaHub);
                    $restanteBaixar -= $baixaHub;
                    
                    $this->logMovement([
                        'produto_id' => $produto->id,
                        'tipo_movimentacao' => 'manual_adjustment', // Usando manual_adjustment para RNC ou podemos adicionar rnc_output
                        'quantidade' => -$baixaHub,
                        'origem' => 'HUB (Armazém 1)',
                        'motivo' => "RNC: {$motivo}",
                        'observacao' => "Baixa por RNC - HUB",
                    ]);
                }
            }

            if ($restanteBaixar > 0) {
                $this->logMovement([
                    'produto_id' => $produto->id,
                    'tipo_movimentacao' => 'manual_adjustment',
                    'quantidade' => -$restanteBaixar,
                    'origem' => 'Estoque Principal',
                    'motivo' => "RNC: {$motivo}",
                    'observacao' => "Baixa por RNC - Geral",
                ]);
            }

            $produto->decrement('estoque_atual', $quantidade);
            $this->verificarAlertaEstoqueBaixo($produto);
        });
    }

    public function getHubStock(int $productId): float
    {
        $hubStock = \App\Models\HubStock::where('produto_id', $productId)->first();
        return $hubStock ? (float) $hubStock->quantidade : 0;
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
            // Alerta de reposição necessária
            SystemAlert::create([
                'tipo' => 'replenishment_needed',
                'mensagem' => "Item [{$produto->sku}] com estoque baixo. Reposição necessária.",
                'produto_id' => $produto->id,
            ]);

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
     * Marca todas as reservas ativas de um orçamento como consumidas.
     */
    public function consumirReservaDoOrcamento(Orcamento $orcamento): void
    {
        Log::info("Marcando todas as reservas ativas como consumidas para Orçamento #{$orcamento->id}");
        
        EstoqueReserva::where('orcamento_id', $orcamento->id)
            ->where('status', 'ativa')
            ->update(['status' => 'consumida']);
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
            Orcamento::where('id', $orcamento->id)->lockForUpdate()->first();

            $consumos = [];

            foreach ($orcamento->itens->whereNotNull('produto_id') as $oi) {
                $produto = $oi->produto;
                $quantidade = (float) $oi->quantidade;

                if (!$produto || $quantidade <= 0) continue;

                $restanteBaixar = $quantidade;

                // 1. Baixar do HUB
                $hubStock = \App\Models\HubStock::where('produto_id', $produto->id)->lockForUpdate()->first();
                if ($hubStock && $hubStock->quantidade > 0) {
                    $baixaHub = min($hubStock->quantidade, $restanteBaixar);
                    $hubStock->decrement('quantidade', $baixaHub);
                    $restanteBaixar -= $baixaHub;
                    
                    $this->logMovement([
                        'produto_id' => $produto->id,
                        'tipo_movimentacao' => 'sale_output',
                        'quantidade' => -$baixaHub,
                        'origem' => 'HUB (Armazém 1)',
                        'destino' => 'Venda (Cliente)',
                        'orcamento_id' => $orcamento->id,
                        'observacao' => "Baixa definitiva HUB - Orçamento #{$orcamento->id}",
                    ]);
                }

                // 2. Baixar do estoque principal se sobrar
                if ($restanteBaixar > 0) {
                    $this->logMovement([
                        'produto_id' => $produto->id,
                        'tipo_movimentacao' => 'sale_output',
                        'quantidade' => -$restanteBaixar,
                        'origem' => 'Estoque Principal',
                        'destino' => 'Venda (Cliente)',
                        'orcamento_id' => $orcamento->id,
                        'observacao' => "Baixa definitiva Estoque Principal - Orçamento #{$orcamento->id}",
                    ]);
                }

                $produto->decrement('estoque_atual', $quantidade);

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
