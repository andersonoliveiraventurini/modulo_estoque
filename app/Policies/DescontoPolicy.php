<?php

namespace App\Policies;

use App\Models\Desconto;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DescontoPolicy
{

    /**
     * Determina se o usuário pode aprovar descontos
    
    public function aprovar(User $user, Desconto $desconto)
    {
        // Exemplo: apenas gerentes e administradores podem aprovar
        // Ajuste conforme suas regras de negócio
        
        // Não pode aprovar próprios descontos
        if ($desconto->user_id === $user->id) {
            return false;
        }
        
        // Não pode aprovar descontos já aprovados ou rejeitados
        if ($desconto->aprovado_em || $desconto->rejeitado_em) {
            return false;
        }
        
        // Verifica se tem permissão (exemplo usando roles)
        return $user->hasRole(['admin', 'gerente', 'supervisor']);
    }
 */
    /**
     * Determina se o usuário pode rejeitar descontos
     
    public function rejeitar(User $user, Desconto $desconto)
    {
        // Mesmas regras da aprovação
        return $this->aprovar($user, $desconto);
    }*/

    /**
     * Determina se o usuário pode aprovar em lote
     
    public function aprovarTodos(User $user)
    {
        return $user->hasRole(['admin', 'gerente']);
    }
*/
    /**
     * Determina se o usuário pode rejeitar em lote
    
    public function rejeitarTodos(User $user)
    {
        return $user->hasRole(['admin', 'gerente']);
    } 
        */
    
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
    public function view(User $user, Desconto $desconto): bool
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
    public function update(User $user, Desconto $desconto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Desconto $desconto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Desconto $desconto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Desconto $desconto): bool
    {
        return false;
    }
}
