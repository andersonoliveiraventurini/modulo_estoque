<?php

namespace Tests\Feature;

use App\Models\ProductReturn;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Garante cache limpo antes de cada teste
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    // -------------------------------------------------------------------------
    // Grupo 1 — Integridade dos roles no seeder
    // -------------------------------------------------------------------------

    /** @test */
    public function supervisor_role_exists_with_exact_permissions(): void
    {
        $role = Role::findByName('supervisor', 'web');

        $this->assertNotNull($role, 'Role "supervisor" não existe.');

        $expected = [
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
        ];

        $actual = $role->permissions->pluck('name')->sort()->values()->all();
        sort($expected);

        $this->assertEquals(
            $expected,
            $actual,
            'As permissões do role "supervisor" não batem com o esperado.'
        );
    }

    /** @test */
    public function new_specialized_roles_exist_with_at_least_one_permission(): void
    {
        $roles = [
            'supervisor estoque',
            'supervisor financeiro',
            'supervisor vendas',
            'supervisor logistica',
            'caixa',
        ];

        foreach ($roles as $roleName) {
            $role = Role::findByName($roleName, 'web');

            $this->assertNotNull($role, "Role \"{$roleName}\" não existe.");
            $this->assertGreaterThan(
                0,
                $role->permissions->count(),
                "Role \"{$roleName}\" não possui nenhuma permissão."
            );
        }
    }

    /** @test */
    public function caixa_permissions_exist_in_database(): void
    {
        $caixaPermissions = [
            'caixa_abrir',
            'caixa_fechar',
            'caixa_sangria',
            'caixa_suprimento',
            'caixa_visualizar_movimentos',
            'caixa_emitir_relatorio',
        ];

        foreach ($caixaPermissions as $permission) {
            $this->assertNotNull(
                Permission::findByName($permission, 'web'),
                "Permissão \"{$permission}\" não existe no banco."
            );
        }
    }

    // -------------------------------------------------------------------------
    // Grupo 2 — Isolamento entre domínios
    // -------------------------------------------------------------------------

    /** @test */
    public function supervisor_estoque_does_not_have_caixa_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor estoque');

        $caixaPermissions = [
            'caixa_abrir',
            'caixa_fechar',
            'caixa_sangria',
            'caixa_suprimento',
            'caixa_visualizar_movimentos',
            'caixa_emitir_relatorio',
        ];

        foreach ($caixaPermissions as $permission) {
            $this->assertFalse(
                $user->hasPermissionTo($permission),
                "supervisor estoque não deveria ter a permissão \"{$permission}\"."
            );
        }
    }

    /** @test */
    public function caixa_role_does_not_have_estoque_or_financeiro_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('caixa');

        $forbidden = [
            'visualizar_movimentacao',
            'aprovar_movimentacao',
            'rejeitar_movimentacao',
            'aprovar_requisicao_nivel_1',
            'aprovar_requisicao_nivel_2',
            'faturamento_rota_ver_faturamento',
        ];

        foreach ($forbidden as $permission) {
            $this->assertFalse(
                $user->hasPermissionTo($permission),
                "caixa não deveria ter a permissão \"{$permission}\"."
            );
        }
    }

    /** @test */
    public function supervisor_financeiro_does_not_have_aprovar_movimentacao(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor financeiro');

        $this->assertFalse(
            $user->hasPermissionTo('aprovar_movimentacao'),
            'supervisor financeiro não deveria ter a permissão "aprovar_movimentacao".'
        );
    }

    /** @test */
    public function supervisor_vendas_does_not_have_aprovar_requisicao_nivel_2(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor vendas');

        $this->assertFalse(
            $user->hasPermissionTo('aprovar_requisicao_nivel_2'),
            'supervisor vendas não deveria ter a permissão "aprovar_requisicao_nivel_2".'
        );
    }

    // -------------------------------------------------------------------------
    // Grupo 3 — Permissões compartilhadas
    // -------------------------------------------------------------------------

    /** @test */
    public function supervisor_vendas_has_devolucao_visualizar_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor vendas');

        $this->assertTrue(
            $user->hasPermissionTo('devolucao_visualizar_dashboard'),
            'supervisor vendas deveria ter "devolucao_visualizar_dashboard".'
        );
    }

    /** @test */
    public function supervisor_logistica_has_devolucao_visualizar_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor logistica');

        $this->assertTrue(
            $user->hasPermissionTo('devolucao_visualizar_dashboard'),
            'supervisor logistica deveria ter "devolucao_visualizar_dashboard".'
        );
    }

    /** @test */
    public function both_supervisor_vendas_and_logistica_have_devolucao_solicitar_devolucao(): void
    {
        $userVendas = User::factory()->create();
        $userVendas->assignRole('supervisor vendas');

        $userLogistica = User::factory()->create();
        $userLogistica->assignRole('supervisor logistica');

        $this->assertTrue(
            $userVendas->hasPermissionTo('devolucao_solicitar_devolucao'),
            'supervisor vendas deveria ter "devolucao_solicitar_devolucao".'
        );

        $this->assertTrue(
            $userLogistica->hasPermissionTo('devolucao_solicitar_devolucao'),
            'supervisor logistica deveria ter "devolucao_solicitar_devolucao".'
        );
    }

    // -------------------------------------------------------------------------
    // Grupo 4 — Correções críticas aplicadas (regressão)
    // -------------------------------------------------------------------------

    /** @test */
    public function supervisor_logistica_can_approve_product_return_as_supervisor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor logistica');

        // ProductReturnPolicy::approveSupervisor() sem instância verifica apenas a permissão
        $this->assertTrue(
            $user->can('approveSupervisor', ProductReturn::class),
            'supervisor logistica deveria poder executar approveSupervisor (sem instância).'
        );

        // Com instância no status correto, a policy também deve permitir
        $productReturn = new ProductReturn(['status' => 'pendente_supervisor']);
        $productReturn->id = 0; // evita query — policy verifica status do objeto
        $productReturn->exists = false;

        $this->assertTrue(
            $user->can('approveSupervisor', $productReturn),
            'supervisor logistica deveria poder executar approveSupervisor em retorno pendente_supervisor.'
        );
    }

    /** @test */
    public function original_supervisor_role_can_still_approve_product_return(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        // Verificação genérica (sem instância) — para o menu sidebar
        $this->assertTrue(
            $user->can('approveSupervisor', ProductReturn::class),
            'O role "supervisor" original não deveria ter perdido a capacidade de approveSupervisor.'
        );

        // Com instância no status correto
        $productReturn = new ProductReturn(['status' => 'pendente_supervisor']);
        $productReturn->exists = false;

        $this->assertTrue(
            $user->can('approveSupervisor', $productReturn),
            'O role "supervisor" original deveria poder aprovar um ProductReturn pendente_supervisor.'
        );
    }

    // -------------------------------------------------------------------------
    // Grupo 5 — Seeder é idempotente
    // -------------------------------------------------------------------------

    /** @test */
    public function seeder_is_idempotent_and_does_not_duplicate_roles_or_permissions(): void
    {
        // O setUp() já executou o seeder uma vez.
        // Executa uma segunda vez para verificar idempotência.
        $rolesBeforeCount       = Role::count();
        $permissionsBeforeCount = Permission::count();

        $this->seed(RolesAndPermissionsSeeder::class);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assertEquals(
            $rolesBeforeCount,
            Role::count(),
            'O número de roles duplicou após executar o seeder pela segunda vez.'
        );

        $this->assertEquals(
            $permissionsBeforeCount,
            Permission::count(),
            'O número de permissões duplicou após executar o seeder pela segunda vez.'
        );
    }
}
