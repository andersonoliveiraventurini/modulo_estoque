<?php

namespace App\Policies;

use App\Models\RequisicaoCompra;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequisicaoCompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('visualizar_requisicao_compra');
    }

    public function view(User $user, RequisicaoCompra $requisicaoCompra): bool
    {
        return $user->hasPermissionTo('visualizar_requisicao_compra');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('criar_requisicao_compra');
    }

    public function update(User $user, RequisicaoCompra $requisicaoCompra): bool
    {
        return $user->hasPermissionTo('criar_requisicao_compra') && $requisicaoCompra->status === 'pendente';
    }

    public function delete(User $user, RequisicaoCompra $requisicaoCompra): bool
    {
        return $user->hasRole('admin');
    }

    public function aprovarNivel1(User $user): bool
    {
        return $user->hasPermissionTo('aprovar_requisicao_nivel_1');
    }

    public function aprovarNivel2(User $user): bool
    {
        return $user->hasPermissionTo('aprovar_requisicao_nivel_2');
    }

    public function aprovarNivel3(User $user): bool
    {
        return $user->hasPermissionTo('aprovar_requisicao_nivel_3');
    }
}
