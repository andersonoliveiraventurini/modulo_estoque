<?php

namespace App\Policies;

use App\Models\Orcamento;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OrcamentoPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Orcamento $orcamento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Orcamento $orcamento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Orcamento $orcamento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Orcamento $orcamento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Orcamento $orcamento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the route billing summary (Financeiro & Supervisor).
     */
    public function viewBilling(User $user)
    {
        return $user->hasPermissionTo('route_billing_view_billing');
    }

    /**
     * Determine whether the user can view loading screen.
     * Financeiro, Separação e Conferência
     */
    public function viewLoading(User $user)
    {
        return $user->hasPermissionTo('route_billing_view_loading');
    }

    /**
     * Determine whether the user can attach a payment proof.
     * Only the Vendedor who owns the Orcamento.
     */
    public function attach(User $user, Orcamento $orcamento)
    {
        if (!$user->hasPermissionTo('route_billing_attach')) {
            return false;
        }

        // Apenas Vendedor dono do pedido
        return $orcamento->vendedor_id === $user->id;
    }

    /**
     * Determine whether the user can approve.
     * Apenas Financeiro.
     */
    public function approve(User $user)
    {
        return $user->hasPermissionTo('route_billing_approve');
    }

    /**
     * Determine whether the user can deny.
     * Apenas Financeiro.
     */
    public function deny(User $user)
    {
        return $user->hasPermissionTo('route_billing_deny');
    }

    /**
     * Determine whether the user can validate attachment.
     * Apenas Financeiro.
     */
    public function validateAttachment(User $user)
    {
        return $user->hasPermissionTo('route_billing_validate_attachment');
    }
}
