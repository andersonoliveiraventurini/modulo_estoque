<?php

namespace App\Services;

use App\Models\Estorno;
use App\Models\Pagamento;
use App\Models\User;
use App\Notifications\EstornoDecididoNotification;
use App\Notifications\EstornoSolicitadoNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EstornoService
{
    /**
     * Cria a solicitação do estorno e notifica os aprovadores.
     */
    public function solicitar(User $solicitante, Pagamento $pagamento, array $dados): Estorno
    {
        return DB::transaction(function () use ($solicitante, $pagamento, $dados) {
            $estorno = Estorno::create([
                'pagamento_id'          => $pagamento->id,
                'solicitante_id'        => $solicitante->id,
                'motivo'                => $dados['motivo'],
                'forma_estorno'         => $dados['forma_estorno'],
                'forma_estorno_detalhe' => $dados['forma_estorno_detalhe'] ?? null,
                'valor'                 => $dados['valor'],
                'status'                => Estorno::STATUS_PENDENTE,
            ]);

            // Busca os aprovadores
            $aprovadores = User::permission('estorno_aprovar')->get();
            
            // Notifica aprovadores, evitando notificar o próprio solicitante
            // caso ele mesmo tenha o papel logado (embora a policy bloqueie
            // que ele aprove a própria solicitação).
            foreach ($aprovadores as $aprovador) {
                if ($aprovador->id !== $solicitante->id) {
                    $aprovador->notify(new EstornoSolicitadoNotification($estorno));
                }
            }

            return $estorno;
        });
    }

    /**
     * Aprova a solicitação e notifica o solicitante.
     */
    public function aprovar(User $aprovador, Estorno $estorno, ?string $observacao = null): void
    {
        DB::transaction(function () use ($aprovador, $estorno, $observacao) {
            $estorno->update([
                'status'               => Estorno::STATUS_APROVADO,
                'aprovador_id'         => $aprovador->id,
                'aprovado_em'          => now(),
                'observacao_aprovador' => $observacao,
            ]);

            $estorno->solicitante->notify(new EstornoDecididoNotification($estorno));
        });
    }

    /**
     * Rejeita o estorno mediante observação obrigatória e notifica.
     */
    public function rejeitar(User $aprovador, Estorno $estorno, string $observacao): void
    {
        if (trim($observacao) === '') {
            throw new InvalidArgumentException("A observação é obrigatória ao rejeitar um estorno.");
        }

        DB::transaction(function () use ($aprovador, $estorno, $observacao) {
            $estorno->update([
                'status'               => Estorno::STATUS_REJEITADO,
                'aprovador_id'         => $aprovador->id,
                'aprovado_em'          => now(),
                'observacao_aprovador' => $observacao,
            ]);

            $estorno->solicitante->notify(new EstornoDecididoNotification($estorno));
        });
    }

    /**
     * Conclui a devolução do dinheiro fisicamente / digitalmente.
     */
    public function concluir(User $operador, Estorno $estorno): void
    {
        DB::transaction(function () use ($estorno) {
            $estorno->update([
                'status'       => Estorno::STATUS_CONCLUIDO,
                'concluido_em' => now(),
            ]);
            
            // TODO: Aqui deverá entrar a interface com o gateway (Stone/Asaas, etc)
            // ou atualização de saldo de crédito do cliente se aplicável no futuro.
        });
    }
}
