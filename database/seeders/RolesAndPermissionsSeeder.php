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
            'ver_usuarios',
            'ver_usuario',
            'criar_usuario',
            'editar_usuario',
            'excluir_usuario',
            'acessar_painel_admin',
            
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
            
            // Permissões de Faturamento da Rota
            'faturamento_rota_anexar',
            'faturamento_rota_aprovar',
            'faturamento_rota_rejeitar',
            'faturamento_rota_validar_anexo',
            'faturamento_rota_ver_faturamento',
            'faturamento_rota_ver_carregamento',

            // Permissões de Orçamento
            'criar_orcamento',
            'editar_orcamento',

            // Permissões de Qualidade e Devoluções
            'qualidade_visualizar_dashboard',
            'qualidade_criar_rnc',
            'qualidade_solicitar_devolucao',
            'qualidade_aprovar_supervisor',
            'qualidade_aprovar_estoque',
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
            'faturamento_rota_ver_faturamento',
            'criar_orcamento',
            'editar_orcamento',
            'qualidade_visualizar_dashboard',
            'qualidade_criar_rnc',
            'qualidade_solicitar_devolucao',
            'qualidade_aprovar_supervisor',
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
            'qualidade_visualizar_dashboard',
            'qualidade_criar_rnc',
            'qualidade_aprovar_estoque',
        ]);

        // VENDEDOR: Operação de vendas
        $roleVendedor = Role::findOrCreate('vendedor', 'web');
        $roleVendedor->syncPermissions([
            'ver_usuario',
            'faturamento_rota_anexar',
            'criar_orcamento',
            'editar_orcamento',
            'qualidade_visualizar_dashboard',
            'qualidade_solicitar_devolucao',
        ]);

        // FINANCEIRO: Operação financeira e Faturamento da Rota
        $roleFinanceiro = Role::findOrCreate('financeiro', 'web');
        $roleFinanceiro->syncPermissions([
            'faturamento_rota_ver_faturamento',
            'faturamento_rota_aprovar',
            'faturamento_rota_rejeitar',
            'faturamento_rota_validar_anexo',
            'faturamento_rota_ver_carregamento',
        ]);

        // SEPARAÇÃO: Operação de separação (Faturamento da Rota)
        $roleSeparacao = Role::findOrCreate('separacao', 'web');
        $roleSeparacao->syncPermissions([
            'faturamento_rota_ver_carregamento',
        ]);

        // CONFERÊNCIA: Operação de conferência (Faturamento da Rota)
        $roleConferencia = Role::findOrCreate('conferencia', 'web');
        $roleConferencia->syncPermissions([
            'faturamento_rota_ver_carregamento',
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
