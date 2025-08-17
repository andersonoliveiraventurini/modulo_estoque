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
        Schema::create('acao_deletar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()
                  ->comment('Referência ao usuário que executou a ação de deletar.');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('descricao')->comment('o que foi deletado.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acao_deletars');
    }
};
