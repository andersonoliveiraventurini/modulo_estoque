<?php

namespace App\Policies;

use App\Models\NonConformity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonConformityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('devolucao_visualizar_dashboard');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NonConformity $nonConformity): bool
    {
        return $user->hasPermissionTo('devolucao_visualizar_dashboard');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // RNC pode ser criada por Supervisor, Estoquista ou Admin
        return $user->hasPermissionTo('devolucao_criar_rnc');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NonConformity $nonConformity): bool
    {
        return $user->hasPermissionTo('devolucao_criar_rnc');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NonConformity $nonConformity): bool
    {
        return $user->hasRole('admin');
    }
}
