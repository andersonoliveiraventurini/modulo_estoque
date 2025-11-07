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
        Schema::create('cliente_credito_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->constrained('cliente_creditos')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->enum('tipo_movimentacao', ['utilizacao', 'estorno', 'expiracao', 'cancelamento', 'geracao_troco']);
            $table->decimal('valor_movimentado', 10, 2);
            $table->decimal('saldo_anterior', 10, 2);
            $table->decimal('saldo_posterior', 10, 2);
            $table->text('motivo'); // Detalhes da movimentação
            $table->string('referencia_tipo')->nullable(); // Ex: 'venda', 'orcamento'
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID da venda/orçamento
            $table->foreignId('usuario_id')->constrained('users'); // Quem fez a movimentação
            $table->foreignId('credito_troco_gerado_id')->nullable()->constrained('cliente_creditos'); // Se gerou troco
            $table->timestamps();
            $table->softDeletes();

            $table->index(['credito_id', 'tipo_movimentacao']);
            $table->index(['cliente_id', 'created_at']);
            $table->index('referencia_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_credito_movimentacoes');
    }
};
