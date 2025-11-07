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
        Schema::create('cliente_creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->decimal('valor_original', 10, 2); // Valor inicial do crédito
            $table->decimal('valor_disponivel', 10, 2); // Valor atual disponível
            $table->enum('tipo', ['devolucao', 'troco', 'bonificacao', 'ajuste', 'outro']);
            $table->text('motivo_origem'); // Por que foi gerado o crédito
            $table->string('origem_tipo')->nullable(); // Ex: 'venda', 'orcamento', 'manual'
            $table->unsignedBigInteger('origem_id')->nullable(); // ID da venda/orçamento que gerou
            $table->foreignId('usuario_criacao_id')->constrained('users');
            $table->enum('status', ['ativo', 'utilizado', 'expirado', 'cancelado'])->default('ativo');
            $table->date('data_validade')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cliente_id', 'status']);
            $table->index('data_validade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_creditos');
    }
};
