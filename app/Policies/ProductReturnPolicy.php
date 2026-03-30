<?php

namespace App\Policies;

use App\Models\ProductReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductReturnPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('qualidade_visualizar_dashboard');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductReturn $productReturn): bool
    {
        return $user->hasPermissionTo('qualidade_visualizar_dashboard');
    }

    /**
     * Determine whether the user can create models (Vendedor, Supervisor, Admin).
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('qualidade_solicitar_devolucao');
    }

    /**
     * Determine whether the user can approve the first stage (Supervisor, Admin).
     */
    public function approveSupervisor(User $user, ProductReturn $productReturn): bool
    {
        if ($productReturn->status !== 'pendente_supervisor') {
            return false;
        }

        return $user->hasPermissionTo('qualidade_aprovar_supervisor');
    }

    /**
     * Determine whether the user can approve the final stage (Estoquista, Admin).
     */
    public function approveEstoque(User $user, ProductReturn $productReturn): bool
    {
        if ($productReturn->status !== 'pendente_estoque') {
            return false;
        }

        return $user->hasPermissionTo('qualidade_aprovar_estoque');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductReturn $productReturn): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductReturn $productReturn): bool
    {
        return $user->hasRole('admin');
    }
}
