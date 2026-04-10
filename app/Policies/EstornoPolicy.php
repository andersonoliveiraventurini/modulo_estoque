<?php

namespace App\Policies;

use App\Models\Estorno;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstornoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode listar estornos.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_visualizar');
    }

    /**
     * Determina se o usuário pode ver um estorno específico.
     */
    public function view(User $user, Estorno $estorno): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_visualizar');
    }

    /**
     * Determina se o usuário pode abrir uma nova solicitação de estorno.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_solicitar');
    }

    /**
     * Determina se o usuário pode aprovar o estorno.
     *
     * Regras:
     * - Requer permissão 'estorno_aprovar'
     * - Estorno deve estar com status 'pendente'
     * - O aprovador não pode ser o mesmo usuário que solicitou
     */
    public function approve(User $user, Estorno $estorno): bool
    {
        if ($estorno->status !== Estorno::STATUS_PENDENTE) {
            return false;
        }

        if ($estorno->solicitante_id === $user->id && !$user->hasRole('admin')) {
            return false;
        }

        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_aprovar');
    }

    /**
     * Determina se o usuário pode rejeitar o estorno.
     *
     * Regras:
     * - Requer permissão 'estorno_aprovar' (mesma alçada da aprovação)
     * - Estorno deve estar com status 'pendente'
     */
    public function reject(User $user, Estorno $estorno): bool
    {
        if ($estorno->status !== Estorno::STATUS_PENDENTE) {
            return false;
        }

        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_aprovar');
    }

    /**
     * Determina se o usuário pode marcar o estorno como concluído.
     *
     * Regras:
     * - Requer permissão 'estorno_concluir'
     * - Estorno deve estar com status 'aprovado'
     */
    public function conclude(User $user, Estorno $estorno): bool
    {
        if ($estorno->status !== Estorno::STATUS_APROVADO) {
            return false;
        }

        return $user->hasRole('admin') || $user->hasPermissionTo('estorno_concluir');
    }
}
