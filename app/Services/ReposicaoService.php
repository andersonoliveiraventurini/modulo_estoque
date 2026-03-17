<?php

namespace App\Services;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\HubStock;
use App\Models\Movimentacao;
use App\Models\OrdemReposicao;
use App\Models\Posicao;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Serviço responsável por toda a lógica de reposição de produtos ao HUB.
 *
 * Regras de negócio principais:
 * - O HUB é o Armazém de ID = 1.
 * - Uma "Ordem de Reposição" formaliza a solicitação de movimentação
 *   Origem → HUB, incluindo quem pediu e quem vai executar.
 * - A baixa no estoque da origem ocorre apenas ao confirmarReposicao().
 * - A devolução reverte o processo: HUB → Origem.
 * - hub_stocks mantém o saldo atual de cada produto no HUB para leitura rápida.
 */
final class ReposicaoService
{
    public const HUB_ARMAZEM_ID = 1;

    /**
     * Cria uma nova Ordem de Reposição ao HUB (status: pendente).
     */
    public function solicitarReposicao(int $produtoId, float $quantidade): OrdemReposicao
    {
        Log::info('ReposicaoService: solicitando reposição ao HUB', [
            'produto_id'    => $produtoId,
            'quantidade'    => $quantidade,
            'solicitado_por' => auth()->id(),
        ]);

        $ordem = OrdemReposicao::create([
            'produto_id'           => $produtoId,
            'quantidade_solicitada' => $quantidade,
            'status'               => 'pendente',
            'solicitado_por_id'    => auth()->id(),
        ]);

        Log::info('ReposicaoService: ordem criada', ['ordem_id' => $ordem->id]);

        return $ordem;
    }

    /**
     * Confirma a execução da reposição:
     * - Decrementa estoque da origem (se houver)
     * - Registra movimentação de saída na origem e entrada no HUB
     * - Atualiza hub_stocks com o saldo atual
     * - Marca a ordem como concluída
     */
    public function confirmarReposicao(
        OrdemReposicao $ordem,
        ?int $armazemOrigemId,
        ?int $corredorOrigemId,
        ?int $posicaoOrigemId,
        int $executorId
    ): void {
        $tipoEntrada = $armazemOrigemId ? 'entrada_hub' : 'entrada_hub';

        Log::info('ReposicaoService: confirmando reposição', [
            'ordem_id'       => $ordem->id,
            'executor'       => $executorId,
            'armazem_origem' => $armazemOrigemId,
            'tipo_entrada'   => $tipoEntrada,
        ]);

        try {
            DB::transaction(function () use ($ordem, $armazemOrigemId, $corredorOrigemId, $posicaoOrigemId, $executorId, $tipoEntrada) {
                $produto = Produto::lockForUpdate()->findOrFail($ordem->produto_id);

                // 1. Registrar movimentação de SAÍDA da origem (apenas se houver origem física)
                if ($armazemOrigemId) {
                    Log::info('ReposicaoService: registrando saída da origem', [
                        'tipo'           => 'saida_para_hub',
                        'produto_id'     => $produto->id,
                        'armazem_origem' => $armazemOrigemId,
                        'quantidade'     => $ordem->quantidade_solicitada,
                    ]);

                    $saida = Movimentacao::create([
                        'tipo'              => 'saida_para_hub',
                        'status'            => 'aprovado',
                        'data_movimentacao' => now()->toDateString(),
                        'observacao'        => "Reposição ao HUB - Ordem #{$ordem->id}",
                        'is_reposicao'      => true,
                        'usuario_id'        => auth()->id(),
                        'executor_id'       => $executorId,
                        'aprovado_em'       => now(),
                    ]);

                    $saida->itens()->create([
                        'produto_id'  => $produto->id,
                        'quantidade'  => $ordem->quantidade_solicitada,
                        'armazem_id'  => $armazemOrigemId,
                        'corredor_id' => $corredorOrigemId,
                        'posicao_id'  => $posicaoOrigemId,
                    ]);
                }

                // 2. Registrar movimentação de ENTRADA no HUB
                $observacaoEntrada = $armazemOrigemId
                    ? "Entrada no HUB - Ordem #{$ordem->id}"
                    : "Entrada no HUB - Ordem #{$ordem->id} (Entrada Direta)";

                Log::info('ReposicaoService: registrando entrada no HUB', [
                    'tipo'       => $tipoEntrada,
                    'produto_id' => $produto->id,
                    'quantidade' => $ordem->quantidade_solicitada,
                    'observacao' => $observacaoEntrada,
                ]);

                $entrada = Movimentacao::create([
                    'tipo'              => $tipoEntrada,
                    'status'            => 'aprovado',
                    'data_movimentacao' => now()->toDateString(),
                    'observacao'        => $observacaoEntrada,
                    'is_reposicao'      => true,
                    'usuario_id'        => auth()->id(),
                    'executor_id'       => $executorId,
                    'aprovado_em'       => now(),
                ]);

                $entrada->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $ordem->quantidade_solicitada,
                    'armazem_id' => self::HUB_ARMAZEM_ID,
                ]);

                // 3. Atualizar saldo no HubStock (updateOrCreate para manter consistência)
                $hubStock = HubStock::lockForUpdate()->firstOrCreate(
                    ['produto_id' => $produto->id],
                    ['quantidade' => 0, 'quantidade_reservada' => 0]
                );
                $hubStock->increment('quantidade', $ordem->quantidade_solicitada);

                Log::info('ReposicaoService: hub_stock atualizado', [
                    'produto_id'    => $produto->id,
                    'nova_qtd'      => $hubStock->fresh()->quantidade,
                ]);

                // 4. Atualizar a OrdemReposicao
                $ordem->update([
                    'status'             => 'concluida',
                    'executor_id'        => $executorId,
                    'armazem_origem_id'  => $armazemOrigemId,
                    'corredor_origem_id' => $corredorOrigemId,
                    'posicao_origem_id'  => $posicaoOrigemId,
                    'concluido_em'       => now(),
                ]);

                Log::info('ReposicaoService: reposição concluída', [
                    'ordem_id'   => $ordem->id,
                    'entrada_id' => $entrada->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('ReposicaoService: falha ao confirmar reposição', [
                'ordem_id'   => $ordem->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
                'usuario_id' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Devolução: HUB → Estoque físico (endereço de destino).
     */
    public function devolverAoEstoque(
        int $produtoId,
        float $quantidade,
        int $armazemDestinoId,
        ?int $corredorDestinoId,
        ?int $posicaoDestinoId,
        int $executorId
    ): void {
        Log::info('ReposicaoService: devolvendo produto do HUB ao estoque', [
            'produto_id'      => $produtoId,
            'quantidade'      => $quantidade,
            'armazem_destino' => $armazemDestinoId,
            'executor'        => $executorId,
        ]);

        try {
            DB::transaction(function () use ($produtoId, $quantidade, $armazemDestinoId, $corredorDestinoId, $posicaoDestinoId, $executorId) {
                // Validar saldo no HUB antes de debitar
                $hubStock = HubStock::lockForUpdate()->where('produto_id', $produtoId)->first();
                if (!$hubStock || $hubStock->quantidade < $quantidade) {
                    throw new \RuntimeException(
                        "Saldo insuficiente no HUB. Disponível: " . ($hubStock ? $hubStock->quantidade : 0) . ", Solicitado: {$quantidade}"
                    );
                }

                // 1. Saída do HUB
                Log::info('ReposicaoService: registrando saída do HUB (devolução)', [
                    'tipo'       => 'devolucao_hub',
                    'produto_id' => $produtoId,
                    'quantidade' => $quantidade,
                ]);

                $saida = Movimentacao::create([
                    'tipo'              => 'devolucao_hub',
                    'status'            => 'aprovado',
                    'data_movimentacao' => now()->toDateString(),
                    'observacao'        => "Devolução do HUB ao estoque físico",
                    'is_devolucao'      => true,
                    'usuario_id'        => auth()->id(),
                    'executor_id'       => $executorId,
                    'aprovado_em'       => now(),
                ]);

                $saida->itens()->create([
                    'produto_id' => $produtoId,
                    'quantidade' => $quantidade,
                    'armazem_id' => self::HUB_ARMAZEM_ID,
                ]);

                // 2. Entrada no endereço de destino
                Log::info('ReposicaoService: registrando entrada no destino (devolução)', [
                    'tipo'            => 'entrada',
                    'produto_id'      => $produtoId,
                    'armazem_destino' => $armazemDestinoId,
                    'quantidade'      => $quantidade,
                ]);

                $entrada = Movimentacao::create([
                    'tipo'              => 'entrada',
                    'status'            => 'aprovado',
                    'data_movimentacao' => now()->toDateString(),
                    'observacao'        => "Devolução do HUB — entrada no endereço físico",
                    'is_devolucao'      => true,
                    'usuario_id'        => auth()->id(),
                    'executor_id'       => $executorId,
                    'aprovado_em'       => now(),
                ]);

                $entrada->itens()->create([
                    'produto_id'  => $produtoId,
                    'quantidade'  => $quantidade,
                    'armazem_id'  => $armazemDestinoId,
                    'corredor_id' => $corredorDestinoId,
                    'posicao_id'  => $posicaoDestinoId,
                ]);

                // 3. Decrementar saldo no HubStock
                $hubStock->decrement('quantidade', $quantidade);

                Log::info('ReposicaoService: devolução concluída', [
                    'produto_id'  => $produtoId,
                    'saida_id'    => $saida->id,
                    'entrada_id'  => $entrada->id,
                    'novo_saldo'  => $hubStock->fresh()->quantidade,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('ReposicaoService: falha ao devolver ao estoque', [
                'produto_id'  => $produtoId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'usuario_id'  => auth()->id(),
            ]);
            throw $e;
        }
    }
}
