<?php

namespace Tests\Feature;

use App\Models\Estorno;
use App\Models\Pagamento;
use App\Models\User;
use App\Notifications\EstornoDecididoNotification;
use App\Notifications\EstornoSolicitadoNotification;
use App\Services\EstornoService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EstornoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Garante que os papéis e permissões estejam limpos e semeados
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function createPagamento(): Pagamento
    {
        return Pagamento::factory()->create([
            'valor_final' => 100.00,
            'valor_pago'  => 100.00,
        ]);
    }

    // ── GRUPO 1: Permissões e Acesso ──────────────────────────────────────

    public function test_usuario_com_role_caixa_pode_criar_estorno(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();

        $this->actingAs($caixa);
        $this->assertTrue($caixa->can('create', Estorno::class));

        // Testar instanciando o service para simular criação
        $service = app(EstornoService::class);
        $estorno = $service->solicitar($caixa, $pagamento, [
            'motivo' => 'Motivo de teste com mais de dez chars',
            'forma_estorno' => 'pix',
            'valor' => 50.00
        ]);

        $this->assertDatabaseHas('estornos', ['id' => $estorno->id, 'solicitante_id' => $caixa->id]);
    }

    public function test_usuario_sem_permissao_estorno_solicitar_recebe_403_ao_tentar_criar(): void
    {
        // Estoquista não tem estorno_solicitar
        $estoquista = $this->createUserWithRole('estoquista');
        
        $this->actingAs($estoquista);
        $this->assertFalse($estoquista->can('create', Estorno::class));
    }

    public function test_usuario_supervisor_financeiro_pode_aprovar_estorno_pendente(): void
    {
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $solicitante = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();

        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $solicitante->id,
            'motivo' => 'Qualquer motivo.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $this->actingAs($supervisor);
        $this->assertTrue($supervisor->can('approve', $estorno));
    }

    public function test_aprovador_nao_pode_ser_o_mesmo_que_o_solicitante(): void
    {
        // Se um usuário pode solicitar e aprovar, ele não deveria aprovar o seu próprio pedido
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        // Supondo que dermos provisoriamente a permissao solicitar pra ele, ou se ele for admin
        $admin = $this->createUserWithRole('admin');
        $pagamento = $this->createPagamento();

        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $admin->id,
            'motivo' => 'Motivo próprio.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $this->actingAs($admin);
        
        // Bloqueio de negócio via Policy
        $this->assertFalse($admin->can('approve', $estorno));
    }

    // ── GRUPO 2: Fluxo de Status ──────────────────────────────────────────

    public function test_estorno_criado_comeca_com_status_pendente(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();
        $service = app(EstornoService::class);

        $estorno = $service->solicitar($caixa, $pagamento, [
            'motivo' => 'Motivo de teste longo o suficiente',
            'forma_estorno' => 'pix',
            'valor' => 10.00
        ]);

        $this->assertEquals(Estorno::STATUS_PENDENTE, $estorno->status);
        $this->assertTrue($estorno->isPendente());
    }

    public function test_aprovacao_muda_status_para_aprovado_e_seta_aprovado_em(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $service = app(EstornoService::class);
        $service->aprovar($supervisor, $estorno, 'Tudo ok');

        $estorno->refresh();
        $this->assertEquals(Estorno::STATUS_APROVADO, $estorno->status);
        $this->assertEquals($supervisor->id, $estorno->aprovador_id);
        $this->assertNotNull($estorno->aprovado_em);
        $this->assertEquals('Tudo ok', $estorno->observacao_aprovador);
    }

    public function test_rejeicao_muda_status_para_rejeitado_e_exige_observacao(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $service = app(EstornoService::class);
        $service->rejeitar($supervisor, $estorno, 'Não procede o estorno');

        $estorno->refresh();
        $this->assertEquals(Estorno::STATUS_REJEITADO, $estorno->status);
        $this->assertEquals($supervisor->id, $estorno->aprovador_id);
        $this->assertNotNull($estorno->aprovado_em);
        $this->assertEquals('Não procede o estorno', $estorno->observacao_aprovador);
    }

    public function test_rejeicao_sem_observacao_lanca_exception(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $service = app(EstornoService::class);
        
        $this->expectException(\InvalidArgumentException::class);
        $service->rejeitar($supervisor, $estorno, '   '); // Em branco
    }

    public function test_conclusao_so_e_permitida_se_status_aprovado(): void
    {
        $operador = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $operador->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $this->actingAs($operador);
        
        // Pendente não pode ser concluido
        $this->assertFalse($operador->can('conclude', $estorno));

        // Mudando via banco para simular aprovado
        $estorno->update(['status' => Estorno::STATUS_APROVADO]);
        $this->assertTrue($operador->can('conclude', $estorno));

        // Executando a ação do Service
        $service = app(EstornoService::class);
        $service->concluir($operador, $estorno);

        $estorno->refresh();
        $this->assertEquals(Estorno::STATUS_CONCLUIDO, $estorno->status);
        $this->assertNotNull($estorno->concluido_em);
    }

    public function test_nao_e_possivel_aprovar_estorno_ja_decidido(): void
    {
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $solicitante = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $solicitante->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_APROVADO
        ]);

        $this->actingAs($supervisor);
        
        // Como o status já é 'aprovado', a action de approve DEVE ser bloqueada pela policy
        $this->assertFalse($supervisor->can('approve', $estorno));
        $this->assertFalse($supervisor->can('reject', $estorno));
    }

    // ── GRUPO 3: Notificações ─────────────────────────────────────────────

    public function test_ao_solicitar_usuarios_com_permissao_recebem_notificacao(): void
    {
        Notification::fake();

        $solicitante = $this->createUserWithRole('caixa');
        $supervisorFinanceiro = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();

        $service = app(EstornoService::class);
        $estorno = $service->solicitar($solicitante, $pagamento, [
            'motivo' => 'Solicitação teste.',
            'forma_estorno' => 'pix',
            'valor' => 10.00
        ]);

        Notification::assertSentTo(
            [$supervisorFinanceiro],
            EstornoSolicitadoNotification::class,
            function ($notification) use ($estorno) {
                return $notification->estorno->id === $estorno->id;
            }
        );

        Notification::assertNotSentTo([$solicitante], EstornoSolicitadoNotification::class);
    }

    public function test_ao_aprovar_solicitante_recebe_notificacao(): void
    {
        Notification::fake();

        $caixa = $this->createUserWithRole('caixa');
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $service = app(EstornoService::class);
        $service->aprovar($supervisor, $estorno, 'Tudo ok');

        Notification::assertSentTo(
            [$caixa],
            EstornoDecididoNotification::class,
            function ($notification) use ($estorno) {
                return $notification->estorno->id === $estorno->id &&
                       $notification->estorno->status === Estorno::STATUS_APROVADO;
            }
        );
    }

    public function test_ao_rejeitar_solicitante_recebe_notificacao(): void
    {
        Notification::fake();

        $caixa = $this->createUserWithRole('caixa');
        $supervisor = $this->createUserWithRole('supervisor financeiro');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste.',
            'forma_estorno' => 'dinheiro',
            'valor' => 50.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $service = app(EstornoService::class);
        $service->rejeitar($supervisor, $estorno, 'Recusado explicitamente');

        Notification::assertSentTo(
            [$caixa],
            EstornoDecididoNotification::class,
            function ($notification) use ($estorno) {
                return $notification->estorno->id === $estorno->id &&
                       $notification->estorno->status === Estorno::STATUS_REJEITADO;
            }
        );
    }

    // ── GRUPO 4: Integridade ──────────────────────────────────────────────

    public function test_estorno_fica_registrado_no_pagamento_via_relacionamento(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste integridade.',
            'forma_estorno' => 'dinheiro',
            'valor' => 15.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $pagamento->refresh();
        $this->assertCount(1, $pagamento->estornos);
        $this->assertEquals($estorno->id, $pagamento->estornos->first()->id);
    }

    public function test_softdelete_estorno_oculta_da_listagem_mas_mantem_no_banco(): void
    {
        $caixa = $this->createUserWithRole('caixa');
        $pagamento = $this->createPagamento();
        
        $estorno = Estorno::create([
            'pagamento_id' => $pagamento->id,
            'solicitante_id' => $caixa->id,
            'motivo' => 'Teste exclusão.',
            'forma_estorno' => 'dinheiro',
            'valor' => 15.00,
            'status' => Estorno::STATUS_PENDENTE
        ]);

        $estorno->delete();

        // Não deve aparecer na listagem normal do Laravel Model
        $this->assertCount(0, Estorno::all());
        
        // Mas o Model ainda existe apagado com o SoftDelete
        $this->assertCount(1, Estorno::withTrashed()->get());
        $this->assertDatabaseHas('estornos', [
            'id' => $estorno->id,
        ]);
        $this->assertNotNull(Estorno::withTrashed()->find($estorno->id)->deleted_at);
    }
}
