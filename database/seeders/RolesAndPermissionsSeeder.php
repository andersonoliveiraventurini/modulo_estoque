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
            // TODO: corrigir blade resources/views/paginas/movimentacao/show.blade.php L25
            //       @can('aprovar movimentacao') usa espaço — deve ser 'aprovar_movimentacao' (underscore)
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
            'devolucao_visualizar_dashboard',
            'devolucao_criar_rnc',
            'devolucao_solicitar_devolucao',
            'devolucao_aprovar_supervisor',
            'devolucao_aprovar_estoque',

            // Permissões de Caixa (novas — criadas para o role 'caixa')
            'caixa_abrir',
            'caixa_fechar',
            'caixa_sangria',
            'caixa_suprimento',
            'caixa_visualizar_movimentos',
            'caixa_emitir_relatorio',

            // Permissões de Estorno (módulo de solicitação e aprovação de estornos)
            // Espelha: 2026_04_10_000002_add_estorno_permissions.php
            'estorno_solicitar',   // Abrir solicitação de estorno
            'estorno_aprovar',     // Aprovar ou rejeitar solicitações
            'estorno_concluir',    // Marcar como executado (concluido)
            'estorno_visualizar',  // Visualizar lista e detalhes
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Criar Roles Existentes e Atribuir Permissões

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
            'devolucao_visualizar_dashboard',
            'devolucao_criar_rnc',
            'devolucao_solicitar_devolucao',
            'devolucao_aprovar_supervisor',
            'estorno_solicitar',
            'estorno_aprovar',
            'estorno_concluir',
            'estorno_visualizar',
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
            'devolucao_visualizar_dashboard',
            'devolucao_criar_rnc',
            'devolucao_aprovar_estoque',
        ]);

        // VENDEDOR: Operação de vendas
        $roleVendedor = Role::findOrCreate('vendedor', 'web');
        $roleVendedor->syncPermissions([
            'ver_usuario',
            'faturamento_rota_anexar',
            'criar_orcamento',
            'editar_orcamento',
            'devolucao_visualizar_dashboard',
            'devolucao_solicitar_devolucao',
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

        // ---------------------------------------------------------------
        // 3. Novos Roles de Supervisão Especializada
        //    Idempotente: findOrCreate + syncPermissions são safe para
        //    múltiplas execuções sem duplicar dados.
        // ---------------------------------------------------------------

        // SUPERVISOR ESTOQUE: Aprovação e visibilidade de movimentações e compras
        $roleSupervisorEstoque = Role::findOrCreate('supervisor estoque', 'web');
        $roleSupervisorEstoque->syncPermissions([
            'visualizar_movimentacao',
            'aprovar_movimentacao',
            'rejeitar_movimentacao',
            'visualizar_requisicao_compra',
            'aprovar_requisicao_nivel_1',
        ]);

        // SUPERVISOR FINANCEIRO: Alçada financeira, faturamento de rota
        $roleSupervisorFinanceiro = Role::findOrCreate('supervisor financeiro', 'web');
        $roleSupervisorFinanceiro->syncPermissions([
            'visualizar_requisicao_compra',
            'aprovar_requisicao_nivel_2',
            'faturamento_rota_ver_faturamento',
        ]);

        // SUPERVISOR VENDAS: Orçamentos e devoluções iniciadas pelo cliente
        $roleSupervisorVendas = Role::findOrCreate('supervisor vendas', 'web');
        $roleSupervisorVendas->syncPermissions([
            'criar_orcamento',
            'editar_orcamento',
            'devolucao_visualizar_dashboard',
            'devolucao_solicitar_devolucao',
        ]);

        // SUPERVISOR LOGISTICA: Qualidade, RNC e aprovação de devoluções
        $roleSupervisorLogistica = Role::findOrCreate('supervisor logistica', 'web');
        $roleSupervisorLogistica->syncPermissions([
            'devolucao_visualizar_dashboard',
            'devolucao_criar_rnc',
            'devolucao_solicitar_devolucao',
            'devolucao_aprovar_supervisor',
        ]);

        // CAIXA: Operação de caixa (permissões caixa_* criadas acima)
        $roleCaixa = Role::findOrCreate('caixa', 'web');
        $roleCaixa->syncPermissions([
            'caixa_abrir',
            'caixa_fechar',
            'caixa_sangria',
            'caixa_suprimento',
            'caixa_visualizar_movimentos',
            'caixa_emitir_relatorio',
        ]);

        // Atribuições baseadas em givePermissionTo exigidas para o módulo de estorno
        $roleCaixa->givePermissionTo([
            'estorno_visualizar',
            'estorno_solicitar',
            'estorno_concluir',
        ]);

        $roleSupervisorFinanceiro->givePermissionTo([
            'estorno_visualizar',
            'estorno_aprovar',
        ]);

        // 4. Atribuir Admin ao Primeiro Usuário (se existir)
        $user = User::first();
        if ($user) {
            $user->assignRole($roleAdmin);
            $this->command->info('Cargo Admin atribuído ao usuário: ' . $user->email);
        }

        $this->command->info('Roles e Permissions semeadas com sucesso!');
    }
}
