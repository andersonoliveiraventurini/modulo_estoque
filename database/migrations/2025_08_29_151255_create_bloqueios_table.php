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
        Schema::create('bloqueios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable()
                    ->comment('Referência ao cliente associado a este orçamento.');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->text('motivo')->nullable()
                    ->comment('Motivo do bloqueio.');

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
        Schema::dropIfExists('bloqueios');
    }
};
