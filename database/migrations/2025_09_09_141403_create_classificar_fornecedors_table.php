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
        Schema::create('classificar_fornecedors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fornecedor_id')->nullable()
                  ->comment('Referência ao fornecedor associado a este endereço.');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');
            // cliente
            $table->unsignedBigInteger('usuario_id')->nullable()
                ->comment('Usuário que fez a movimentação.');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classificar_fornecedors');
    }
};
