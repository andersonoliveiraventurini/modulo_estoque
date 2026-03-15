<?php

namespace App\Policies;

use App\Models\Movimentacao;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MovimentacaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('visualizar_movimentacao');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Movimentacao $movimentacao): bool
    {
        return $user->hasPermissionTo('visualizar_movimentacao');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('criar_movimentacao');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Movimentacao $movimentacao): bool
    {
        return $user->hasPermissionTo('criar_movimentacao') && $movimentacao->status === 'pendente';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Movimentacao $movimentacao): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Movimentacao $movimentacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Movimentacao $movimentacao): bool
    {
        return false;
    }
}
