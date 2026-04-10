<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estornos', function (Blueprint $table) {
            $table->id();

            // ── Relacionamentos ──────────────────────────────────────────────
            $table->foreignId('pagamento_id')
                ->constrained('pagamentos')
                ->cascadeOnDelete()
                ->comment('Pagamento que está sendo estornado.');

            $table->foreignId('solicitante_id')
                ->constrained('users')
                ->comment('Usuário que abriu a solicitação de estorno.');

            $table->foreignId('aprovador_id')
                ->nullable()
                ->constrained('users')
                ->comment('Usuário que aprovou ou rejeitou o estorno.');

            // ── Dados do estorno ─────────────────────────────────────────────
            $table->string('motivo')
                ->comment('Motivo pelo qual o estorno foi solicitado.');

            $table->enum('forma_estorno', [
                'debito',
                'credito',
                'pix',
                'dinheiro',
                'outro',
            ])->comment('Forma pela qual o valor será devolvido ao cliente.');

            $table->string('forma_estorno_detalhe')
                ->nullable()
                ->comment('Detalhamento obrigatório quando forma_estorno = "outro".');

            $table->decimal('valor', 10, 2)
                ->comment('Valor a ser estornado.');

            // ── Fluxo de aprovação ───────────────────────────────────────────
            $table->enum('status', [
                'pendente',
                'aprovado',
                'rejeitado',
                'concluido',
            ])->default('pendente')
              ->comment('Estado atual do estorno no fluxo de aprovação.');

            $table->text('observacao_aprovador')
                ->nullable()
                ->comment('Observação registrada pelo aprovador ao aceitar ou rejeitar.');

            $table->timestamp('aprovado_em')
                ->nullable()
                ->comment('Momento em que o estorno foi aprovado ou rejeitado.');

            $table->timestamp('concluido_em')
                ->nullable()
                ->comment('Momento em que o estorno foi efetivamente executado.');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estornos');
    }
};
