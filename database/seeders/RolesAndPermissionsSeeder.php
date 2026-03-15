<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Criar Permissões de Usuários (Administração)
        $permissions = [
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'access_filament_admin',
            
            // Permissões de Estoque
            'visualizar_movimentacao',
            'criar_movimentacao',
            'aprovar_movimentacao',
            'rejeitar_movimentacao',
            'realizar_conferencia',
            
            // Permissões de Compras
            'visualizar_requisicao_compra',
            'criar_requisicao_compra',
            'aprovar_requisicao_nivel_1',
            'aprovar_requisicao_nivel_2',
            'aprovar_requisicao_nivel_3',
            'gerenciar_pedido_compra',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Criar Cargos e Atribuir Permissões

        // ADMIN: Acesso Total
        $roleAdmin = Role::findOrCreate('admin', 'web');
        $roleAdmin->syncPermissions(Permission::all());

        // SUPERVISOR: Aprovações
        $roleSupervisor = Role::findOrCreate('supervisor', 'web');
        $roleSupervisor->syncPermissions([
            'visualizar_movimentacao',
            'aprovar_movimentacao',
            'rejeitar_movimentacao',
            'visualizar_requisicao_compra',
            'aprovar_requisicao_nivel_1',
            'aprovar_requisicao_nivel_2',
        ]);

        // COMPRAS: Operação comercial
        $roleCompras = Role::findOrCreate('compras', 'web');
        $roleCompras->syncPermissions([
            'visualizar_requisicao_compra',
            'criar_requisicao_compra',
            'gerenciar_pedido_compra',
        ]);

        // ESTOQUISTA: Operação de pátio
        $roleEstoquista = Role::findOrCreate('estoquista', 'web');
        $roleEstoquista->syncPermissions([
            'visualizar_movimentacao',
            'criar_movimentacao',
            'realizar_conferencia',
        ]);

        // VENDEDOR: Operação de vendas
        $roleVendedor = Role::findOrCreate('vendedor', 'web');
        $roleVendedor->syncPermissions([
            'view_user',
        ]);

        // 3. Atribuir Admin ao Primeiro Usuário (se existir)
        $user = User::first();
        if ($user) {
            $user->assignRole($roleAdmin);
            $this->command->info('Cargo Admin atribuído ao usuário: ' . $user->email);
        }

        $this->command->info('Roles e Permissions semeadas com sucesso!');
    }
}
