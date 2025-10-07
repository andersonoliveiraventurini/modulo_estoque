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
        Schema::create('analise_creditos', function (Blueprint $table) {
            $table->id();
            $table->double('limite_boleto')->nullable()
                    ->comment('Limite de boleto aprovado para o cliente.');
            $table->date('validade')->nullable()
                    ->comment('Data de validade do limite de crédito.');
            $table->text('observacoes')->nullable()
                    ->comment('Observações adicionais sobre a análise de crédito.');
            $table->unsignedBigInteger('cliente_id')->nullable()
                    ->comment('Referência ao cliente associado a este orçamento.');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->double('limite_carteira')->nullable()
                    ->comment('Limite de crédito aprovado para o cliente.');

            $table->unsignedBigInteger('user_id')->nullable()
                    ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analise_creditos');
    }
};
